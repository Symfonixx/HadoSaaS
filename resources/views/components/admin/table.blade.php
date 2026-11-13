@props([ 'model' ,'dataTable' => true, 'class' => null, 'formUrl' => null, 'search' => null,'title' => null])
<div class="card">

    <div class="card-header border-0 pt-6">
        @if($search || $title)

        <div class="card-title">
            @if($search)
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <input type="text" data-kt-data-table-filter="search"
                           class="form-control form-control-solid w-250px ps-12"
                           placeholder="{{__($search)}}"/>
                </div>
            @endif
            @if($title)
                <h2>{{$title}}</h2>
            @endif
        </div>
        @elseif(!$search && !$title && $formUrl)
            <div></div>
          @endif
        @if($formUrl)
            <div class="card-toolbar">

                <div class="d-flex justify-content-end" data-kt-comp-table-toolbar="base"></div>
                <div class="d-flex justify-content-end align-items-center d-none"
                     data-kt-comp-table-toolbar="selected">
                    <div class="fw-bold me-5">
                        <span class="me-2" data-kt-comp-table-toolbar="selected_count"></span>{{__('Selected')}}
                    </div>
                    <button type="button" class="btn btn-danger"
                            data-kt-comp-table-toolbar="delete_selected">{{__('Delete Selected')}}
                    </button>
                </div>

            </div>
        @endif
    </div>

    @if($formUrl)
        <form method="post" id="delete_all" action="{{$formUrl}}">
            @csrf
            @method('DELETE')
            @endif

            <div class="card-body">
                <table class="table align-middle table-row-dashed fs-6 gy-5 {{$class ?? null}}"
                       @if(isset($dataTable)) id="dataTable" @endif>
                    {{$slot}}
                </table>
            </div>
            @if($formUrl)
        </form>
    @endif
    @if(isset($model) && $model instanceof \Illuminate\Pagination\LengthAwarePaginator)

        <div class="card-footer">
            {!! $model->withQueryString()->links() !!}
        </div>
    @endif
</div>


@if($dataTable)
    @push('scripts')
        <script>
            $(document).ready(function () {
                t = document.getElementById("dataTable");
                e = $('#dataTable').DataTable({
                    "responsive": true,
                    "paging": false,  // Disable pagination
                    "searching": true, // Enable searching
                    "info": false,
                    "order": [],          // Disable initial auto-ordering
                    "ordering": true,
                    "language": {
                        "emptyTable": "{{__('No data available in table')}}"
                    }

                });

                var l = (dataTable) => {
                    const r = t.querySelectorAll('[type="checkbox"]');
                    n = document.querySelector('[data-kt-comp-table-toolbar="base"]');
                    o = document.querySelector('[data-kt-comp-table-toolbar="selected"]');
                    c = document.querySelector('[data-kt-comp-table-toolbar="selected_count"]');
                    const a = document.querySelector('[data-kt-comp-table-toolbar="delete_selected"]');

                    if (!n || !o || !c || !a) {
                        console.log(n, o, c, a);
                        console.error("One or more elements not found.");
                        return;
                    }

                    r.forEach((checkbox => {
                        checkbox.addEventListener("click", (() => {
                            setTimeout(() => {
                                const checkboxes = t.querySelectorAll('tbody [type="checkbox"]');
                                let isChecked = false;
                                let checkedCount = 0;

                                checkboxes.forEach((checkbox => {
                                    if (checkbox.checked) {
                                        isChecked = true;
                                        checkedCount++;
                                    }
                                }));

                                if (isChecked) {
                                    c.innerHTML = checkedCount;
                                    n.classList.add("d-none");
                                    o.classList.remove("d-none");
                                } else {
                                    n.classList.remove("d-none");
                                    o.classList.add("d-none");
                                }
                            }, 50);
                        }));
                    }));

                    a.addEventListener("click", (() => {
                        Swal.fire({
                            text: "{{__('This action cannot be undone.')}}",
                            icon: "warning",
                            showCancelButton: true,
                            buttonsStyling: false,
                            confirmButtonText: "{{__('Yes Delete!')}}",
                            cancelButtonText: "{{__('No Cancel')}}",
                            customClass: {
                                confirmButton: "btn fw-bold btn-danger",
                                cancelButton: "btn fw-bold btn-active-light-primary"
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#delete_all').submit();
                            }
                        });
                    }));
                };

                l();

                if (e) {
                    document.querySelector('[data-kt-data-table-filter="search"]').addEventListener("keyup", ((event) => {
                        e.search(event.target.value).draw();
                    }));
                }
            });
        </script>

    @endpush
@endif
