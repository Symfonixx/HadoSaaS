@section('title' , __('Seo Configurations'))

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

<x-admin-layout>
    <x-admin.create-card title="Seo Configurations" :formUrl="route('admin.seo.store')">
        <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i
                        class="bi bi-translate text-primary mx-1 "></i>{{__('Website Name')}}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <input type="text" class="form-control form-control-solid" name="data[website_name]"
                       value="{{$seo->get('website_name')}}" placeholder="Avedi tech"/>
            </div>
        </div>
        <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i
                        class="bi bi-translate text-primary mx-1 "></i> {{__('Website Description')}}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <input type="text" class="form-control form-control-solid" name="data[website_desc]"
                       value="{{$seo->get('website_desc')}}" placeholder="Avedi tech desc .."/>
            </div>
        </div>
        <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i
                        class="bi bi-translate text-primary mx-1 "></i>{{__('Website Keywords')}}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <input type="text" class="form-control form-control-solid" name="data[website_keywords]"
                       value="{{$seo->get('website_keywords')}}" placeholder="Avedi tech marketing web "/>
            </div>
        </div>
        <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i
                        class="bi bi-translate text-primary mx-1 "></i> {{__('About Us')}}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <input type="text" class="form-control form-control-solid" name="data[about_us]"
                       value="{{$seo->get('about_us')}}"
                       placeholder="Required honoured trifling eat pleasure man relation. Assurance yet bed was improving furniture man. "/>
            </div>
        </div>
        <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i
                        class="bi bi-translate text-primary mx-1 "></i>{{__('Website Main Title')}}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <input type="text" class="form-control form-control-solid" name="data[main_title]"
                       value="{{$seo->get('main_title')}}" placeholder="Boots your website traffic today "/>
            </div>
        </div>
        <div class="row mb-8">
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3">{{ __('Update Other Languages') }}</div>
            </div>
            <div class="col-xl-9 fv-row">
                <div class="form-check form-check-custom form-check-solid">
                    <input class="form-check-input" type="checkbox" name="update_translations"
                           id="update_translations" value="1" @checked(old('update_translations'))>
                    <label class="form-check-label" for="update_translations">
                        {{ __('Use Google Translate to update all other languages.') }}
                    </label>
                </div>
            </div>
        </div>
    </x-admin.create-card>
</x-admin-layout>
