@extends('backend.app')

@section('title', 'Stripe settings')

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

                    <li class="breadcrumb-item text-muted"> Setting </li>
                    <li class="breadcrumb-item text-muted"> Stripe </li>

                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Info-->
        </div>
    </div>
    <!--end::Toolbar-->

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mt-4">
                <div class="card-style mb-4">
                    <form method="POST" action="{{ route('stripe.update') }}">
                        @csrf
                        <div class="input-style-1">
                            <label for="STRIPE_KEY">STRIPE KEY:</label>
                            <input type="text" placeholder="Enter stripe key" id="STRIPE_KEY"
                                class="form-control @error('STRIPE_KEY') is-invalid @enderror" name="STRIPE_KEY"
                                value="{{ env('STRIPE_KEY') }}" />
                            @error('STRIPE_KEY')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="input-style-1 mt-4">
                            <label for="STRIPE_SECRET">STRIPE_SECRET:</label>
                            <input type="text" placeholder="Enter stripe secret" id="STRIPE_SECRET"
                                class="form-control @error('STRIPE_SECRET') is-invalid @enderror" name="STRIPE_SECRET"
                                value="{{ env('STRIPE_SECRET') }}" />
                            @error('STRIPE_SECRET')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-danger me-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
