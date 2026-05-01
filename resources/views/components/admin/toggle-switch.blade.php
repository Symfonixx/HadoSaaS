@props([
    'name',
    'label',
    'helper' => null,
    'checked' => false,
    'value' => 1,
    'icon' => null,
    'tone' => 'primary',
    'last' => false,
])

<div {{ $attributes->class([
        'd-flex flex-stack py-3',
        'border-bottom border-gray-200 border-bottom-dashed' => ! $last,
    ]) }}>
    <div class="d-flex flex-column flex-grow-1 me-3">
        <label class="form-check-label fw-bold fs-6 mb-1 d-flex align-items-center" for="{{ $name }}">
            @if($icon)
                <i class="{{ $icon }} text-{{ $tone }} me-2"></i>
            @endif
            <span>{{ __($label) }}</span>
        </label>
        @if($helper)
            <span class="text-muted fs-7">{{ __($helper) }}</span>
        @endif
    </div>
    <div class="form-check form-switch form-check-custom form-check-solid form-check-{{ $tone }}">
        <input class="form-check-input h-25px w-45px"
               type="checkbox"
               id="{{ $name }}"
               name="{{ $name }}"
               value="{{ $value }}"
               @checked($checked)/>
    </div>
</div>
