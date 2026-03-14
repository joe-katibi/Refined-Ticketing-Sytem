@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Subcategories')
@section('content')

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Subcategories of {{ $category->category_name }}</h4>
                    <a href="{{ route('escalation-category.subcategories.create', $category) }}" class="btn btn-primary btn-xs">Add Subcategory</a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <table id="subcategories-table" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Edited By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subcategories as $subcategory)
                                <tr>
                                    <td>{{ $subcategory->id }}</td>
                                    <td>{{ $subcategory->sub_category_name }}</td>
                                    <td>{{ $subcategory->status }}</td>
                                    <td>{{ $subcategory->created_by }}</td>
                                    <td>{{ $subcategory->edited_by }}</td>
                                    <td>
                                        <a href="{{ route('escalation-category.subcategories.show', [$category, $subcategory]) }}" class="btn btn-info btn-xs">View</a>
                                        <a href="{{ route('escalation-category.subcategories.edit', [$category, $subcategory]) }}" class="btn btn-warning btn-xs">Edit</a>
                                        @if($subcategory->status === 'Active')
                                            <form action="{{ route('escalation-category.subcategories.inactive', [$category, $subcategory]) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Mark as inactive?')">Inactivate</button>
                                            </form>
                                        @else
                                            <form action="{{ route('escalation-category.subcategories.active', [$category, $subcategory]) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-success btn-xs" onclick="return confirm('Mark as active?')">Activate</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $subcategories->links() }}
                    <a href="{{ route('escalation-category.index') }}" class="btn btn-secondary btn-xs mt-2">Back to Categories</a>
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
    $('#subcategories-table').DataTable({
        responsive: true
    });
});
</script>
@endsection

