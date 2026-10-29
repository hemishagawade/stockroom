@extends('layouts.app')

@section('title', 'Add a material')
@section('breadcrumb', 'Add material')

@section('content')
    <div class="page-heading">
        <h1>Add a material</h1>
        <p class="page-description">Add a material and set its opening balance.</p>
    </div>
    <div class="form-layout form-layout-single">
        @include('materials._form')
    </div>
@endsection
