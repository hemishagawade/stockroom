@extends('layouts.app')

@section('title', 'Add category')
@section('breadcrumb', 'Add category')

@section('content')
    <div class="page-heading">
        <h1>Add a category</h1>
        <p class="page-description">Create a category for organizing materials.</p>
    </div>
    <div class="form-layout form-layout-single">
        @include('categories._form')
    </div>
@endsection
