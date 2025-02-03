@extends('backend.app')
@section('title', 'category')
@push('style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
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
                        Category </li>

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
                <div class="col-md-12">
                    <h2 class="my-3">Create Event</h2>
                    <form action="{{ route('admin.event.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="cover" class="form-label">Cover Image</label>
                            <input type="file" name="cover" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="business_name" class="form-label">Business Name</label>
                            <input type="text" name="business_name" class="form-control" placeholder="Enter business name" required>
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">Select</option>
                                @foreach ($categories as $category)
                                    
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                               
                            </select>
                        </div>

                        

                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" placeholder="Enter location" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-block">Activity</label>
                            <div class="form-check form-check-inline">
                                <input type="radio" name="activity" value="Indoor" id="activityIndoor"
                                    class="form-check-input" required>
                                <label class="form-check-label" for="activityIndoor">Indoor</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" name="activity" value="Outdoor" id="activityOutdoor"
                                    class="form-check-input" required>
                                <label class="form-check-label" for="activityOutdoor">Outdoor</label>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="age_min" class="form-label">Minimum Age</label>
                                <input type="number" name="age_min" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="age_max" class="form-label">Maximum Age</label>
                                <input type="number" name="age_max" class="form-control" required>
                            </div>
                        </div>

                        <h4 class="mt-4">Operating Hours</h4>
                        <div class="row">
                            @foreach (['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $index => $day)
                                <div class="col-md-6">
                                    <div class="mb-3 border p-3 rounded">
                                        <label class="form-label">{{ $day }}</label>
                                        <input type="hidden" name="hours[{{ $index }}][day]"
                                            value="{{ $day }}">
                                        <div class="form-check">
                                            <input type="checkbox" name="hours[{{ $index }}][is_closed]"
                                                class="form-check-input" value="1"> Closed
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Open Time</label>
                                                <input type="text" name="hours[{{ $index }}][open_time]"
                                                    class="form-control">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Close Time</label>
                                                <input type="text" name="hours[{{ $index }}][close_time]"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" name="hours[{{ $index }}][is_second_time]"
                                                class="form-check-input" value="1"> Second Time
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Reopen Time</label>
                                                <input type="text" name="hours[{{ $index }}][re_open_time]"
                                                    class="form-control">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Reclose Time</label>
                                                <input type="text" name="hours[{{ $index }}][re_close_time]"
                                                    class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>


                        {{-- <h4 class="mt-4">Prices</h4>
                        <div id="price-section">
                            <div class="mb-3 border p-3 rounded">
                                <label class="form-label">Type</label>
                                <select name="prices[0][type]" class="form-select">
                                    <option value="day">Day</option>
                                    <option value="purch">Purchase</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                                <label class="form-label mt-2">Amount</label>
                                <input type="number" name="prices[0][amount]" class="form-control" required>
                                <label class="form-label mt-2">Days</label>
                                <input type="number" name="prices[0][days]" class="form-control">
                                <label class="form-label mt-2">Offerings</label>
                                <input type="text" name="prices[0][offerings]" class="form-control">
                            </div>
                        </div> --}}

                        <button type="submit" class="btn btn-primary mt-3 w-100">Save Event</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    {{-- @push('script')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function() {
                $('#style').select2({
                    placeholder: 'Select an option',
                    multiple: true
                });
            });
            $(document).ready(function() {
                $('#theme').select2({
                    placeholder: 'Select an option',
                    multiple: true
                });
            });
        </script>
    @endpush --}}
@endsection
