@props([
    'name',
    'id' => null,
    'label',
    'tip' => null,
    'value' => '',
    'placeholder' => '',
    'type' => 'text',
    'rows' => 3,
    'required' => false,
    'optimalMin' => 0,
    'optimalMax' => 0,
    'hardMax' => 0,
    'unit' => 'characters',
    'optimalLabel' => null,
    'translatable' => false,
    'errorKey' => null,
])

@php
    // Allow array names like "data[website_name]" by deriving a safe id when not given.
    $resolvedId = $id ?: preg_replace('/[^A-Za-z0-9_\-]/', '_', $name);
    $resolvedId = trim($resolvedId, '_');

    $optimalLabelText = $optimalLabel ?? ($optimalMin && $optimalMax ? "Optimal: {$optimalMin}–{$optimalMax} {$unit}" : null);
    $resolvedErrorKey = $errorKey ?: $name;
    $oldValue = old($resolvedErrorKey, $value);
@endphp

<div class="fv-row mb-7" data-seo-field="true">
    <label class="form-label fw-semibold fs-6 d-flex align-items-center" for="{{ $resolvedId }}">
        @if($translatable)
            <i class="bi bi-translate text-primary me-1" data-bs-toggle="tooltip"
               title="{{ __('Translatable') }}"></i>
        @endif
        <span>{{ __($label) }}</span>
        @if($required)
            <span class="text-danger ms-1">*</span>
        @endif
    </label>

    @if($type === 'textarea')
        <textarea
            id="{{ $resolvedId }}"
            name="{{ $name }}"
            rows="{{ $rows }}"
            placeholder="{{ $placeholder }}"
            class="form-control form-control-solid seo-input"
            data-seo-counter="true"
            data-seo-optimal-min="{{ $optimalMin }}"
            data-seo-optimal-max="{{ $optimalMax }}"
            data-seo-hard-max="{{ $hardMax }}"
            data-seo-unit="{{ $unit }}"
        >{{ $oldValue }}</textarea>
    @else
        <input
            type="{{ $type }}"
            id="{{ $resolvedId }}"
            name="{{ $name }}"
            value="{{ $oldValue }}"
            placeholder="{{ $placeholder }}"
            class="form-control form-control-solid seo-input"
            data-seo-counter="true"
            data-seo-optimal-min="{{ $optimalMin }}"
            data-seo-optimal-max="{{ $optimalMax }}"
            data-seo-hard-max="{{ $hardMax }}"
            data-seo-unit="{{ $unit }}"
        />
    @endif

    <div class="d-flex flex-stack mt-2 gap-3 flex-wrap">
        <div class="d-flex align-items-center text-muted fs-7">
            @if($tip)
                <i class="bi bi-lightbulb-fill text-warning me-2"></i>
                <span><strong class="text-dark">{{ __('SEO Tip') }}:</strong> {{ __($tip) }}</span>
            @endif
        </div>
        <div class="d-flex align-items-center gap-3 flex-shrink-0 ms-auto">
            @if($optimalLabelText)
                <span class="badge badge-light-info fs-8 fw-semibold">
                    <i class="bi bi-bullseye me-1"></i>{{ __($optimalLabelText) }}
                </span>
            @endif
            <span class="badge badge-light fs-8 fw-bold seo-counter-badge">
                <span class="seo-counter-value">0</span>
                <span class="ms-1">/</span>
                <span class="ms-1">{{ $optimalMax ?: ($hardMax ?: '∞') }}</span>
                <span class="ms-1">{{ __($unit) }}</span>
            </span>
        </div>
    </div>

    @if($errors->has($resolvedErrorKey))
        <div class="text-danger fs-7 mt-1">{{ $errors->first($resolvedErrorKey) }}</div>
    @endif
</div>
