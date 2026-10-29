@extends('layouts.app')

@section('title', $editing ? 'Edit movement' : 'Record movement')
@section('breadcrumb', $editing ? 'Edit movement' : 'Stock movement')

@section('content')
    <div class="page-heading">
        <p class="eyebrow">STOCK CONTROL</p>
        <h1>{{ $editing ? 'Edit stock movement' : 'Record stock movement' }}</h1>
        <p class="page-description">Positive quantities add stock. Negative quantities remove stock.</p>
    </div>

    <div class="form-layout form-layout-single">
        <form class="panel form-panel" method="POST" action="{{ $editing ? route('stock-movements.update', $movement) : route('stock-movements.store') }}" data-validate data-has-errors="{{ $errors->any() ? 'true' : 'false' }}" novalidate>
            @csrf
            @if ($editing)
                @method('PUT')
                <div class="material-id-note">Internal inward ID <strong>IN-{{ str_pad((string) $movement->id, 4, '0', STR_PAD_LEFT) }}</strong></div>
            @endif
            <div class="form-heading">
                <h2>Movement details</h2>
            </div>

            <label class="field-label" for="category_id">Material category <span class="required">*</span></label>
            <select id="category_id" name="category_id" required aria-invalid="{{ $errors->has('category_id') ? 'true' : 'false' }}" aria-describedby="category_id-error">
                <option value="">Select a category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id', $movement->material?->category_id) === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <span class="field-error" id="category_id-error" data-error-for="category_id" @if (!$errors->has('category_id')) hidden @endif>{{ $errors->first('category_id') }}</span>

            <label class="field-label" for="material_id">Material name <span class="required">*</span></label>
            <select id="material_id" name="material_id" data-validation="category-material" required aria-invalid="{{ $errors->has('material_id') ? 'true' : 'false' }}" aria-describedby="material_id-error">
                <option value="">Select a material</option>
                @foreach ($categories as $category)
                    @foreach ($category->materials as $material)
                        <option value="{{ $material->id }}" data-category="{{ $category->id }}" @selected((string) old('material_id', $movement->material_id) === (string) $material->id)>{{ $material->name }}</option>
                    @endforeach
                @endforeach
            </select>
            <span class="field-error" id="material_id-error" data-error-for="material_id" @if (!$errors->has('material_id')) hidden @endif>{{ $errors->first('material_id') }}</span>

            <label class="field-label" for="movement_date">Date <span class="required">*</span></label>
            <input id="movement_date" name="movement_date" type="date" value="{{ old('movement_date', $movement->movement_date?->format('Y-m-d')) }}" required aria-invalid="{{ $errors->has('movement_date') ? 'true' : 'false' }}" aria-describedby="movement_date-error">
            <span class="field-error" id="movement_date-error" data-error-for="movement_date" @if (!$errors->has('movement_date')) hidden @endif>{{ $errors->first('movement_date') }}</span>

            <label class="field-label" for="quantity">Inward / outward quantity <span class="required">*</span></label>
            <div class="input-suffix">
                <input id="quantity" name="quantity" type="number" step="0.01" min="-9999999999.99" max="9999999999.99" data-validation="decimal" value="{{ old('quantity', $movement->quantity) }}" placeholder="e.g. 12.50 or -3" required aria-invalid="{{ $errors->has('quantity') ? 'true' : 'false' }}" aria-describedby="quantity-hint quantity-error">
                <span>units</span>
            </div>
            <span class="field-error" id="quantity-error" data-error-for="quantity" @if (!$errors->has('quantity')) hidden @endif>{{ $errors->first('quantity') }}</span>

            <div class="form-actions">
                <a class="button button-secondary" href="{{ route('stock-movements.index') }}">Cancel</a>
                <button class="button button-primary" type="submit">{{ $editing ? 'Update movement' : 'Save movement' }} <span aria-hidden="true">→</span></button>
            </div>
        </form>

    </div>
@endsection

@push('scripts')
    <script>
        // Filter available material options when the user changes category.
        const categorySelect = document.querySelector('#category_id');
        const materialSelect = document.querySelector('#material_id');
        const filterMaterials = () => {
            const categoryId = categorySelect.value;
            let selectedIsAvailable = false;
            Array.from(materialSelect.options).forEach((option, index) => {
                if (index === 0) return;
                const visible = option.dataset.category === categoryId;
                option.hidden = !visible;
                if (option.value === materialSelect.value && visible) selectedIsAvailable = true;
            });
            if (!selectedIsAvailable) materialSelect.value = '';
            materialSelect.disabled = false;
        };
        categorySelect.addEventListener('change', filterMaterials);
        filterMaterials();
    </script>
@endpush
