@props([
    'label' => null,
    'name' => null,
    'required' => false,
    'translatable' => false,
    'helper' => null,
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'fv-row mb-7']) }}>
    @if($label)
        <label class="form-label fw-semibold fs-6 d-flex align-items-center" @if($name) for="{{ $name }}" @endif>
            @if($translatable)
                <i class="bi bi-translate text-primary me-1" data-bs-toggle="tooltip"
                   title="{{ __('Translatable') }}"></i>
            @endif
            @if($icon)
                <i class="{{ $icon }} text-primary me-1"></i>
            @endif
            <span>{{ __($label) }}</span>
            @if($required)
                <span class="text-danger ms-1">*</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if($helper)
        <div class="form-text text-muted mt-2">{!! __($helper) !!}</div>
    @endif

    @if($name && $errors->has($name))
        <div class="text-danger fs-7 mt-2">{{ $errors->first($name) }}</div>
    @endif
</div>
