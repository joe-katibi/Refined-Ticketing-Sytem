@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Ticket Details')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Ticket Details</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr><th>Ticket ID</th><td>{{ $list->ticket_id }}</td></tr>
                        <tr><th>Account Number</th><td>{{ $list->account_number }}</td></tr>
                        <tr><th>Category</th><td>{{ $list->category->category_name ?? '-' }}</td></tr>
                        <tr><th>Subcategory</th><td>{{ $list->subcategory->sub_category_name ?? '-' }}</td></tr>
                        <tr><th>Description</th><td>{{ $list->description }}</td></tr>
                        <tr><th>Priority</th><td>{{ $list->priority }}</td></tr>
                        <tr><th>Status</th><td>
                          @if($list->status == "Escalated-Open")
                          <span class="badge  bg-label-warning">Escalated-Open</span>
                          @else
                          <span class="badge  bg-label-success">Escalated-Closed</span>
                          @endif</td></tr>
                    </table>
                    <div class="d-flex justify-content-between mt-3">
                        <a href="{{ route('list.edit', $list->id) }}" class="btn btn-warning btn-xs">Edit</a>
                        <a href="{{ route('list.index') }}" class="btn btn-secondary btn-xs">Back to List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

