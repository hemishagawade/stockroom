<form class="panel form-panel" method="POST" action="{{ $material->exists ? route('materials.update', $material) : route('materials.store') }}" data-validate data-has-errors="{{ $errors->any() ? 'true' : 'false' }}" data-existing-materials="{{ json_encode($existingMaterials) }}" novalidate>
    @csrf
    @if ($material->exists)
        @method('PUT')
    @endif
    <div class="form-heading">
        <h2>Material details</h2>
    </div>
    @if ($material->exists)
        <div class="material-id-note">Internal material ID <strong>MAT-{{ str_pad((string) $material->id, 4, '0', STR_PAD_LEFT) }}</strong></div>
    @endif
    <label class="field-label" for="category_id">Category <span class="required">*</span></label>
    <select id="category_id" name="category_id" required aria-invalid="{{ $errors->has('category_id') ? 'true' : 'false' }}" aria-describedby="category_id-error">
        <option value="">Select a category</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected((string) old('category_id', $material->category_id) === (string) $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
    <span class="field-error" id="category_id-error" data-error-for="category_id" @if (!$errors->has('category_id')) hidden @endif>{{ $errors->first('category_id') }}</span>

    <label class="field-label" for="name">Material name <span class="required">*</span></label>
    <input id="name" name="name" type="text" maxlength="80" pattern="[A-Za-z0-9][A-Za-z0-9 ._\x2D]*" data-validation="material-name" value="{{ old('name', $material->name) }}" placeholder="e.g. Stainless steel sheet" autocomplete="off" required aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" aria-describedby="name-hint name-error">
    <span class="field-error" id="name-error" data-error-for="name" @if (!$errors->has('name')) hidden @endif>{{ $errors->first('name') }}</span>

    <label class="field-label" for="opening_balance">Opening balance <span class="required">*</span></label>
    <div class="input-suffix">
        <input id="opening_balance" name="opening_balance" type="number" min="0" max="9999999999.99" step="0.01" data-validation="decimal" value="{{ old('opening_balance', $material->opening_balance) }}" placeholder="0.00" required aria-invalid="{{ $errors->has('opening_balance') ? 'true' : 'false' }}" aria-describedby="opening_balance-hint opening_balance-error">
        <span>units</span>
    </div>
    <span class="field-error" id="opening_balance-error" data-error-for="opening_balance" @if (!$errors->has('opening_balance')) hidden @endif>{{ $errors->first('opening_balance') }}</span>

    <div class="form-actions">
        <a class="button button-secondary" href="{{ route('materials.index') }}">Cancel</a>
        <button class="button button-primary" type="submit">{{ $material->exists ? 'Update material' : 'Save material' }} <span aria-hidden="true">→</span></button>
    </div>
</form>
