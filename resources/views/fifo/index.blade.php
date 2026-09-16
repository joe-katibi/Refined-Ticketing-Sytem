@php
$configData = Helper::appClasses();
$moduleLabel = ucfirst($module);
@endphp

@extends('layouts/layoutMaster')

@section('title', $moduleLabel . ' FIFO Queue')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Waiting in queue</div>
                    <div class="fs-3 fw-bold">{{ $entries->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Oldest waiting</div>
                    <div class="fs-6 fw-bold" title="{{ optional($entries->first())->queue_entered_at }}">
                        {{ $entries->first() ? $entries->first()->queue_entered_at->diffForHumans(null, true) . ' ago' : '—' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Available agents</div>
                    <div class="fs-3 fw-bold">{{ $agents->where('is_available', true)->count() }} / {{ $agents->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 d-flex align-items-center">
            <form method="POST" action="{{ route('fifo.assign-next', $module) }}" class="w-100">
                @csrf
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bx bx-fast-forward-circle me-1"></i> Assign Next (FIFO)
                </button>
            </form>
        </div>
    </div>

    <ul class="nav nav-tabs mb-3">
        @foreach(['escalation' => 'Escalations', 'appointment' => 'Appointments', 'outage' => 'Outages'] as $key => $label)
            <li class="nav-item">
                <a class="nav-link {{ $module === $key ? 'active' : '' }}" href="{{ route('fifo.index', $key) }}">{{ $label }}</a>
            </li>
        @endforeach
    </ul>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $moduleLabel }} Queue — oldest eligible first</h5>
                </div>
                <div class="card-body">
                    @if($entries->isEmpty())
                        <p class="text-muted mb-0">No tickets waiting in this queue.</p>
                    @else
                        <form method="POST" action="{{ route('fifo.bulk-assign', $module) }}" id="bulk-assign-form">
                            @csrf
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="select-all"></th>
                                            <th>Ticket</th>
                                            <th>Priority</th>
                                            <th>Region</th>
                                            <th>Waiting</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($entries as $entry)
                                            <tr>
                                                <td><input type="checkbox" name="entry_ids[]" value="{{ $entry->id }}" class="entry-checkbox"></td>
                                                <td>
                                                    @if($entry->ticket)
                                                        <a href="#" onclick="return false;">#{{ $entry->work_id }}</a>
                                                        <div class="small text-muted">{{ \Illuminate\Support\Str::limit($entry->ticket->description ?? ($entry->ticket->title ?? ''), 60) }}</div>
                                                    @else
                                                        <span class="text-danger">#{{ $entry->work_id }} (ticket not found)</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-label-{{ ['Critical'=>'danger','High'=>'warning','Medium'=>'info','Low'=>'secondary'][$entry->priority] ?? 'secondary' }}">{{ $entry->priority }}</span>
                                                </td>
                                                <td>{{ $entry->region->name ?? '—' }}</td>
                                                <td title="{{ $entry->queue_entered_at }}">{{ $entry->queue_entered_at->diffForHumans(null, true) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex gap-2 align-items-center mt-3">
                                <select name="user_id" class="form-select" style="max-width: 280px" required>
                                    <option value="">Assign selected to…</option>
                                    @foreach($technicians as $tech)
                                        <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bx bx-group me-1"></i> Bulk Assign Selected
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Agent Availability — {{ $moduleLabel }}</h5>
                </div>
                <div class="card-body">
                    @forelse($agents as $agent)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <div class="fw-semibold">{{ $agent->user->name ?? 'Unknown user' }}</div>
                                <div class="small text-muted">
                                    Active: {{ $agent->active_count }}{{ $agent->capacity ? ' / ' . $agent->capacity : '' }}
                                    · {{ $agent->user->region->name ?? 'No region' }}
                                </div>
                            </div>
                            <span class="badge bg-label-{{ $agent->is_available ? 'success' : 'secondary' }}">
                                {{ $agent->is_available ? 'Available' : 'Unavailable' }}
                            </span>
                        </div>
                    @empty
                        <p class="text-muted mb-3">No agents registered for this queue yet.</p>
                    @endforelse

                    <form method="POST" action="{{ route('fifo.agent-availability', $module) }}" class="mt-3 pt-3 border-top">
                        @csrf
                        <label class="form-label small">Add / update agent</label>
                        <select name="user_id" class="form-select form-select-sm mb-2" required>
                            <option value="">Select technician</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                            @endforeach
                        </select>
                        <div class="d-flex gap-2">
                            <select name="is_available" class="form-select form-select-sm">
                                <option value="1">Available</option>
                                <option value="0">Unavailable</option>
                            </select>
                            <input type="number" name="capacity" class="form-control form-control-sm" placeholder="Max concurrent (optional)" min="1">
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-secondary mt-2 w-100">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
document.getElementById('select-all')?.addEventListener('change', function () {
    document.querySelectorAll('.entry-checkbox').forEach(cb => cb.checked = this.checked);
});
</script>
@endsection
