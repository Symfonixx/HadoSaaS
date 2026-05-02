@section('title' , __('My Profile'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => 'My Profile'],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('My Profile')" :breadcrumbItems="$breadcrumbItems"/>
@endsection

<x-admin-layout>
    <x-admin.create-card title="My Profile" :formUrl="route('admin.profile.update')">
        @method('PUT')

        <div class="row mb-8">
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3">{{__('Image')}}</div>
            </div>
            <div class="col-xl-9 fv-row">
                <x-admin.image-input name="img" :preview="$user->avatar"/>
                <div class="form-text">{{__('Allowed file types: png, jpg, jpeg, webp')}}</div>
            </div>
        </div>

        <div class="row mb-8">
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3">{{__('Name')}} <span class="text-danger">*</span></div>
            </div>
            <div class="col-xl-9 fv-row">
                <input type="text" class="form-control form-control-solid @error('name') is-invalid @enderror"
                       name="name" value="{{ old('name', $user->name) }}" required>
            </div>
        </div>

        <div class="row mb-8">
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3">{{__('Email')}} <span class="text-danger">*</span></div>
            </div>
            <div class="col-xl-9 fv-row">
                <input type="email" class="form-control form-control-solid @error('email') is-invalid @enderror"
                       name="email" value="{{ old('email', $user->email) }}" required>
            </div>
        </div>

        <div class="row mb-8">
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3">{{__('New Password')}}</div>
            </div>
            <div class="col-xl-9 fv-row">
                <input type="password" class="form-control form-control-solid @error('password') is-invalid @enderror"
                       name="password" autocomplete="new-password">
            </div>
        </div>

        <div class="row mb-8">
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3">{{__('Confirm Password')}}</div>
            </div>
            <div class="col-xl-9 fv-row">
                <input type="password"
                       class="form-control form-control-solid @error('password') is-invalid @enderror"
                       name="password_confirmation" autocomplete="new-password">
            </div>
        </div>
    </x-admin.create-card>
</x-admin-layout>
