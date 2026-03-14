@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Appointment Statuses</h5>
                    <a href="{{ route('appointment.statuses.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New Status
                    </a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Display Name</th>
                                    <th>Description</th>
                                    <th>Color</th>
                                    <th>Status</th>
                                    <th>System</th>
                                    <th>Order</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($statuses as $status)
                                    <tr>
                                        <td>{{ $status->id }}</td>
                                        <td>{{ $status->name }}</td>
                                        <td>
                                            <span class="badge" style="background-color: {{ $status->color }}">
                                                {{ $status->display_name }}
                                            </span>
                                        </td>
                                        <td>{{ Str::limit($status->description, 50) }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="color-box me-2" style="width: 20px; height: 20px; background-color: {{ $status->color }}; border: 1px solid #ddd;"></div>
                                                <span>{{ $status->color }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $status->status == 'Active' ? 'success' : 'danger' }}">
                                                {{ $status->status }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($status->is_system)
                                                <span class="badge bg-info">System</span>
                                            @else
                                                <span class="badge bg-secondary">Custom</span>
                                            @endif
                                        </td>
                                        <td>{{ $status->sort_order }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('appointment.statuses.show', $status) }}" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('appointment.statuses.edit', $status) }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('appointment.statuses.toggle', $status) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-{{ $status->status == 'Active' ? 'warning' : 'success' }} btn-sm" title="{{ $status->status == 'Active' ? 'Deactivate' : 'Activate' }}">
                                                        <i class="fas fa-{{ $status->status == 'Active' ? 'ban' : 'check' }}"></i>
                                                    </button>
                                                </form>
                                                @if(!$status->is_system)
                                                    <form action="{{ route('appointment.statuses.destroy', $status) }}" method="POST" class="d-inline delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this status?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No appointment statuses found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $statuses->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Confirm delete
        $('.delete-form').on('submit', function(e) {
            if (!confirm('Are you sure you want to delete this status?')) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush
