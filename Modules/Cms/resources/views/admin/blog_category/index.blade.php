@section('title' , __('Blog Categories'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => 'Blog Categories'],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Blog Categories')" :breadcrumbItems="$breadcrumbItems"/>
    <div class="d-flex align-items-center gap-2 gap-lg-3">
        <button class="btn btn-sm fw-bold btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
            {{__('Add New Blog Category')}}
        </button>
    </div>
@endsection
@section('js')
@endsection
<x-admin-layout>
    <x-admin.table :model="$model" search="Search In Blog Categories"
                   :form-url="route('admin.blogs_categories.deleteMulti')">
        <thead>
        <tr class="text-start text-muted fw-bold fs-7 gs-0">
            <th class="w-10px pe-2" data-orderable="false">
                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                    <input class="form-check-input" type="checkbox" data-kt-check="true"
                           data-kt-check-target="#dataTable .form-check-input" value="1"/>
                </div>
            </th>
            <th class="min-w-200px">{{__('Name')}}</th>
            <th class="min-w-200px">{{__('Url')}}</th>
            <th class="min-w-150px">{{__('Created At')}}</th>
            <th class="min-w-200px text-end rounded-end"></th>
        </thead>
        <tbody class="text-gray-600 fw-semibold">
        @foreach($model as $category)
            <tr>
                <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" name="ids[]" value="{{$category->id}}"/>
                    </div>
                </td>
                <td>{{$category->name}}</td>
                <td>{{$category->slug}}</td>
                <td>{{$category->created_at->diffForHumans() }}</td>
                <td class="text-end">
                    <button type="button" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
                            data-bs-toggle="modal" data-bs-target="#editCategoryModal{{$category->id}}"
                            title="{{ __('Edit Category') }}">
                        <i class="ki-duotone ki-message-edit fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </x-admin.table>
    @foreach($model as $category)
        @include('cms::admin.blog_category.edit', ['blogs_category' => $category, 'modal' => true])
    @endforeach
    @include('cms::admin.blog_category.create', ['modal' => true])
</x-admin-layout>
