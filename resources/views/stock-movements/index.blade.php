@extends('layouts.app')

@section('title', 'Stock movement')
@section('breadcrumb', 'Stock movement')

@section('content')
    <div class="page-heading heading-with-action">
        <div>
            <p class="eyebrow">STOCK CONTROL</p>
            <h1>Stock movement</h1>
            <p class="page-description">Record incoming stock or reduce stock with an outward quantity.</p>
        </div>
        <a class="button button-primary" href="{{ route('stock-movements.create') }}"><span aria-hidden="true">＋</span> Record movement</a>
    </div>

    <div class="panel">
        <div class="panel-header">
            <div>
                <h2>Movement history</h2>
                <p class="panel-subtitle">Each signed entry updates the material's current balance.</p>
            </div>
            <span class="count-pill">{{ $movements->total() }}</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>INTERNAL ID</th><th>DATE</th><th>CATEGORY</th><th>MATERIAL</th><th>QUANTITY</th><th class="actions-heading">ACTIONS</th></tr>
                </thead>
                <tbody>
                    @forelse ($movements as $movement)
                        <tr>
                            <td><span class="id-code">IN-{{ str_pad((string) $movement->id, 4, '0', STR_PAD_LEFT) }}</span></td>
                            <td>{{ $movement->movement_date->format('M j, Y') }}</td>
                            <td><span class="category-tag">{{ $movement->material?->category?->name ?? 'Archived' }}</span></td>
                            <td class="material-cell">{{ $movement->material?->name ?? 'Deleted material' }}</td>
                            <td class="{{ (float) $movement->quantity < 0 ? 'quantity-negative' : 'quantity-positive' }}">
                                {{ (float) $movement->quantity > 0 ? '+' : '' }}{{ number_format((float) $movement->quantity, 2) }}
                            </td>
                            <td class="actions-cell">
                                @if (! $movement->material?->trashed())
                                    <span class="row-actions">
                                        <a class="row-action" href="{{ route('stock-movements.edit', $movement) }}">Edit</a>
                                        <form method="POST" action="{{ route('stock-movements.destroy', $movement) }}" onsubmit="return confirm('Delete this movement and recalculate the material balance?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="row-action delete" type="submit">Delete</button>
                                        </form>
                                    </span>
                                @else
                                    <span class="field-hint">History retained</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td class="table-empty" colspan="6">No stock movements yet. Record an inward or outward quantity to begin.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($movements->hasPages())
            <div class="pagination-wrap">{{ $movements->links() }}</div>
        @endif
    </div>
@endsection
