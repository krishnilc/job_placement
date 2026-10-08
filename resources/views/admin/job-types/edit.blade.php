@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.jobTypes') }}">Job Types</a></li>
                            <li class="breadcrumb-item active">Edit Job Type</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3">@include('admin.sidebar')</div>
                <div class="col-lg-9">
                    @include('front.message')
                    <div class="card border-0 shadow mb-4">
                        <form id="jobTypeForm">
                            @csrf
                            @method('PUT')
                            <div class="card-body p-4">
                                <h3 class="fs-4 mb-1">Edit Job Type</h3>
                                <div class="mb-4">
                                    <label for="name" class="mb-2">Name<span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        value="{{ old('name', $jobType->name) }}">
                                    <p class="text-danger" id="nameError"></p>
                                </div>
                                <div class="mb-4">
                                    <label for="status" class="mb-2">Status<span class="text-danger">*</span></label>
                                    <select name="status" id="status"
                                        class="form-control fw-bold {{ $jobType->status ? 'text-success' : 'text-danger' }}">
                                        <option value="1" {{ $jobType->status ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ !$jobType->status ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    <p class="text-danger" id="statusError"></p>
                                </div>
                            </div>
                            <div class="card-footer p-4">
                                <button type="submit" class="btn btn-primary">Update Job Type</button>
                                <a href="{{ route('admin.jobTypes') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('customJS')
    <script>
        $('#status').on('change', function() {
            $(this).toggleClass('text-success', this.value === '1').toggleClass('text-danger', this.value === '0');
        });

        $('#jobTypeForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('admin.jobTypes.update', $jobType->id) }}",
                type: 'POST',
                dataType: 'json',
                data: $(this).serialize(),
                success: function(response) {
                    $('#nameError, #statusError').text('');
                    if (response.status) {
                        window.location.href = "{{ route('admin.jobTypes') }}";
                        return;
                    }
                    $.each(response.errors, function(field, errors) {
                        $('#' + field + 'Error').text(errors[0]);
                    });
                },
                error: function() {
                    alert('An error occurred while updating the job type. Please try again.');
                }
            });
        });
    </script>
@endsection
