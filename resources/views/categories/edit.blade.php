@extends('layouts.app')

@section('title', 'Edit category')
@section('breadcrumb', 'Edit category')

@section('content')
    <div class="page-heading">
        <h1>Edit category</h1>
        <p class="page-description">Update the category name used by your materials.</p>
    </div>
    <div class="form-layout form-layout-single">
        @include('categories._form')
    </div>
@endsection
