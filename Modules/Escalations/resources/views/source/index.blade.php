@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Sources')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-md-20">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Sources</h4>
                    @can('view-sources-escalation')
                    <a href="{{ route('sources.create') }}" class="btn btn-primary btn-xs">Add Source</a>
                    @endcan
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif


<table id="sources-table" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Edited By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sources as $source)
                                <tr>
                                    <td>{{ $source->id }}</td>
                                    <td>{{ $source->name }}</td>
                                    <td>{{ $source->description }}</td>
                                    <td>{{ $source->status }}</td>
                                    <td>{{ $source->created_by }}</td>
                                    <td>{{ $source->edited_by }}</td>
                                    <td>
                                        @can('view-sources-escalation')
                                        <a href="{{ route('sources.show', $source) }}" class="btn btn-info btn-xs">View</a>
                                        @endcan
                                        @can('view-edit-escalation-source')
                                        <a href="{{ route('sources.edit', $source) }}" class="btn btn-warning btn-xs">Edit</a>
                                        @endcan
                                        @can('view-edit-escalation-source-status')
                                        @if($source->status === 'Active')
                                            <form action="{{ route('sources.active', $source) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Mark as inactive?')">Inactivate</button>
                                            </form>
                                        @else
                                            <form action="{{ route('sources.deactive', $source) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-success btn-xs" onclick="return confirm('Mark as active?')">Activate</button>
                                            </form>
                                        @endif
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $sources->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('vendor-style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endsection

@section('vendor-script')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    $('#sources-table').DataTable({
        responsive: true
    });
});
</script>
@endsection

