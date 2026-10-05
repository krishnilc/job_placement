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
                            <li class="breadcrumb-item active">Add Job Type</li>
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
                            <div class="card-body p-4">
                                <h3 class="fs-4 mb-1">Add Job Type</h3>
                                <div class="mb-4">
                                    <label for="name" class="mb-2">Name*</label>
                                    <input type="text" name="name" id="name" class="form-control">
                                    <p class="text-danger" id="nameError"></p>
                                </div>
                                <div class="mb-4">
                                    <label for="status" class="mb-2">Status*</label>
                                    <select name="status" id="status" class="form-control fw-bold text-success">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    <p class="text-danger" id="statusError"></p>
                                </div>
                            </div>
                            <div class="card-footer p-4">
                                <button type="submit" class="btn btn-primary">Create Job Type</button>
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
                url: "{{ route('admin.jobTypes.store') }}",
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
                    alert('An error occurred while saving the job type. Please try again.');
                }
            });
        });
    </script>
@endsection
