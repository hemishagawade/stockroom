@extends('layouts.app')

@section('title', 'Categories')
@section('breadcrumb', 'Categories')

@section('content')
    <div class="page-heading heading-with-action">
        <div>
            <p class="eyebrow">INVENTORY SETUP</p>
            <h1>Categories</h1>
            <p class="page-description">Organize your materials into categories that fit your business.</p>
        </div>
        <a class="button button-primary" href="{{ route('categories.create') }}"><span aria-hidden="true">＋</span> Add category</a>
    </div>

    <div class="panel category-list-panel">
        <div class="panel-header">
            <div>
                <h2>All categories</h2>
                <p class="panel-subtitle">Categories with assigned materials cannot be deleted.</p>
            </div>
            <span class="count-pill">{{ $categories->total() }}</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>CATEGORY</th><th>MATERIALS</th><th class="actions-heading">ACTIONS</th></tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td class="material-cell">{{ $category->name }}</td>
                            <td>{{ $category->materials_count }} {{ \Illuminate\Support\Str::plural('material', $category->materials_count) }}</td>
                            <td class="actions-cell">
                                <span class="row-actions">
                                    <a class="row-action" href="{{ route('categories.edit', $category) }}">Edit</a>
                                    <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="row-action delete" type="submit">Delete</button>
                                    </form>
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="table-empty" colspan="3">No categories yet. Add one to get started.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($categories->hasPages())
            <div class="pagination-wrap">{{ $categories->links() }}</div>
        @endif
    </div>
@endsection
