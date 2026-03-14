@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Category Details')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Category Details</div>
                <div class="card-body">
                    <h5>Name: {{ $category->category_name }}</h5>
                    <p>Status: {{ $category->status }}</p>
                    <p>Created By: {{ $category->created_by }}</p>
                    <p>Edited By: {{ $category->edited_by }}</p>
                    <hr>
                    <h6>Subcategories:</h6>
                    <ul>
                        @foreach($category->subcategories as $subcategory)
                            <li>{{ $subcategory->sub_category_name }} ({{ $subcategory->status }})</li>
                        @endforeach
                    </ul>
                    <a href="{{ route('escalation-category.edit', $category) }}" class="btn btn-warning btn-xs">Edit</a>
                    <a href="{{ route('escalation-category.index') }}" class="btn btn-secondary btn-xs">Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

