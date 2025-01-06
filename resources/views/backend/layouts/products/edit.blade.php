@extends('backend.app')
@section('title', 'Products')
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
                        Products </li>

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
                    <h1 class="mb-4">Edit Product</h1>
                    <form action="{{ route('admin.products.update', $product->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div>
                            <label for="name" class="form-label">Name</label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror" placeholder="Enter Name"
                                value="{{ $product->name }}">
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mt-3">
                            <label for="type" class="form-label">Type</label>
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror">
                                <option value="">Select Type</option>
                                <option value="free" {{ $product->type == 'free' ? 'selected' : '' }}>Free</option>
                                <option value="premium" {{ $product->type == 'premium' ? 'selected' : '' }}>Premium</option>
                            </select>
                            @error('type')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mt-3">
                            <label for="price" class="form-label">Price</label>
                            <input type="text" name="price" id="price"
                                class="form-control @error('price') is-invalid @enderror" placeholder="Enter Price"
                                value="{{ $product->price }}">
                            @error('price')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mt-3">
                            <label for="style" class="form-label">Style</label>
                            <select name="style[]" id="style" class="form-select @error('style') is-invalid @enderror"
                                multiple="multiple">
                                <option value="">Select Style</option>
                                @foreach ($styles as $style)
                                    <option value="{{ $style->id }}"
                                        {{ $product->styles->contains($style->id) ? 'selected' : '' }}>{{ $style->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('style')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mt-3">
                            <label for="theme" class="form-label">Theme</label>
                            <select name="theme[]" id="theme" class="form-select @error('theme') is-invalid @enderror"
                                multiple="multiple">
                                <option value="">Select Theme</option>
                                @foreach ($themes as $theme)
                                    <option value="{{ $theme->id }}"
                                        {{ $product->themes->contains($theme->id) ? 'selected' : '' }}>{{ $theme->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('theme')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mt-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                                placeholder="Enter Description" rows="6">{{ $product->description }}</textarea>
                            @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mt-3">
                            <label for="image_url" class="form-label">Image</label>
                            <input type="file" name="image_url" id="image_url"
                                class="dropify @error('image_url') is-invalid @enderror" value="{{ old('image_url') }}"
                                data-default-file="{{ asset($product->image_url) }}">
                            @error('image_url')
                                <span style="color: #F03F6A" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mt-3">
                            <label for="popular" class="form-label">Popular</label>
                            <select name="popular" id="popular"
                                class="form-select @error('popular') is-invalid @enderror">
                                <option value="">Select</option>
                                <option value="1" {{ $product->popular == 1 ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ $product->popular == 0 ? 'selected' : '' }}>No</option>
                            </select>
                            @error('popular')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mt-5">
                            <input type="submit" class="btn btn-primary btn-lg" value="Submit">
                            <a href="{{ route('admin.products.index') }}" class="btn btn-danger btn-lg">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    @push('script')
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
    @endpush
@endsection
