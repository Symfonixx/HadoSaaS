@section('title' , __('Website Configurations'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => 'Website Configurations'],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Website Configurations')" :breadcrumbItems="$breadcrumbItems"/>
    <div class="d-flex align-items-center gap-2 gap-lg-3"></div>
@endsection

<x-admin-layout>
    <x-admin.create-card title="Website Configurations" :formUrl="route('admin.settings.store')">
        <div class="row mb-10">
            <!--begin::Col-->
            <div class="col-xl-3 mb-5">
                <div class="fs-6 fw-bold mt-2 mb-5">{{__('Transparent Logo')}}</div>
                <x-admin.image-input
                    name="imgs[white_logo]"
                    :preview="asset('storage/' . $settings->get('white_logo' ,'default.jpg'))"
                    mediaInputName="imgs_media[white_logo]"/>
                <!--begin::Hint-->
                <div class="form-text"> 75px * 150px</div>
                <!--end::Hint-->
            </div>
            <!--end::Col-->

            <!--begin::Col-->
            <div class="col-xl-3 mb-5">
                <div class="fs-6 fw-bold mt-2 mb-5">{{__('Dark Logo')}}</div>
                <x-admin.image-input
                    name="imgs[black_logo]"
                    :preview="asset('storage/' . $settings->get('black_logo' ,'default.jpg'))"
                    mediaInputName="imgs_media[black_logo]"/>
                <!--begin::Hint-->
                <div class="form-text"> 238px * 51px</div>
                <!--end::Hint-->
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-3 mb-5">
                <div class="fs-6 fw-bold mt-2 mb-5">{{__('Meta Image')}}</div>
                <x-admin.image-input
                    name="imgs[meta_img]"
                    :preview="asset('storage/' . $settings->get('meta_img' ,'default.jpg'))"
                    mediaInputName="imgs_media[meta_img]"/>
                <!--begin::Hint-->
                <div class="form-text"> 600px * 600px</div>
                <!--end::Hint-->
            </div>
            <!--end::Col-->

        </div>
        <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i
                        class="bi bi-phone mx-1 text-primary"></i> {{__('Website Phone')}}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <input type="text" class="form-control form-control-solid" name="data[phone]"
                       value="{{$settings->get('phone')}}" placeholder="00905234***"/>
            </div>
        </div>

        <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i
                        class="bi bi-envelope mx-1 text-primary"></i> {{__('Website Email')}}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <input type="text" class="form-control form-control-solid" name="data[email]"
                       value="{{$settings->get('email')}}" placeholder="support@example.com"/>
            </div>
        </div>

        <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i
                        class="bi bi-geo-fill mx-1 text-primary"></i> {{__('Website Address')}}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <input type="text" class="form-control form-control-solid" name="data[address]"
                       value="{{$settings->get('address')}}" placeholder="California, TX 70240"/>
            </div>
        </div>

        <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i
                        class="bi bi-map mx-1 text-primary"></i> {{__('Website Map')}}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <textarea name="data[map]"
                          class="form-control form-control-solid h-150px">{{$settings->get('map')}}</textarea>
            </div>
            <!--begin::Col-->
        </div>

        <h5 class="my-3 fw-bold text-primary">{{__('Social Media')}}</h5>
        <hr/>
        <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i
                        class="bi bi-whatsapp mx-1 text-success"></i> {{__('Whatsapp')}}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <input type="text" class="form-control form-control-solid" name="data[whatsapp]"
                       value="{{$settings->get('whatsapp')}}" placeholder="90564xxxxxxx"/>
            </div>
        </div>
        <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i
                        class="bi bi-facebook mx-1 text-primary"></i> {{__('Facebook')}}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <input type="text" class="form-control form-control-solid" name="data[facebook]"
                       value="{{$settings->get('facebook')}}" placeholder="https://www.facebook.com/xxxx"/>
            </div>
        </div>
        <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i
                        class="bi bi-instagram mx-1 text-danger"></i> {{__('Instagram')}}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <input type="text" class="form-control form-control-solid" name="data[instagram]"
                       value="{{$settings->get('instagram')}}" placeholder="https://www.instagram.com/xxxx"/>
            </div>
        </div>
        <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i class="bi bi-youtube mx-1 text-danger"></i> {{__('Youtube')}}
                </div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <input type="text" class="form-control form-control-solid" name="data[youtube]"
                       value="{{$settings->get('youtube')}}" placeholder="https://www.youtube.com/xxxx"/>
            </div>
        </div>
        <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i
                        class="bi bi-twitter mx-1 text-primary"></i> {{__('Twitter')}}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <input type="text" class="form-control form-control-solid" name="data[twitter]"
                       value="{{$settings->get('twitter')}}" placeholder="https://www.twitter.com/xxxx"/>
            </div>
        </div>


    </x-admin.create-card>
</x-admin-layout>
