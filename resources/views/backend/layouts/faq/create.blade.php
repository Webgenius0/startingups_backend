@extends('backend.app')
@section('title', 'Styles')
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
                        Faq </li>

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
                    <h1 class="mb-4">Add Faq</h1>
                    <form action="{{ route('admin.faq.store') }}" method="POST" >
                        @csrf
                        <div>
                            <label for="question" class="form-label">Question</label>
                            <input type="text" name="question" id="question"
                                class="form-control @error('question') is-invalid @enderror" placeholder="Enter question content"
                                value="{{ old('question') }}">
                            @error('question')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <label for="answer" class="form-label">Answer</label>
                            <input type="text" name="answer" id="answer"
                                class="form-control @error('answer') is-invalid @enderror" placeholder="Enter answer content"
                                value="{{ old('answer') }}">
                            @error('answer')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mt-4">
                            <input type="submit" class="btn btn-primary btn-lg" value="Submit">
                            <a href="{{ route('admin.faq.index') }}" class="btn btn-danger btn-lg">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
