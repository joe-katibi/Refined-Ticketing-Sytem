@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Subcategory Details')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Subcategory Details</div>
                <div class="card-body">
                    <h5>Name: {{ $subcategory->sub_category_name }}</h5>
                    <p>Status: {{ $subcategory->status }}</p>
                    <p>Created By: {{ $subcategory->created_by }}</p>
                    <p>Edited By: {{ $subcategory->edited_by }}</p>
                    <a href="{{ route('escalation-category.subcategories.edit', [$category, $subcategory]) }}" class="btn btn-warning btn-xs">Edit</a>
                    <a href="{{ route('escalation-category.subcategories.index', $category) }}" class="btn btn-secondary btn-xs">Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

