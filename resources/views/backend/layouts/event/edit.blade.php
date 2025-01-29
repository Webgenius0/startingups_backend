@extends('backend.app')
@section('title', 'Edit Event')

@section('content')
    <section>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Edit Event</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.event.update', $event->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf

                                <!-- Business Name -->
                                <div class="mb-3">
                                    <label class="form-label">Business Name</label>
                                    <input type="text" name="business_name" class="form-control"
                                        value="{{ $event->business_name }}" required>
                                </div>

                                <!-- Category -->
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select name="category_id" class="form-control" required>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ $event->category_id == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Subcategory -->
                                <div class="mb-3">
                                    <label class="form-label">Sub Category</label>
                                    <select name="sub_category_id" class="form-control">
                                        @foreach ($sub_categories as $sub)
                                            <option value="{{ $sub->id }}"
                                                {{ $event->sub_category_id == $sub->id ? 'selected' : '' }}>
                                                {{ $sub->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Activity (Indoor/Outdoor) -->
                                <div class="mb-3">
                                    <label class="form-label">Activity Type</label>
                                    <div>
                                        <input type="radio" name="activity" value="indoor"
                                            {{ $event->activity == 'Indoor' ? 'checked' : '' }}> Indoor
                                        <input type="radio" name="activity" value="Outdoor"
                                            {{ $event->activity == 'Outdoor' ? 'checked' : '' }}> Outdoor
                                    </div>
                                </div>

                                <!-- Location -->
                                <div class="mb-3">
                                    <label class="form-label">Location</label>
                                    <input type="text" name="location" class="form-control"
                                        value="{{ $event->location }}" required>
                                </div>

                                <!-- Age Range -->
                                <div class="mb-3">
                                    <label class="form-label">Age Range</label>
                                    <input type="number" name="age_min" class="form-control" value="{{ $event->age_min }}"
                                        required>
                                    <input type="number" name="age_max" class="form-control" value="{{ $event->age_max }}"
                                        required>
                                </div>

                                <!-- Cover Image -->
                                <div class="mb-3">
                                    <label class="form-label">Cover Image</label>
                                    @if ($event->cover)
                                        <img src="{{ asset($event->cover) }}" width="100">
                                    @endif
                                    <input type="file" name="cover" class="form-control">
                                </div>

                                <!-- Business Hours -->
                                <!-- Business Hours -->
                                <div class="mb-3">
                                    <label class="form-label">Operating Hours</label>
                                    @foreach ($event->business_hours as $hour)
                                        <div class="d-flex mb-2">
                                            <!-- Regular Operating Hours -->
                                            <div class="col-lg-6">
                                                <label class="form-label">Regular Hours ({{ $hour->day }})</label>
                                                <input type="time" name="hours[{{ $loop->index }}][open_time]"
                                                    class="form-control" value="{{ $hour->open_time }}">
                                                <input type="time" name="hours[{{ $loop->index }}][close_time]"
                                                    class="form-control" value="{{ $hour->close_time }}">
                                            </div>

                                            <!-- Re-Opening Hours -->
                                            <div class="col-lg-6">
                                                <label class="form-label">Re-opening Hours ({{ $hour->day }})</label>
                                                <input type="time" name="hours[{{ $loop->index }}][re_open_time]"
                                                    class="form-control" value="{{ $hour->re_open_time }}">
                                                <input type="time" name="hours[{{ $loop->index }}][re_close_time]"
                                                    class="form-control" value="{{ $hour->re_close_time }}">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>



                                <button type="submit" class="btn btn-primary">Update Event</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>

    </section>
@endsection
