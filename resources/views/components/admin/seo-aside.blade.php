@props([
    'title' => 'SEO Health',
    'checks' => [],
])

@php
    $arcRadius = 52;
    $arcCircumference = (int) round(2 * M_PI * $arcRadius);
@endphp

<div {{ $attributes->merge(['class' => 'card card-flush mb-7']) }} data-seo-health>
    <div class="card-header">
        <div class="card-title">
            <h2 class="d-flex align-items-center">
                <i class="bi bi-graph-up-arrow text-primary fs-3 me-2"></i>
                {{ __($title) }}
            </h2>
        </div>
    </div>
    <div class="card-body pt-0">

        {{-- =========== Score Donut =========== --}}
        <div class="text-center mb-6" data-seo-score-summary>
            <div class="position-relative d-inline-block mb-3">
                <svg width="130" height="130" viewBox="0 0 130 130" class="seo-score-donut">
                    <circle cx="65" cy="65" r="{{ $arcRadius }}"
                            fill="none" stroke="var(--bs-gray-200)" stroke-width="9"/>
                    <circle cx="65" cy="65" r="{{ $arcRadius }}"
                            fill="none" stroke="var(--bs-success)" stroke-width="9"
                            stroke-linecap="round"
                            stroke-dasharray="{{ $arcCircumference }}"
                            stroke-dashoffset="{{ $arcCircumference }}"
                            data-seo-score-arc
                            data-seo-arc-circumference="{{ $arcCircumference }}"
                            transform="rotate(-90 65 65)"/>
                </svg>
                <div class="position-absolute top-50 start-50 translate-middle text-center">
                    <div class="fs-1 fw-bolder text-dark lh-1">
                        <span data-seo-score-value>0</span>
                    </div>
                    <div class="fs-8 text-muted fw-semibold">/ 100</div>
                </div>
            </div>
            <div class="fs-7 fw-semibold text-uppercase letter-spacing-1"
                 data-seo-score-label>{{ __('Calculating...') }}</div>
        </div>

        <div class="separator separator-dashed mb-5"></div>

        {{-- =========== Checklist =========== --}}
        <div class="seo-checklist d-flex flex-column gap-3">
            @foreach($checks as $check)
                <div class="seo-check"
                     data-seo-check
                     data-seo-target="{{ $check['target'] ?? '' }}"
                     data-seo-rule="{{ $check['rule'] ?? 'length' }}"
                     @if(isset($check['min'])) data-seo-min="{{ $check['min'] }}" @endif
                     @if(isset($check['max'])) data-seo-max="{{ $check['max'] }}" @endif
                     @if(isset($check['hardMax'])) data-seo-hard-max="{{ $check['hardMax'] }}" @endif
                     @if(isset($check['initial'])) data-seo-initial="{{ $check['initial'] }}" @endif>
                    <div class="d-flex align-items-start">
                        <span class="seo-check-icon flex-shrink-0 me-3 d-inline-flex align-items-center justify-content-center"
                              aria-hidden="true">
                            <i class="bi bi-circle"></i>
                        </span>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex flex-stack">
                                <span class="seo-check-label fw-semibold text-dark fs-7 text-truncate">
                                    {{ __($check['label'] ?? '') }}
                                </span>
                                <span class="seo-check-status badge badge-light fs-9 fw-bold ms-2"></span>
                            </div>
                            @if(! empty($check['hint']))
                                <div class="seo-check-hint text-muted fs-8 mt-1">
                                    {{ __($check['hint']) }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
