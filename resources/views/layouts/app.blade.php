<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f6f2">
    <title>@yield('title', 'Stockroom') · {{ config('app.name', 'Stockroom') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <a class="brand" href="{{ route('materials.index') }}" aria-label="Stockroom home">
                <span class="brand-mark" aria-hidden="true">S</span>
                <span>stockroom<span class="brand-period">.</span></span>
            </a>

            <nav class="navigation" aria-label="Main navigation">
                <a class="nav-link {{ request()->routeIs('materials.index') ? 'active' : '' }}" href="{{ route('materials.index') }}">
                    <span class="nav-icon" aria-hidden="true">▦</span> Manage materials
                </a>
                <a class="nav-link {{ request()->routeIs('stock-movements.*') ? 'active' : '' }}" href="{{ route('stock-movements.index') }}">
                    <span class="nav-icon" aria-hidden="true">↕</span> Stock movement
                </a>
                <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                    <span class="nav-icon" aria-hidden="true">◇</span> Categories
                </a>
            </nav>

        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="breadcrumb">Inventory <span>/</span> <strong>@yield('breadcrumb', 'Manage materials')</strong></div>
            </header>

            <div class="page-content">
                @if (session('success'))
                    <div class="alert alert-success" role="status">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-error" role="alert">{{ session('error') }}</div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
    @stack('scripts')
    <script>
        // Add immediate field feedback while Laravel remains responsible for authoritative validation.
        document.querySelectorAll('form[data-validate]').forEach((form) => {
            let showValidation = form.dataset.hasErrors === 'true';

            const validateField = (field) => {
                let message = '';
                const value = field.value.trim();
                field.setCustomValidity('');

                if (field.required && !value) {
                    message = 'This field is required.';
                } else if (value && field.dataset.validation === 'material-name' &&
                    !/^[A-Za-z0-9][A-Za-z0-9 ._-]*$/.test(value)) {
                    message = 'Use letters, numbers, spaces, hyphens, underscores or periods.';
                } else if (value && field.dataset.validation === 'material-name') {
                    const categoryId = form.querySelector('[name="category_id"]').value;
                    const existing = JSON.parse(form.dataset.existingMaterials || '{}')[categoryId] || [];
                    if (existing.some((name) => name.toLowerCase() === value.toLowerCase())) {
                        message = 'A material with this name already exists in the selected category.';
                    }
                } else if (value && field.dataset.validation === 'category-name') {
                    const existing = JSON.parse(form.dataset.existingNames || '[]');
                    if (existing.some((name) => name.toLowerCase() === value.toLowerCase())) {
                        message = 'A category with this name already exists.';
                    }
                } else if (value && field.dataset.validation === 'decimal' &&
                    !/^-?\d+(?:\.\d{1,2})?$/.test(value)) {
                    message = 'Enter a number with no more than 2 decimal places.';
                } else if (field.dataset.validation === 'category-material' && value) {
                    const categoryId = form.querySelector('[name="category_id"]').value;
                    const option = field.selectedOptions[0];
                    if (!option || option.dataset.category !== categoryId) {
                        message = 'Choose a material in the selected category.';
                    }
                }

                if (!message && value && field.maxLength >= 0 && value.length > field.maxLength) {
                    message = `Use no more than ${field.maxLength} characters.`;
                }

                if (!message && value && !field.validity.valid) {
                    message = field.validationMessage || 'Enter a valid value.';
                }

                field.setCustomValidity(message);
                field.setAttribute('aria-invalid', message ? 'true' : 'false');
                const error = form.querySelector(`[data-error-for="${field.id}"]`);
                if (error) {
                    error.textContent = message;
                    error.hidden = !message;
                }
                return !message;
            };

            form.querySelectorAll('input, select, textarea').forEach((field) => {
                field.addEventListener('input', () => {
                    if (showValidation) validateField(field);
                });
                field.addEventListener('change', () => {
                    if (showValidation) validateField(field);
                });
                field.addEventListener('blur', () => {
                    if (showValidation) validateField(field);
                });
                field.addEventListener('change', () => {
                    if (showValidation && field.name === 'category_id') {
                        form.querySelectorAll('[data-validation="material-name"], [data-validation="category-material"]')
                            .forEach((dependentField) => validateField(dependentField));
                    }
                });
            });

            form.addEventListener('submit', (event) => {
                showValidation = true;
                const fields = Array.from(form.querySelectorAll('input, select, textarea'));
                let firstInvalid = null;
                fields.forEach((field) => {
                    if (!validateField(field) && !firstInvalid) firstInvalid = field;
                });
                if (firstInvalid) {
                    event.preventDefault();
                    firstInvalid.focus();
                }
            });
        });
    </script>
</body>
</html>
