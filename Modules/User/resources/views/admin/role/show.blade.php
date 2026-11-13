@section('title' , __('View Role Details'))

@section('toolbar')

    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
             ['label' => 'Roles List', 'url' => route('admin.roles.index')],
            ['label' => 'View Role Details'],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Roles')" :breadcrumbItems="$breadcrumbItems"/>
    <div class="d-flex align-items-center gap-2 gap-lg-3">
        @if ($role->name !== 'Admin')
            <form method="POST" action="{{ route('admin.roles.destroy', $role->id) }}">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-danger fw-bold" type="submit">{{ __('Delete Role') }}</button>
            </form>
        @endif
    </div>

@endsection
@section('js')
    <!--begin::Page Vendors Javascript(used by this page)-->
    <script>
        var KTUsersViewRole = function () {
            var t, e, o = () => {
                const r = e.querySelectorAll('[type="checkbox"]'),
                    c = document.querySelector('[data-kt-view-roles-table-select="delete_selected"]');
                r.forEach((t => {
                    t.addEventListener("click", (function () {
                        setTimeout((function () {
                            n()
                        }), 50)
                    }))
                }))
            };
            const n = () => {
                const t = document.querySelector('[data-kt-view-roles-table-toolbar="base"]'),
                    o = document.querySelector('[data-kt-view-roles-table-toolbar="selected"]'),
                    n = document.querySelector('[data-kt-view-roles-table-select="selected_count"]'),
                    r = e.querySelectorAll('tbody [type="checkbox"]');
                let c = !1,
                    l = 0;
                r.forEach((t => {
                    t.checked && (c = !0, l++)
                })), c ? (n.innerHTML = l, t.classList.add("d-none"), o.classList.remove("d-none")) : (t.classList.remove("d-none"), o.classList.add("d-none"))
            };
            return {
                init: function () {
                    (e = document.querySelector("#kt_roles_view_table")) && (e.querySelectorAll("tbody tr").forEach((t => {
                        const e = t.querySelectorAll("td"),
                            o = moment(e[3].innerHTML, "DD MMM YYYY, LT").format();
                        e[3].setAttribute("data-order", o)
                    })), t = $(e).DataTable({
                        info: !1,
                        order: [],
                        pageLength: 5,
                        lengthChange: !1,
                        columnDefs: [{
                            orderable: !1,
                            targets: 0
                        }, {
                            orderable: !1,
                            targets: 4
                        }]
                    }), document.querySelector('[data-kt-roles-table-filter="search"]').addEventListener("keyup", (function (e) {
                        t.search(e.target.value).draw()
                    })), e.querySelectorAll('[data-kt-roles-table-filter="delete_row"]').forEach((e => {
                        e.addEventListener("click", (function (e) {
                            e.preventDefault();
                            const o = e.target.closest("tr"),
                                n = o.querySelectorAll("td")[1].innerText;
                            Swal.fire({
                                text: "Are you sure you want to delete " + n + "?",
                                icon: "warning",
                                showCancelButton: !0,
                                buttonsStyling: !1,
                                confirmButtonText: "{{__('Yes, Delete!')}}",
                                cancelButtonText: "{{__('No, Cancel')}}",
                                customClass: {
                                    confirmButton: "btn fw-bold btn-danger",
                                    cancelButton: "btn fw-bold btn-active-light-primary"
                                }
                            }).then((function (e) {
                                e.value ? Swal.fire({
                                    text: "You have deleted " + n + "!.",
                                    icon: "success",
                                    buttonsStyling: !1,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary"
                                    }
                                }).then((function () {
                                    t.row($(o)).remove().draw()
                                })) : "cancel" === e.dismiss && Swal.fire({
                                    text: customerName + " was not deleted.",
                                    icon: "error",
                                    buttonsStyling: !1,
                                    confirmButtonText: "{{__('Ok, Go It')}}",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary"
                                    }
                                })
                            }))
                        }))
                    })), o())
                }
            }
        }();
        KTUtil.onDOMContentLoaded((function () {
            KTUsersViewRole.init()
        }));

    </script>
    <script>
        var KTUsersUpdatePermissions = function () {
            const t = document.getElementById("kt_modal_update_role"),
                e = t.querySelector("#kt_modal_update_role_form"),
                n = new bootstrap.Modal(t);
            return {
                init: function () {
                    (() => {
                        var o = FormValidation.formValidation(e, {
                            fields: {
                                role_name: {
                                    validators: {
                                        notEmpty: {
                                            message: "{{__('The role name is required')}}"
                                        }
                                    }
                                }
                            },
                            plugins: {
                                trigger: new FormValidation.plugins.Trigger,
                                bootstrap: new FormValidation.plugins.Bootstrap5({
                                    rowSelector: ".fv-row",
                                    eleInvalidClass: "",
                                    eleValidClass: ""
                                })
                            }
                        });
                        t.querySelector('[data-kt-roles-modal-action="close"]').addEventListener("click", (t => {
                            t.preventDefault(), Swal.fire({
                                text: "{{__('Are you sure you would like to close?')}}",
                                icon: "warning",
                                showCancelButton: !0,
                                buttonsStyling: !1,
                                confirmButtonText: "{{__('Yes')}}",
                                cancelButtonText: "{{__('No, Cancel')}}",
                                customClass: {
                                    confirmButton: "btn btn-primary",
                                    cancelButton: "btn btn-active-light"
                                }
                            }).then((function (t) {
                                t.value && n.hide()
                            }))
                        })), t.querySelector('[data-kt-roles-modal-action="cancel"]').addEventListener("click", (t => {
                            t.preventDefault(), Swal.fire({
                                text: "{{__('Are you sure you would like to cancel?')}}",
                                icon: "warning",
                                showCancelButton: !0,
                                buttonsStyling: !1,
                                confirmButtonText: "{{__('Yes')}}",
                                cancelButtonText: "{{__('No')}}",
                                customClass: {
                                    confirmButton: "btn btn-primary",
                                    cancelButton: "btn btn-active-light"
                                }
                            }).then((function (t) {
                                t.value && n.hide()
                            }))
                        }));
                        const i = t.querySelector('[data-kt-roles-modal-action="submit"]');
                        i.addEventListener("click", (function (t) {
                            t.preventDefault(), o && o.validate().then((function (t) {
                                "Valid" == t ? (i.setAttribute("data-kt-indicator", "on"), i.disabled = !0, setTimeout((function () {
                                    i.removeAttribute("data-kt-indicator"), i.disabled = !1, Swal.fire({
                                        text: "{{__('Form has been successfully submitted!')}}",
                                        icon: "success",
                                        buttonsStyling: !1,
                                        confirmButtonText: "{{__('Ok, Go It')}}",
                                        customClass: {
                                            confirmButton: "btn btn-primary"
                                        }
                                    }).then((function (t) {
                                        t.isConfirmed && n.hide();
                                        $('#kt_modal_update_role_form').submit();
                                    }))
                                }), 2e3)) : Swal.fire({
                                    text: "{{__('Sorry, looks like there are some errors detected, please try again')}}",
                                    icon: "error",
                                    buttonsStyling: !1,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                })
                            }))
                        }))
                    })(), (() => {
                        const t = e.querySelector("#kt_roles_select_all"),
                            n = e.querySelectorAll('[type="checkbox"]');
                        t.addEventListener("change", (t => {
                            n.forEach((e => {
                                e.checked = t.target.checked
                            }))
                        }))
                    })()
                }
            }
        }();
        KTUtil.onDOMContentLoaded((function () {
            KTUsersUpdatePermissions.init()
        }));

    </script>
    <script>
        $(document).on('click', '.delete-user', function (e) {
            e.preventDefault();
            let userId = $(this).data('id');
            let userName = $(this).data('name');
            let csrfToken = $('meta[name="csrf-token"]').attr('content');
            let tr = $(this).closest("tr");
            let selectElement = $('select[name="user_ids[]"]');

            // Show the confirmation dialog
            Swal.fire({
                text: '{{__('This action cannot be undone')}}',
                icon: 'warning',
                showCancelButton: !0,
                buttonsStyling: !1,
                confirmButtonText: "{{__('Yes, Delete!')}}",
                cancelButtonText: "{{__('No, Cancel')}}",
                customClass: {
                    confirmButton: "btn fw-bold btn-danger",
                    cancelButton: "btn fw-bold btn-active-light-primary"
                }
            }).then((result) => {
                if (result.isConfirmed) {

                    $.ajax({
                        url: '{{route('admin.roles.remove_user_from_role')}}',
                        type: 'POST',
                        data: {
                            'user_id': userId,
                            'role_id': '{{$role->id}}',
                            '_token': csrfToken,
                        },
                        success: function () {
                            // Handle the success response
                            Swal.fire({
                                text: "{{__('The Operation Done Successfully')}}",
                                icon: "success",
                                buttonsStyling: !1,
                                confirmButtonText: "{{__('Ok, Go It')}}",
                                customClass: {
                                    confirmButton: "btn fw-bold btn-primary"
                                }
                            });
                            tr.remove();
                            selectElement.append('<option value="' + userId + '">' + userName + '</option>');

                        },
                        error: function (xhr, status, error) {
                            // Handle the error response
                            Swal.fire({
                                title: '{{__('Error!')}}',
                                text: '{{__('An Error Occurred!')}}',
                                icon: 'error',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                    });
                }
            });
        });
        $(document).on('click', '#delete_selected', function (e) {
            e.preventDefault();

            // Show the confirmation dialog
            Swal.fire({
                text: '{{__('This action cannot be undone')}}',
                icon: 'warning',
                showCancelButton: !0,
                buttonsStyling: !1,
                confirmButtonText: "{{__('Yes, Delete!')}}",
                cancelButtonText: "{{__('No, Cancel')}}",
                customClass: {
                    confirmButton: "btn fw-bold btn-danger",
                    cancelButton: "btn fw-bold btn-active-light-primary"
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#delete_all_users').submit();
                }
            });
        });
    </script>
@endsection


<x-admin-layout>
    <div class="d-flex flex-column flex-xl-row">
        <!--begin::Sidebar-->
        <div class="flex-column flex-lg-row-auto w-100 w-lg-300px mb-10">
            <!--begin::Card-->
            <div class="card card-flush">
                <!--begin::Card header-->
                <div class="card-header">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h2 class="mb-0">{{$role->name}}</h2>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Permissions-->
                    <div class="d-flex flex-column text-gray-600">
                        @foreach($role->permissions as $permission)
                            <div class="d-flex align-items-center py-2">
                                <span class="bullet bg-primary me-3"></span>{{__($permission->name)}}
                            </div>
                        @endforeach
                    </div>
                    <!--end::Permissions-->
                </div>
                <!--end::Card body-->

                <!--begin::Card footer-->
                <div class="card-footer pt-0">
                    <button type="button" class="btn btn-light btn-active-primary" data-bs-toggle="modal"
                            data-bs-target="#kt_modal_update_role">{{__('Edit Role')}}</button>
                </div>
                <!--end::Card footer-->

            </div>
            <!--end::Card-->
            <!--begin::Modal-->
            <!--begin::Modal - Update role-->
            <div class="modal fade" id="kt_modal_update_role" tabindex="-1" aria-hidden="true">
                <!--begin::Modal dialog-->
                <div class="modal-dialog modal-dialog-centered mw-750px">
                    <!--begin::Modal content-->
                    <div class="modal-content">
                        <!--begin::Modal header-->
                        <div class="modal-header">
                            <!--begin::Modal title-->
                            <h2 class="fw-bolder">{{__('Update Role')}}</h2>
                            <!--end::Modal title-->
                            <!--begin::Close-->
                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-kt-roles-modal-action="close">
                                <!--begin::Svg Icon | path: icons/duotune/arrows/arr061.svg-->
                                <span class="svg-icon svg-icon-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none">
                                        <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                              transform="rotate(-45 6 17.3137)" fill="black"/>
                                        <rect x="7.41422" y="6" width="16" height="2" rx="1"
                                              transform="rotate(45 7.41422 6)" fill="black"/>
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->
                            </div>
                            <!--end::Close-->
                        </div>
                        <!--end::Modal header-->
                        <!--begin::Modal body-->
                        <div class="modal-body scroll-y mx-5 my-7">
                            <!--begin::Form-->
                            <form id="kt_modal_update_role_form" class="form" method="post"
                                  action="{{route('admin.roles.update' , $role->id)}}">
                                @csrf
                                @method('PUT')
                                <!--begin::Scroll-->
                                <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_update_role_scroll"
                                     data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}"
                                     data-kt-scroll-max-height="auto"
                                     data-kt-scroll-dependencies="#kt_modal_update_role_header"
                                     data-kt-scroll-wrappers="#kt_modal_update_role_scroll"
                                     data-kt-scroll-offset="300px">
                                    <!--begin::Input group-->
                                    <div class="fv-row mb-10">
                                        <!--begin::Label-->
                                        <label class="fs-5 fw-bolder form-label mb-2">
                                            <span class="required">{{__('Role Name')}}</span>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input class="form-control form-control-solid" name="role_name"
                                               value="{{old('role_name' , $role->name)}}"/>
                                        @error('role_name')
                                        <p class="text-danger">{{ $message }}</p>
                                        @enderror
                                        <div class="fv-plugins-message-container invalid-feedback">
                                        </div>
                                        <!--end::Input-->
                                    </div>
                                    <!--end::Input group-->
                                    <!--begin::Permissions-->
                                    <div class="fv-row">
                                        <!--begin::Label-->
                                        <label class="fs-5 fw-bolder form-label mb-2">{{__('Role Permissions')}}</label>
                                        <!--end::Label-->
                                        <!--begin::Table wrapper-->
                                        <div class="table-responsive">
                                            <!--begin::Table-->
                                            <table class="table align-middle table-row-dashed fs-6 gy-5">
                                                <!--begin::Table body-->
                                                <tbody class="text-gray-600 fw-bold">
                                                <!--begin::Table row-->
                                                <tr>
                                                    <td class="text-gray-800">{{__('Administrator Access')}}
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7"
                                                           data-bs-toggle="tooltip"
                                                           title="Allows a full access to the system"></i></td>
                                                    <td>
                                                        <!--begin::Checkbox-->
                                                        <label
                                                            class="form-check form-check-sm form-check-custom form-check-solid me-9">
                                                            <input class="form-check-input" type="checkbox" value=""
                                                                   id="kt_roles_select_all"/>
                                                            <span class="form-check-label"
                                                                  for="kt_roles_select_all">{{__('Select All')}}</span>
                                                        </label>
                                                        <!--end::Checkbox-->
                                                    </td>
                                                </tr>
                                                @foreach($permissions as $permission)
                                                    <tr>
                                                        <!--begin::Label-->
                                                        <td class="text-gray-800">{{__($permission->name)}}</td>
                                                        <!--end::Label-->
                                                        <!--begin::Options-->
                                                        <td>
                                                            <!--begin::Wrapper-->
                                                            <div class="d-flex">
                                                                <!--begin::Checkbox-->
                                                                <label
                                                                    class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                    <input class="form-check-input" type="checkbox"
                                                                           name="permissions[]"
                                                                           @checked(in_array($permission->id, $role->permissions->pluck('id')->toArray(), true))
                                                                           value="{{ $permission->name }}"/>
                                                                </label>
                                                                <!--end::Checkbox-->
                                                            </div>
                                                            <!--end::Wrapper-->
                                                        </td>
                                                        <!--end::Options-->
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                                <!--end::Table body-->
                                            </table>
                                            <!--end::Table-->
                                        </div>
                                        <!--end::Table wrapper-->
                                    </div>
                                    <!--end::Permissions-->
                                </div>
                                <!--end::Scroll-->
                                <!--begin::Actions-->
                                <div class="text-center pt-15">
                                    <button type="reset" class="btn btn-light me-3"
                                            data-kt-roles-modal-action="cancel">{{__('Discard')}}</button>
                                    <button type="submit" class="btn btn-primary" data-kt-roles-modal-action="submit">
                                        <span class="indicator-label">{{__('Submit')}}</span>
                                        <span class="indicator-progress">{{__('Please Wait')}}
                                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                </div>
                                <!--end::Actions-->
                            </form>
                            <!--end::Form-->
                        </div>
                        <!--end::Modal body-->
                    </div>
                    <!--end::Modal content-->
                </div>
                <!--end::Modal dialog-->
            </div>
            <!--end::Modal - Update role-->
            <!--end::Modal-->
        </div>
        <!--end::Sidebar-->

        <!--begin::Content-->
        <div class="flex-lg-row-fluid ms-lg-10">
            <!--begin::Card-->
            <div class="card card-flush mb-6 mb-xl-9">
                <!--begin::Card header-->
                <div class="card-header pt-5">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h2 class="d-flex align-items-center">{{__('Users Assigned')}}
                            <span class="text-gray-600 fs-6 ms-1">({{$role->users()->count()}})</span></h2>
                    </div>
                    <!--end::Card title-->
                    <!--begin::Card toolbar-->
                    <div class="card-toolbar">
                        <!--begin::Search-->
                        <div class="d-flex align-items-center position-relative my-1"
                             data-kt-view-roles-table-toolbar="base">
                            <!--begin::Svg Icon | path: icons/duotune/general/gen021.svg-->
                            <span class="svg-icon svg-icon-1 position-absolute ms-6">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none">
                                    <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1"
                                          transform="rotate(45 17.0365 15.1223)" fill="black"/>
                                    <path
                                        d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z"
                                        fill="black"/>
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                            <input type="text" data-kt-roles-table-filter="search"
                                   class="form-control form-control-solid w-250px ps-15"
                                   placeholder="{{__('Search In Users')}}"/>
                        </div>
                        <!--end::Search-->
                        <!--begin::Group actions-->
                        <div class="d-flex justify-content-end align-items-center d-none"
                             data-kt-view-roles-table-toolbar="selected">
                            <div class="fw-bolder me-5">
                                    <span class="me-2"
                                          data-kt-view-roles-table-select="selected_count"></span>{{__('Selected')}}
                            </div>
                            <button type="button" class="btn btn-danger"
                                    id="delete_selected">{{__('Delete Selected')}}</button>
                        </div>
                        <!--end::Group actions-->
                    </div>
                    <!--end::Card toolbar-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <form method="post" id="delete_all_users"
                          action="{{route('admin.roles.remove_users_from_role')}}">
                        @csrf
                        <input type="hidden" name="role_id" value="{{$role->id}}">
                        <!--begin::Table-->
                        <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0" id="kt_roles_view_table">
                            <!--begin::Table head-->
                            <thead>
                            <!--begin::Table row-->
                            <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                <th class="w-10px pe-2">
                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                        <input class="form-check-input" type="checkbox" data-kt-check="true"
                                               data-kt-check-target="#kt_roles_view_table .form-check-input"
                                               value="1"/>
                                    </div>
                                </th>
                                <th class="min-w-50px">#</th>
                                <th class="min-w-150px">{{__('User')}}</th>
                                <th class="min-w-125px">{{__('Created At')}}</th>
                                <th class="text-end min-w-100px"></th>
                            </tr>
                            <!--end::Table row-->
                            </thead>
                            <!--end::Table head-->
                            <!--begin::Table body-->
                            <tbody class="fw-bold text-gray-600">

                            @foreach($role->users as $key => $user)
                                <tr>
                                    <!--begin::Checkbox-->
                                    <td>
                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" name="user_ids[]"
                                                   value="{{$user->id}}"/>
                                        </div>
                                    </td>
                                    <!--end::Checkbox-->
                                    <!--begin::ID-->
                                    <td>{{$key + 1}}</td>
                                    <!--begin::ID-->
                                    <!--begin::User=-->
                                    <td class="d-flex align-items-center">
                                        <!--begin:: Avatar -->
                                        <!--end::Avatar-->
                                        <!--begin::User details-->
                                        <div class="d-flex flex-column">
                                            <a href="#"
                                               class="text-gray-800 text-hover-primary mb-1">{{$user->name}}</a>
                                            <span>{{$user->email}}</span>
                                        </div>
                                        <!--begin::User details-->
                                    </td>
                                    <!--end::user=-->
                                    <!--begin::Joined date=-->
                                    <td>{{$user->created_at}}</td>
                                    <!--end::Joined date=-->
                                    <!--begin::Action=-->
                                    <td>
                                        <a href="#" class="px-3 delete-user"
                                           data-id="{{$user->id}}"
                                           data-name="{{$user->name}}">
                                            <i class="ki-duotone ki-trash fs-2 text-danger">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                                <span class="path4"></span>
                                                <span class="path5"></span>
                                            </i>
                                        </a>
                                    </td>
                                    <!--end::Action=-->
                                </tr>
                            @endforeach

                            </tbody>
                            <!--end::Table body-->
                        </table>
                    </form>
                    <!--end::Table-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->

            <!--begin::Card-->
            <div class="card card-flush mb-6 mb-xl-9">
                <!--begin::Card header-->
                <div class="card-header pt-5">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h3 class="d-flex align-items-center">{{__('Assign New User')}}</h3>
                    </div>
                    <!--end::Card title-->

                </div>
                <!--end::Card header-->
                <form method="post" action="{{route('admin.roles.assign_users')}}">
                    @csrf

                    <input type="hidden" value="{{$role->id}}" name="role_id">
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <select class="form-select form-select-solid" data-control="select2" name="user_ids[]" required
                                data-placeholder="{{__('Please Choose One')}}" data-allow-clear="true"
                                multiple="multiple">
                            <option>{{__('Please Choose One')}}</option>
                            @foreach($users as $user)
                                <option value="{{$user->id}}">{{$user->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <!--end::Card body-->
                    <div class="card-footer d-flex justify-content-end py-6 px-9">
                        <button class="btn btn-light btn-active-light-primary me-2">{{__('Discard')}}</button>
                        <button class="btn btn-primary px-6" type="submit">{{__('Save Changes')}}</button>
                    </div>
                </form>
            </div>
            <!--end::Card-->
        </div>
        <!--end::Content-->

    </div>
</x-admin-layout>


