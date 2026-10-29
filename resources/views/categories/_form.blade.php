<form class="panel category-form" method="POST" action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}" data-validate data-has-errors="{{ $errors->any() ? 'true' : 'false' }}" data-existing-names="{{ json_encode($existingNames) }}" novalidate>
    @csrf
    @if ($category->exists)
        @method('PUT')
    @endif
    <div class="form-heading">
        <h2>Category details</h2>
    </div>
    <label class="field-label" for="category_name">Category name <span class="required">*</span></label>
    <input id="category_name" name="name" type="text" maxlength="60" data-validation="category-name" value="{{ old('name', $category->name) }}" placeholder="e.g. Raw material" required aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" aria-describedby="category_name-error">
    <span class="field-error" id="category_name-error" data-error-for="category_name" @if (!$errors->has('name')) hidden @endif>{{ $errors->first('name') }}</span>
    <div class="form-actions">
        <a class="button button-secondary" href="{{ route('categories.index') }}">Cancel</a>
        <button class="button button-primary" type="submit">{{ $category->exists ? 'Update category' : 'Add category' }} <span aria-hidden="true">→</span></button>
    </div>
</form>
