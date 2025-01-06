@extends('backend.app')
@section('title', 'Themes')
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
                        Themes </li>

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
                    <h1 class="mb-4">Add Theme</h1>
                    <form action="{{ route('admin.themes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div>
                            <label for="name" class="form-label">Name</label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror" placeholder="Enter Name"
                                value="{{ old('name') }}">
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mt-3">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" name="image" id="image"
                                class="dropify @error('image') is-invalid @enderror" value="{{ old('image') }}"
                                data-default-file="{{ asset('backend/images/placeholder/image_placeholder.png') }}">
                        </div>
                        @error('image')
                            <span style="color: #F03F6A" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <div class="mt-4">
                            <input type="submit" class="btn btn-primary btn-lg" value="Submit">
                            <a href="{{ route('admin.themes.index') }}" class="btn btn-danger btn-lg">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
