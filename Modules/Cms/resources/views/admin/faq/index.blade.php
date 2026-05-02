@section('title' , __('FAQs'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => 'FAQs'],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('FAQs')" :breadcrumbItems="$breadcrumbItems"/>
    <div class="d-flex align-items-center gap-2 gap-lg-3">
        <a class="btn btn-sm fw-bold btn-primary" href="{{ route('admin.faqs.create') }}">
            {{ __('Add New FAQ') }}
        </a>
    </div>
@endsection

<x-admin-layout>
    <x-admin.table :model="$model" search="Search In FAQs" :form-url="route('admin.faqs.deleteMulti')">
        <thead>
        <tr class="text-start text-muted fw-bold fs-7 gs-0">
            <th class="w-10px pe-2" data-orderable="false">
                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                    <input class="form-check-input" type="checkbox" data-kt-check="true"
                           data-kt-check-target="#dataTable .form-check-input" value="1"/>
                </div>
            </th>
            <th class="min-w-250px">{{ __('Question') }}</th>
            <th class="min-w-120px">{{ __('Rank') }}</th>
            <th class="min-w-150px">{{ __('Publish Status') }}</th>
            <th class="min-w-150px">{{ __('Created At') }}</th>
            <th class="min-w-100px text-end rounded-end"></th>
        </tr>
        </thead>
        <tbody class="text-gray-600 fw-semibold">
        @foreach($model as $faq)
            <tr>
                <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" name="ids[]" value="{{ $faq->id }}"/>
                    </div>
                </td>
                <td>
                    <div class="fw-bold">{{ $faq->question }}</div>
                </td>
                <td>{{ $faq->rank }}</td>
                <td>
                    <span
                        class="badge badge-light-{{ $faq->status == 'Published' ? 'success' : 'warning' }} fs-7 fw-bold">{{ __($faq->status) }}</span>
                </td>
                <td>{{ $faq->created_at->diffForHumans() }}</td>
                <td class="text-end">
                    <a href="{{ route('admin.faqs.edit', $faq->id) }}"
                       class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
                        <i class="ki-duotone ki-message-edit fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </x-admin.table>
</x-admin-layout>
