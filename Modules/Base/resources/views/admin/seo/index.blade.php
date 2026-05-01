@section('title', __('Seo Configurations'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => 'Seo Configurations'],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Seo Configurations')" :breadcrumbItems="$breadcrumbItems"/>
    <div class="d-flex align-items-center gap-2 gap-lg-3"></div>
@endsection

@php
    $values = [
        'website_name' => (string) ($seo->get('website_name') ?: ''),
        'main_title' => (string) ($seo->get('main_title') ?: ''),
        'website_desc' => (string) ($seo->get('website_desc') ?: ''),
        'website_keywords' => (string) ($seo->get('website_keywords') ?: ''),
        'about_us' => (string) ($seo->get('about_us') ?: ''),
    ];

    $healthChecks = [
        [
            'target' => '#website_name',
            'rule' => 'length',
            'min' => 3,
            'max' => 30,
            'label' => 'Website name',
            'hint' => 'Used for branding suffix in titles.',
        ],
        [
            'target' => '#main_title',
            'rule' => 'length',
            'min' => 30,
            'max' => 70,
            'hardMax' => 90,
            'label' => 'Main title',
            'hint' => 'Default page title (30–70 chars).',
        ],
        [
            'target' => '#website_desc',
            'rule' => 'length',
            'min' => 120,
            'max' => 160,
            'hardMax' => 255,
            'label' => 'Website description',
            'hint' => 'Default meta description (120–160).',
        ],
        [
            'target' => '#website_keywords',
            'rule' => 'count',
            'min' => 3,
            'max' => 8,
            'label' => 'Website keywords',
            'hint' => '3–8 relevant keywords.',
        ],
        [
            'target' => '#about_us',
            'rule' => 'length',
            'min' => 120,
            'max' => 300,
            'hardMax' => 500,
            'label' => 'About us',
            'hint' => 'Used for the homepage / about section.',
        ],
        [
            'target' => '#robots_txt',
            'rule' => 'presence',
            'label' => 'Robots.txt configured',
            'hint' => 'Tell crawlers which paths to access.',
        ],
    ];
@endphp

<x-admin-layout>
    <x-admin.seo-assets/>

    <form method="POST" action="{{ route('admin.seo.store') }}" enctype="multipart/form-data" id="seo-config-form">
        @csrf

        <div class="row gx-5 gx-xl-10">
            {{-- ===================== MAIN COLUMN ===================== --}}
            <div class="col-xxl-8 col-xl-8 mb-5 mb-xl-0">

                {{-- General SEO --}}
                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-globe text-primary fs-3 me-2"></i>
                                {{ __('General SEO') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <p class="text-muted mb-7">
                            {{ __('These values are used as defaults across the site (titles, meta tags, sharing cards).') }}
                        </p>

                        {{-- Search Preview --}}
                        <div class="mb-7">
                            <label class="form-label fw-semibold fs-6">
                                {{ __('Search engine listing preview') }}
                            </label>
                            <div class="cms-search-preview"
                                 data-seo-preview="true"
                                 data-title-source="#main_title"
                                 data-desc-source="#website_desc"
                                 data-base-url="{{ url('/') }}"
                                 data-default-title="{{ $values['main_title'] }}"
                                 data-default-desc="{{ $values['website_desc'] }}">
                                <div class="preview-title">{{ $values['main_title'] ?: __('Your title here') }}</div>
                                <div class="preview-url">{{ url('/') }}</div>
                                <div class="preview-description">
                                    {{ $values['website_desc'] ?: __('Your meta description preview will appear here.') }}
                                </div>
                            </div>
                        </div>

                        <div class="separator separator-dashed mb-7"></div>

                        {{-- Website Name --}}
                        <x-admin.seo-field
                            name="data[website_name]"
                            id="website_name"
                            error-key="data.website_name"
                            label="Website Name"
                            tip="Short brand name used as the suffix of every page title."
                            optimal-label="Optimal: 3–30 characters"
                            :value="$values['website_name']"
                            placeholder="Hado SaaS"
                            translatable
                            :optimal-min="3"
                            :optimal-max="30"
                            unit="characters"
                        />

                        {{-- Main Title --}}
                        <x-admin.seo-field
                            name="data[main_title]"
                            id="main_title"
                            error-key="data.main_title"
                            label="Website Main Title"
                            tip="Default title shown when a page has no specific title. Aim for 30–70 characters."
                            optimal-label="Optimal: 30–70 characters"
                            :value="$values['main_title']"
                            placeholder="Boost your website traffic today"
                            translatable
                            :optimal-min="30"
                            :optimal-max="70"
                            :hard-max="90"
                            unit="characters"
                        />

                        {{-- Website Description --}}
                        <x-admin.seo-field
                            name="data[website_desc]"
                            id="website_desc"
                            error-key="data.website_desc"
                            label="Website Description"
                            tip="Default meta description used when a page has none. 120–160 characters works best."
                            optimal-label="Optimal: 120–160 characters"
                            :value="$values['website_desc']"
                            type="textarea"
                            placeholder="A concise summary of what your site offers..."
                            translatable
                            :optimal-min="120"
                            :optimal-max="160"
                            :hard-max="255"
                            unit="characters"
                        />

                        {{-- Website Keywords --}}
                        <x-admin.seo-field
                            name="data[website_keywords]"
                            id="website_keywords"
                            error-key="data.website_keywords"
                            label="Website Keywords"
                            tip="Default keywords for the site. Use 3–8 highly relevant terms separated by commas."
                            optimal-label="Optimal: 3–8 keywords"
                            :value="$values['website_keywords']"
                            placeholder="saas, marketing, automation"
                            translatable
                            :optimal-min="3"
                            :optimal-max="8"
                            unit="keywords"
                        />

                        {{-- About Us --}}
                        <x-admin.seo-field
                            name="data[about_us]"
                            id="about_us"
                            error-key="data.about_us"
                            label="About Us"
                            tip="Brief company / product description, used on the homepage and about sections."
                            optimal-label="Optimal: 120–300 characters"
                            :value="$values['about_us']"
                            type="textarea"
                            rows="4"
                            placeholder="We help teams ship faster..."
                            translatable
                            :optimal-min="120"
                            :optimal-max="300"
                            :hard-max="500"
                            unit="characters"
                        />
                    </div>
                </div>

                {{-- Indexing / Robots.txt --}}
                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-robot text-primary fs-3 me-2"></i>
                                {{ __('Indexing') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <x-admin.form-group label="Robots.txt" name="robots_txt"
                                            helper="Controls how search engine crawlers access your website.">
                            <textarea name="robots_txt"
                                      id="robots_txt"
                                      dir="ltr"
                                      spellcheck="false"
                                      class="form-control form-control-solid font-monospace h-200px"
                                      placeholder="User-agent: *&#10;Disallow:">{{ $robotsTxt }}</textarea>
                            <div class="form-text mt-2">
                                {{ __('This file is served at') }}
                                <a href="{{ url('/robots.txt') }}" target="_blank" rel="noopener">{{ url('/robots.txt') }}</a>
                            </div>
                        </x-admin.form-group>
                    </div>
                </div>
            </div>

            {{-- ===================== ASIDE COLUMN ===================== --}}
            <div class="col-xxl-4 col-xl-4">

                {{-- Translations --}}
                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-translate text-primary fs-3 me-2"></i>
                                {{ __('Update Other Languages') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="update_translations"
                                   id="update_translations"
                                   value="1"
                                   @checked(old('update_translations'))/>
                            <label class="form-check-label fs-7 ms-2" for="update_translations">
                                {{ __('Use Google Translate to update all other languages.') }}
                            </label>
                        </div>
                    </div>
                </div>

                {{-- SEO Health --}}
                <x-admin.seo-aside :checks="$healthChecks"/>
            </div>
        </div>

        {{-- Footer --}}
        <div class="d-flex justify-content-end py-6">
            <a href="{{ url()->previous() }}"
               class="btn btn-light btn-active-light-primary me-3">{{ __('Discard') }}</a>
            <button type="submit" class="btn btn-primary" id="submit">
                <span class="indicator-label">{{ __('Save Changes') }}</span>
            </button>
        </div>
    </form>
</x-admin-layout>
