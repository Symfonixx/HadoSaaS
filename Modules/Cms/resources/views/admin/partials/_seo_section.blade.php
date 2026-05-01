@props([
    'metaTitle' => '',
    'metaDescription' => '',
    'metaKeywords' => '',
    'metaImagePreview' => null,
    'titleSource' => '#title',
    'descSource' => '#meta_description',
    'slugSource' => '#slug',
    'baseUrl' => 'https://example.com/',
])

<div class="card card-flush mb-7" id="seo_section">
    <div class="card-header">
        <div class="card-title">
            <h2 class="d-flex align-items-center">
                <i class="bi bi-search text-primary fs-3 me-2"></i>
                {{ __('SEO Settings') }}
            </h2>
        </div>
    </div>
    <div class="card-body pt-0">
        <p class="text-muted mb-7">
            {{ __('Optimize how this content appears in search engines and on social networks.') }}
        </p>

        {{-- Search Preview --}}
        <div class="mb-7">
            <label class="form-label fw-semibold fs-6">{{ __('Search engine listing preview') }}</label>
            <div class="cms-search-preview"
                 data-seo-preview="true"
                 data-title-source="{{ $titleSource }}"
                 data-desc-source="{{ $descSource }}"
                 data-slug-source="{{ $slugSource }}"
                 data-base-url="{{ $baseUrl }}"
                 data-default-title="{{ $metaTitle }}"
                 data-default-desc="{{ $metaDescription }}">
                <div class="preview-title">{{ $metaTitle ?: __('Your title here') }}</div>
                <div class="preview-url">{{ $baseUrl }}</div>
                <div class="preview-description">{{ $metaDescription ?: __('Your meta description preview will appear here.') }}</div>
            </div>
        </div>

        <div class="separator separator-dashed mb-7"></div>

        {{-- Meta Image --}}
        <x-admin.form-group :label="__('Meta Image')"
                            :helper="__('SEO Tip: Use a 1200×630 image for best results on social platforms (Open Graph / Twitter Card).')">
            <x-admin.image-input name="meta_img" :preview="$metaImagePreview"/>
        </x-admin.form-group>

        {{-- Meta Title --}}
        <x-admin.seo-field
            name="meta_title"
            label="Meta Title"
            tip="Aim for 50–60 characters. Place the most important keyword near the beginning."
            optimal-label="Optimal: 50–60 characters"
            :value="$metaTitle"
            placeholder="{{ __('Meta Title') }}"
            translatable
            :optimal-min="50"
            :optimal-max="60"
            :hard-max="70"
            unit="characters"
        />

        {{-- Meta Description --}}
        <x-admin.seo-field
            name="meta_description"
            label="Meta Description"
            tip="A compelling summary in 120–160 characters increases the click-through rate."
            optimal-label="Optimal: 120–160 characters"
            :value="$metaDescription"
            type="textarea"
            placeholder="{{ __('Meta Description') }}"
            translatable
            :optimal-min="120"
            :optimal-max="160"
            :hard-max="255"
            unit="characters"
        />

        {{-- Meta Keywords --}}
        <x-admin.seo-field
            name="meta_keywords"
            label="Meta Keywords"
            tip="Use 3–8 highly relevant keywords separated by commas."
            optimal-label="Optimal: 3–8 keywords"
            :value="$metaKeywords"
            placeholder="keyword 1, keyword 2, keyword 3"
            translatable
            :optimal-min="3"
            :optimal-max="8"
            unit="keywords"
        />
    </div>
</div>
