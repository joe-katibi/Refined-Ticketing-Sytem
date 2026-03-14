@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Status Details: {{ $status->display_name }}</h5>
                    <div>
                        <a href="{{ route('appointment.statuses.edit', $status) }}" class="btn btn-primary btn-sm me-2">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('appointment.statuses.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-12 text-center mb-3">
                            <span class="badge fs-5 p-2" style="background-color: {{ $status->color }}; color: #fff;">
                                {{ $status->display_name }}
                            </span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th style="width: 200px;">ID</th>
                                    <td>{{ $status->id }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ $status->name }}</td>
                                </tr>
                                <tr>
                                    <th>Display Name</th>
                                    <td>{{ $status->display_name }}</td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>{{ $status->description ?? 'No description provided' }}</td>
                                </tr>
                                <tr>
                                    <th>Color</th>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="color-box me-2" style="width: 30px; height: 30px; background-color: {{ $status->color }}; border: 1px solid #ddd;"></div>
                                            <span>{{ $status->color }}</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Badge Class</th>
                                    <td>{{ $status->badge_class ?? 'None' }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge bg-{{ $status->status == 'Active' ? 'success' : 'danger' }}">
                                            {{ $status->status }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>System Status</th>
                                    <td>
                                        @if($status->is_system)
                                            <span class="badge bg-info">Yes - System Defined</span>
                                        @else
                                            <span class="badge bg-secondary">No - Custom Status</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Sort Order</th>
                                    <td>{{ $status->sort_order }}</td>
                                </tr>
                                <tr>
                                    <th>Created By</th>
                                    <td>{{ $status->creator ? $status->creator->name : 'System' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ $status->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Last Updated By</th>
                                    <td>{{ $status->editor ? $status->editor->name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Last Updated At</th>
                                    <td>{{ $status->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <h6>Status Preview Examples:</h6>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <span class="badge" style="background-color: {{ $status->color }}">{{ $status->display_name }}</span>
                            <span class="badge {{ $status->badge_class }}" style="background-color: {{ $status->color }}">{{ $status->display_name }}</span>
                            <span class="badge rounded-pill" style="background-color: {{ $status->color }}">{{ $status->display_name }}</span>
                        </div>
                    </div>

                    @if(!$status->is_system)
                    <div class="mt-4 text-end">
                        <form action="{{ route('appointment.statuses.destroy', $status) }}" method="POST" class="d-inline delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this status?')">
                                <i class="fas fa-trash"></i> Delete Status
                            </button>
                        </form>
                    </div>
                    @endif
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
