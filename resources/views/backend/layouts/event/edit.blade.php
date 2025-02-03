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
                            <form action="{{ route('admin.event.update', $event->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <!-- Business Name -->
                                <div class="mb-3">
                                    <label class="form-label">Business Name</label>
                                    <input type="text" name="business_name" class="form-control" value="{{ $event->business_name }}" required>
                                </div>

                                <!-- Category -->
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select name="category_id" class="form-control" required>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ $event->category_id == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                               

                                <!-- Activity (Indoor/Outdoor) -->
                                <div class="mb-3">
                                    <label class="form-label">Activity Type</label>
                                    <div>
                                        <input type="radio" name="activity" value="Indoor" {{ $event->activity == 'Indoor' ? 'checked' : '' }}> Indoor
                                        <input type="radio" name="activity" value="Outdoor" {{ $event->activity == 'Outdoor' ? 'checked' : '' }}> Outdoor
                                    </div>
                                </div>

                                <!-- Location -->
                                <div class="mb-3">
                                    <label class="form-label">Location</label>
                                    <input type="text" name="location" class="form-control" value="{{ $event->location }}" required>
                                </div>

                                <!-- Age Range -->
                                <div class="mb-3">
                                    <label class="form-label">Age Range</label>
                                    <input type="number" name="age_min" class="form-control" value="{{ $event->age_min }}" required>
                                    <input type="number" name="age_max" class="form-control" value="{{ $event->age_max }}" required>
                                </div>

                                <!-- Cover Image -->
                                <div class="mb-3">
                                    <label class="form-label">Cover Image</label>
                                    @if ($event->cover)
                                        <img src="{{ asset($event->cover) }}" width="100">
                                    @endif
                                    <input type="file" name="cover" class="form-control">
                                </div>

                                <!-- Operating Hours -->
                                <h4 class="mt-4">Operating Hours</h4>
                                <div class="row">
                                    @foreach (['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $index => $day)
                                        <div class="col-md-6">
                                            <div class="mb-3 border p-3 rounded">
                                                <label class="form-label">{{ $day }}</label>
                                                <input type="hidden" name="hours[{{ $index }}][day]" value="{{ $day }}">
                                                
                                                <div class="form-check">
                                                    <input type="checkbox" name="hours[{{ $index }}][is_closed]" class="form-check-input" value="1" {{ isset($event->business_hours[$index]) && $event->business_hours[$index]->is_closed ? 'checked' : '' }}> Closed
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-2">
                                                        <label class="form-label">Open Time</label>
                                                        <input type="text" name="hours[{{ $index }}][open_time]" class="form-control" value="{{ $event->business_hours[$index]->open_time ?? '' }}">
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <label class="form-label">Close Time</label>
                                                        <input type="text" name="hours[{{ $index }}][close_time]" class="form-control" value="{{ $event->business_hours[$index]->close_time ?? '' }}">
                                                    </div>
                                                </div>

                                                <div class="form-check">
                                                    <input type="checkbox" name="hours[{{ $index }}][is_second_time]" class="form-check-input" value="1" {{ isset($event->business_hours[$index]) && $event->business_hours[$index]->is_second_time ? 'checked' : '' }}> Second Time
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-2">
                                                        <label class="form-label">Reopen Time</label>
                                                        <input type="text" name="hours[{{ $index }}][re_open_time]" class="form-control" value="{{ $event->business_hours[$index]->re_open_time ?? '' }}">
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <label class="form-label">Reclose Time</label>
                                                        <input type="text" name="hours[{{ $index }}][re_close_time]" class="form-control" value="{{ $event->business_hours[$index]->re_close_time ?? '' }}">
                                                    </div>
                                                </div>
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
    </section>
@endsection
