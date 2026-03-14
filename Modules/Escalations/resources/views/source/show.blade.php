@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Source Details')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Source Details</h4>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">ID</dt>
                        <dd class="col-sm-9">{{ $source->id }}</dd>
                        <dt class="col-sm-3">Name</dt>
                        <dd class="col-sm-9">{{ $source->name }}</dd>
                        <dt class="col-sm-3">Description</dt>
                        <dd class="col-sm-9">{{ $source->description }}</dd>
                    </dl>
                    <a href="{{ route('sources.edit', $source) }}" class="btn btn-warning btn-xs">Edit</a>
                    <a href="{{ route('sources.index') }}" class="btn btn-secondary btn-xs">Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

