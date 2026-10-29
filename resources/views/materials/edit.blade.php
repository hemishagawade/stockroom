@extends('layouts.app')

@section('title', 'Edit material')
@section('breadcrumb', 'Edit material')

@section('content')
    <div class="page-heading">
        <h1>Edit material</h1>
        <p class="page-description">Update the material details. Recorded stock movements stay unchanged.</p>
    </div>
    <div class="form-layout form-layout-single">
        @include('materials._form')
    </div>
@endsection
