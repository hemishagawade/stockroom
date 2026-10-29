@extends('layouts.app')

@section('title', 'Manage materials')
@section('breadcrumb', 'Manage materials')

@section('content')
    <section class="view active">
        <div class="page-heading heading-with-action">
            <div>
                <h1>Manage materials</h1>
                <p class="page-description">Current stock across your material categories.</p>
            </div>
            <a class="button button-primary" href="{{ route('materials.create') }}"><span aria-hidden="true">＋</span> Add material</a>
        </div>

        <div class="stats-grid" aria-label="Inventory summary">
            <article class="stat-card">
                <div class="stat-label">ACTIVE MATERIALS</div>
                <div class="stat-value">{{ number_format($activeMaterialsCount) }}</div>
                <div class="stat-note">Materials currently in use</div>
            </article>
            <article class="stat-card">
                <div class="stat-label">TOTAL ON HAND</div>
                <div class="stat-value">{{ number_format($totalOnHand, 2) }}</div>
                <div class="stat-note">Combined current balance</div>
            </article>
        </div>

        <div class="panel">
            <div class="panel-header">
                <div>
                    <h2>{{ $archived ? 'Archived materials' : 'Material list' }}</h2>
                    <p class="panel-subtitle">Opening stock adjusted by recorded movements.</p>
                </div>
                <label class="filter-label" for="material-status">Show
                    <select id="material-status" class="select-compact" onchange="window.location.href=this.value">
                        <option value="{{ route('materials.index') }}" @selected(! $archived)>Active materials</option>
                        <option value="{{ route('materials.index', ['status' => 'archived']) }}" @selected($archived)>Archived materials</option>
                    </select>
                </label>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>INTERNAL ID</th>
                            <th>CATEGORY</th>
                            <th>MATERIAL</th>
                            <th>OPENING BALANCE</th>
                            <th>CURRENT BALANCE</th>
                            <th class="actions-heading">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($materials as $material)
                            @php
                                // Current stock is the opening balance plus every signed stock movement.
                                $currentBalance = (float) $material->opening_balance + (float) ($material->movements_total ?? 0);
                            @endphp
                            <tr>
                                <td><span class="id-code">MAT-{{ str_pad((string) $material->id, 4, '0', STR_PAD_LEFT) }}</span></td>
                                <td><span class="category-tag">{{ $material->category->name }}</span></td>
                                <td class="material-cell">{{ $material->name }}</td>
                                <td>{{ number_format((float) $material->opening_balance, 2) }}</td>
                                <td class="balance">{{ number_format($currentBalance, 2) }}</td>
                                <td class="actions-cell">
                                    <span class="row-actions">
                                        @if ($archived)
                                            <form method="POST" action="{{ route('materials.restore', $material) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="row-action" type="submit">Restore</button>
                                            </form>
                                        @else
                                            <a class="row-action" href="{{ route('materials.edit', $material) }}">Edit</a>
                                            <form method="POST" action="{{ route('materials.destroy', $material) }}" onsubmit="return confirm('Archive this material? Its stock movement history will be kept.')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="row-action delete" type="submit">Delete</button>
                                            </form>
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="table-empty">
                                    {{ $archived ? 'There are no archived materials.' : 'No materials yet. Add your first material to get started.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($materials->hasPages())
                <div class="pagination-wrap">{{ $materials->links() }}</div>
            @endif
        </div>
    </section>
@endsection
