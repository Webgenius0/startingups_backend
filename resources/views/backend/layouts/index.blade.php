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
                            <h1 class="display-4 count-up" data-count="{{ $earning_amount }}">0</h1>
                            <p class="card-text">Total Revenue</p>
                        </div>
                    </div>
                </div>

                <!-- Sub Categories Available Card -->
                <div class="col-md-4">
                    <div class="card text-center card-hover">
                        <div class="card-body">
                            <h1 class="display-4 count-up" data-count="{{ $accept_business }}">0</h1>
                            <p class="card-text">Total Business</p>
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
                                                aria-label="Joined Date: activate to sort column ascending"
                                                style="width: 210.266px;">Joined Date</th>
                                            
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
                                                                <img src="{{ $user->avatar ?  asset($user->avatar) : asset('uploads/default_user.jpg') }}" alt="{{ $user->name }}" class="w-100">
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
                                                
                                               
                                                <td data-order="{{ $user->created_at }}">
                                                    {{ \Carbon\Carbon::parse($user->created_at)->format('d M Y, h:i A') }}
                                                </td>
                                                
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    
                                </table>
                                {!! $userData->withQueryString()->links('pagination::bootstrap-5') !!}
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
