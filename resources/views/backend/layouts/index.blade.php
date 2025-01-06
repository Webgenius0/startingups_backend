@extends('backend.app')
@section('title', 'Dashboard')

@section('content')

    @push('style')
        <style>
            .card-hover {
                transition: transform 0.3s, box-shadow 0.3s;
            }

            .card-hover:hover {
                transform: scale(1.05);
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            }
            .container-xxl{
              max-width: 1580px;
            }
        </style>
    @endpush
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <div class=" container-fluid  d-flex flex-stack flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">
                <!--begin::Title-->
                <h1 class="text-dark fw-bold my-1 fs-2">
                    Dashboard <small class="text-muted fs-6 fw-normal ms-1"></small>
                </h1>
                <!--end::Title-->

                <!--begin::Breadcrumb-->
                <ul class="breadcrumb fw-semibold fs-base my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">
                            Home </a>
                    </li>

                    <li class="breadcrumb-item text-muted">
                        Dashboards </li>

                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Info-->
        </div>
    </div>
    <!--end::Toolbar-->


    <section>
        <div class="container-fluid">
            <div class="row">
                <!-- Total Users Card -->
                <div class="col-md-4">
                    <div class="card text-center card-hover">
                        <div class="card-body">
                            <h1 class="display-4 count-up " data-count="{{ $user }}">0</h1>
                            <p class="card-text">Total Users</p>
                        </div>
                    </div>
                </div>

                <!-- Categories Available Card -->
                <div class="col-md-4">
                    <div class="card text-center card-hover">
                        <div class="card-body">
                            <h1 class="display-4 count-up" data-count="{{ $categories }}">0</h1>
                            <p class="card-text">Categories Available</p>
                        </div>
                    </div>
                </div>

                <!-- Sub Categories Available Card -->
                <div class="col-md-4">
                    <div class="card text-center card-hover">
                        <div class="card-body">
                            <h1 class="display-4 count-up" data-count="{{ $subcategories }}">0</h1>
                            <p class="card-text">Sub Categories Available</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <section class="mt-5">
        <div class="post fs-6 d-flex flex-column-fluid" id="kt_post">
            <!--begin::Container-->
            <div class=" container-xxl ">
                <!--begin::Card-->
                <div class="card">
                    <!--begin::Card header-->
                    <div class="card-header border-0 pt-6">
                        <!--begin::Card title-->
                        <div class="card-title">
                            <!--begin::Search-->
                            <div class="d-flex align-items-center position-relative my-1">
                                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span
                                        class="path1"></span><span class="path2"></span></i> <input type="text"
                                    data-kt-user-table-filter="search" class="form-control form-control-solid w-250px ps-13"
                                    placeholder="Search user">
                            </div>
                            <!--end::Search-->
                        </div>
                        <!--begin::Card title-->

                        <!--begin::Card toolbar-->
                        <div class="card-toolbar">
                            <!--begin::Toolbar-->
                            <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                                <!--begin::Filter-->
                                <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click"
                                    data-kt-menu-placement="bottom-end">
                                    <i class="ki-duotone ki-filter fs-2"><span class="path1"></span><span
                                            class="path2"></span></i> Filter
                                </button>
                                <!--begin::Menu 1-->
                                <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                                    <!--begin::Header-->
                                    <div class="px-7 py-5">
                                        <div class="fs-5 text-dark fw-bold">Filter Options</div>
                                    </div>
                                    <!--end::Header-->

                                    <!--begin::Separator-->
                                    <div class="separator border-gray-200"></div>
                                    <!--end::Separator-->

                                    <!--begin::Content-->
                                    <div class="px-7 py-5" data-kt-user-table-filter="form">
                                        <!--begin::Input group-->
                                        <div class="mb-10">
                                            <label class="form-label fs-6 fw-semibold">Role:</label>
                                            <select class="form-select form-select-solid fw-bold select2-hidden-accessible"
                                                data-kt-select2="true" data-placeholder="Select option"
                                                data-allow-clear="true" data-kt-user-table-filter="role"
                                                data-hide-search="true" data-select2-id="select2-data-7-yvvv" tabindex="-1"
                                                aria-hidden="true" data-kt-initialized="1">
                                                <option data-select2-id="select2-data-9-fhdi"></option>
                                                <option value="Administrator">Administrator</option>
                                                <option value="Analyst">Analyst</option>
                                                <option value="Developer">Developer</option>
                                                <option value="Support">Support</option>
                                                <option value="Trial">Trial</option>
                                            </select><span class="select2 select2-container select2-container--bootstrap5"
                                                dir="ltr" data-select2-id="select2-data-8-2hx2"
                                                style="width: 100%;"><span class="selection"><span
                                                        class="select2-selection select2-selection--single form-select form-select-solid fw-bold"
                                                        role="combobox" aria-haspopup="true" aria-expanded="false"
                                                        tabindex="0" aria-disabled="false"
                                                        aria-labelledby="select2-uv3v-container"
                                                        aria-controls="select2-uv3v-container"><span
                                                            class="select2-selection__rendered" id="select2-uv3v-container"
                                                            role="textbox" aria-readonly="true" title="Select option"><span
                                                                class="select2-selection__placeholder">Select
                                                                option</span></span><span class="select2-selection__arrow"
                                                            role="presentation"><b
                                                                role="presentation"></b></span></span></span><span
                                                    class="dropdown-wrapper" aria-hidden="true"></span></span>
                                        </div>
                                        <!--end::Input group-->

                                       
                                        <!--begin::Actions-->
                                        <div class="d-flex justify-content-end">
                                            <button type="reset"
                                                class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6"
                                                data-kt-menu-dismiss="true"
                                                data-kt-user-table-filter="reset">Reset</button>
                                            <button type="submit" class="btn btn-primary fw-semibold px-6"
                                                data-kt-menu-dismiss="true"
                                                data-kt-user-table-filter="filter">Apply</button>
                                        </div>
                                        <!--end::Actions-->
                                    </div>
                                    <!--end::Content-->
                                </div>
                                <!--end::Menu 1--> <!--end::Filter-->

                                {{-- <!--begin::Export-->
                                <button type="button" class="btn btn-light-primary me-3" data-bs-toggle="modal"
                                    data-bs-target="#kt_modal_export_users">
                                    <i class="ki-duotone ki-exit-up fs-2"><span class="path1"></span><span
                                            class="path2"></span></i> Export
                                </button>
                                <!--end::Export--> --}}

                                <!--begin::Modal-->
                                <div class="modal fade" id="kt_modal_export_users" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Export Users</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Choose the format you want to export:</p>
                                                <div class="d-flex justify-content-between">
                                                    <button id="export_csv" class="btn btn-success">Export as CSV</button>
                                                    <button id="export_excel" class="btn btn-primary">Export as Excel</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Modal-->

                               
                            </div>
                            <!--end::Toolbar-->

                            <!--begin::Group actions-->
                            <div class="d-flex justify-content-end align-items-center d-none"
                                data-kt-user-table-toolbar="selected">
                                <div class="fw-bold me-5">
                                    <span class="me-2" data-kt-user-table-select="selected_count"></span> Selected
                                </div>

                                <button type="button" class="btn btn-danger"
                                    data-kt-user-table-select="delete_selected">
                                    Delete Selected
                                </button>
                            </div>
                            <!--end::Group actions-->

                      

                         
                        </div>
                        <!--end::Card toolbar-->
                    </div>
                    <!--end::Card header-->

                    <!--begin::Card body-->
                    <div class="card-body py-4">

                        <!--begin::Table-->
                        <div id="kt_table_users_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer"
                                    id="kt_table_users">
                                    <thead>
                                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                            <th class="w-10px pe-2 sorting_disabled" rowspan="1" colspan="1"
                                                aria-label="" style="width: 29.8906px;">
                                                <div
                                                    class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                    <input class="form-check-input" type="checkbox" data-kt-check="true"
                                                        data-kt-check-target="#kt_table_users .form-check-input"
                                                        value="1">
                                                </div>
                                            </th>
                                            <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_table_users"
                                                rowspan="1" colspan="1"
                                                aria-label="User: activate to sort column ascending"
                                                style="width: 278.328px;">User</th>
                                            <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_table_users"
                                                rowspan="1" colspan="1"
                                                aria-label="Role: activate to sort column ascending"
                                                style="width: 161.844px;">Role</th>
                                            <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_table_users"
                                                rowspan="1" colspan="1"
                                                aria-label="Last login: activate to sort column ascending"
                                                style="width: 161.844px;">Last login</th>
                                            <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_table_users"
                                                rowspan="1" colspan="1"
                                                aria-label="Two-step : activate to sort column ascending"
                                                style="width: 161.844px;">Two-step </th>
                                            <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_table_users"
                                                rowspan="1" colspan="1"
                                                aria-label="Joined Date: activate to sort column ascending"
                                                style="width: 210.266px;">Joined Date</th>
                                            <th class="text-end min-w-100px sorting_disabled" rowspan="1"
                                                colspan="1" aria-label="Actions" style="width: 132.484px;">Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 fw-semibold">
                                        @foreach($userData as $user)
                                            <tr class="odd">
                                                <td>
                                                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                        <input class="form-check-input" type="checkbox" value="1">
                                                    </div>
                                                </td>
                                                <td class="d-flex align-items-center">
                                                    <!--begin:: Avatar -->
                                                    <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                                        <a href="view.html">
                                                            <div class="symbol-label">
                                                                <img src="{{ asset($user->avatar) }}" alt="{{ $user->name }}" class="w-100">
                                                            </div>
                                                        </a>
                                                    </div>
                                                    <!--end::Avatar-->
                                                    <!--begin::User details-->
                                                    <div class="d-flex flex-column">
                                                        <a href="view.html" class="text-gray-800 text-hover-primary mb-1">{{ $user->name }}</a>
                                                        <span>{{ $user->email }}</span>
                                                    </div>
                                                    <!--begin::User details-->
                                                </td>
                                                <td>
                                                    {{ $user->role }} <!-- Assuming 'role' is a column in your users table -->
                                                </td>
                                                <td data-order="{{ $user->last_login }}">
                                                    <div class="badge badge-light fw-bold">
                                                        {{ $user->last_login ? \Carbon\Carbon::parse($user->last_login)->diffForHumans() : 'Never' }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <!-- Two-step authentication status -->
                                                    @if($user->two_step_enabled)
                                                        <span class="badge badge-success">Enabled</span>
                                                    @else
                                                        <span class="badge badge-danger">Disabled</span>
                                                    @endif
                                                </td>
                                                <td data-order="{{ $user->created_at }}">
                                                    {{ \Carbon\Carbon::parse($user->created_at)->format('d M Y, h:i A') }}
                                                </td>
                                                <td class="text-end">
                                                    <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                                        Actions
                                                        <i class="ki-duotone ki-down fs-5 ms-1"></i>
                                                    </a>
                                                    <!--begin::Menu-->
                                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                                        <div class="menu-item px-3">
                                                            <a href="view.html" class="menu-link px-3">Edit</a>
                                                        </div>
                                                        <div class="menu-item px-3">
                                                            <a href="#" class="menu-link px-3" data-kt-users-table-filter="delete_row">Delete</a>
                                                        </div>
                                                    </div>
                                                    <!--end::Menu-->
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    
                                </table>
                            </div>
                            <div class="row">
                                <div
                                    class="col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start">
                                </div>
                                <div
                                    class="col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end">
                                    <div class="dataTables_paginate paging_simple_numbers" id="kt_table_users_paginate">
                                        <ul class="pagination">
                                            <li class="paginate_button page-item previous disabled"
                                                id="kt_table_users_previous"><a href="#"
                                                    aria-controls="kt_table_users" data-dt-idx="0" tabindex="0"
                                                    class="page-link"><i class="previous"></i></a></li>
                                            <li class="paginate_button page-item active"><a href="#"
                                                    aria-controls="kt_table_users" data-dt-idx="1" tabindex="0"
                                                    class="page-link">1</a></li>
                                            <li class="paginate_button page-item "><a href="#"
                                                    aria-controls="kt_table_users" data-dt-idx="2" tabindex="0"
                                                    class="page-link">2</a></li>
                                            <li class="paginate_button page-item "><a href="#"
                                                    aria-controls="kt_table_users" data-dt-idx="3" tabindex="0"
                                                    class="page-link">3</a></li>
                                            <li class="paginate_button page-item next" id="kt_table_users_next"><a
                                                    href="#" aria-controls="kt_table_users" data-dt-idx="4"
                                                    tabindex="0" class="page-link"><i class="next"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Table-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end::Container-->
        </div>
    </section>
@endsection

@push('script')
    <script>
        // Count-up Animation
        document.querySelectorAll('.count-up').forEach((element) => {
            const countTo = parseInt(element.getAttribute('data-count'));
            let currentCount = 0;
            const increment = Math.ceil(countTo / 50);
            const interval = setInterval(() => {
                currentCount += increment;
                if (currentCount >= countTo) {
                    currentCount = countTo;
                    clearInterval(interval);
                }
                element.innerText = currentCount;
            }, 30);
        });
    </script>
@endpush
