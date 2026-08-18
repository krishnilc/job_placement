@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Create Job</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3">
                    @if(auth()->user()->role === 'admin')
                        @include('admin.sidebar')
                    @elseif(auth()->user()->role === 'employer')
                        @include('employer.sidebar')
                    @endif
                </div>
                <div class="col-lg-9">
                    @include('front.message')
                    <form action="" method="POST" id="createJobForm" name="createJobForm">
                        @csrf
                        <div class="card border-0 shadow mb-4 ">
                            <div class="card-body card-form p-4">
                                <h3 class="fs-4 mb-1">Job Details</h3>
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label for="" class="mb-2">Title<span class="req">*</span></label>
                                        <input type="text" placeholder="Job Title" id="title" name="title"
                                            class="form-control">
                                            <p class="text-danger" id="titleError"></p>
                                    </div>
                                    <div class="col-md-6  mb-4">
                                        <label for="" class="mb-2">Category<span class="req">*</span></label>
                                        <select name="category" id="category" class="form-control">
                                            <option value="">Select a Category</option>
                                            @if ($categories->isNotEmpty())
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                            <p class="text-danger" id="categoryError"></p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label for="" class="mb-2">Job Type<span class="req">*</span></label>
                                        <select name="job_type" id="job_type" class="form-control">
                                            <option value="">Select Job </option>
                                            @if ($jobTypes->isNotEmpty())
                                                @foreach ($jobTypes as $jobType)
                                                    <option value="{{ $jobType->id }}">{{ $jobType->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                            <p class="text-danger" id="jobTypeError"></p>
                                    </div>
                                    <div class="col-md-6  mb-4">
                                        <label for="" class="mb-2">No. of Vacancies<span class="req">*</span></label>
                                        <input type="number" min="1" placeholder="No. of Job Openings" id="vacancy"
                                            name="vacancy" class="form-control">
                                            <p class="text-danger" id="vacancyError"></p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="mb-4 col-md-6">
                                        <label for="" class="mb-2">Salary</label>
                                        <input type="text" placeholder="Salary" id="salary" name="salary"
                                            class="form-control">
                                    </div>

                                    <div class="mb-4 col-md-6">
                                        <label for="" class="mb-2">Closing Date</label>
                                        <input type="date" id="closing_date" name="closing_date" class="form-control">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="mb-4 col-md-6">
                                        <label for="" class="mb-2">Location<span class="req">*</span></label>
                                        <input type="text" placeholder="location" id="location" name="location"
                                            class="form-control">
                                        <p class="text-danger" id="locationError"></p>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="" class="mb-2">Description<span class="req">*</span></label>
                                    <textarea class="textarea" name="description" id="description" cols="5" rows="5"
                                        placeholder="Description"></textarea>
                                    <p class="text-danger" id="descriptionError"></p>
                                </div>
                                <div class="mb-4">
                                    <label for="" class="mb-2">Responsibilities</label>
                                    <textarea class="textarea" name="responsibilities" id="responsibilities" cols="5" rows="5"
                                        placeholder="Responsibilities"></textarea>
                                </div>
                                <div class="mb-4">
                                    <label for="" class="mb-2">Qualifications</label>
                                    <textarea class="textarea" name="qualifications" id="qualifications" cols="5" rows="5"
                                        placeholder="Qualifications"></textarea>
                                </div>



                                <div class="mb-4">
                                    <label for="" class="mb-2">Keywords</label>
                                    <input type="text" placeholder="keywords" id="keywords" name="keywords"
                                        class="form-control">
                                </div>

                                 <div class="mb-4">
                                    <label for="" class="mb-2">Experience <span class="req">*</span></label>
                                    <select name="experience" id="experience" class="form-control">
                                        <option value="0">No Experience Required</option>
                                        <option value="1">1 Year</option>
                                        <option value="2">2 Years</option>
                                        <option value="3">3 Years</option>
                                        <option value="4">4 Years</option>
                                        <option value="5">5 Years</option>
                                        <option value="6">6 Years</option>
                                        <option value="7">7 Years</option>
                                        <option value="8">8 Years</option>
                                        <option value="9">9 Years</option>
                                        <option value="10">10 Years</option>
                                        <option value="10_plus">10+ Years</option>
                                    </select>
                                    <p class="text-danger" id="experienceError"></p>
                                </div>

                                <h3 class="fs-4 mb-1 mt-5 border-top pt-5">Company Details</h3>

                                <div class="row">
                                    <div class="mb-4 col-md-6">
                                        <label for="" class="mb-2">Name<span class="req">*</span></label>
                                        <input type="text" placeholder="Company Name" id="company_name"
                                            name="company_name" class="form-control">
                                            <p class="text-danger" id="companyNameError"></p>
                                    </div>

                                    <div class="mb-4 col-md-6">
                                        <label for="" class="mb-2">Location</label>
                                        <input type="text" placeholder="Company Location" id="company_location" name="company_location"
                                            class="form-control">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="" class="mb-2">Company Website</label>
                                    <input type="text" placeholder="Company Website (including http://)" id="company_website" name="company_website"
                                        class="form-control">
                                </div>
                            </div>
                            <div class="card-footer  p-4">
                                <button type="submit" class="btn btn-primary">Save Job</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('customJS')
    <script>
        $('#createJobForm').submit(function(e) {
            e.preventDefault();
          $("button[type='submit']").prop('disabled', true); // Disable the submit button to prevent multiple clicks

            $.ajax({
                url: "{{ route('account.saveJob') }}",
                type: "POST",
                dataType: "json",
                data: $("#createJobForm").serializeArray(), // Use serializeArray to get an array of form data

                success: function(response) {
                     $("button[type='submit']").prop('disabled', false); // Enable the submit button
                    // Always clear all error messages first
                    $("#titleError").text('');
                    $("#categoryError").text('');
                    $("#jobTypeError").text('');
                    $("#vacancyError").text('');
                    $("#locationError").text('');
                    $("#descriptionError").text('');
                    $("#experienceError").text('');
                    $("#companyNameError").text('');    

                    if (response.status == true) {
                        window.location.href = "{{ route('account.myJobs') }}";
                    } else {
                        var errors = response.errors;

                        if (errors.title) {
                            $("#titleError").text(errors.title[0]);
                        }
                        if (errors.category) {
                            $("#categoryError").text(errors.category[0]);
                        }
                        if (errors.job_type) {
                            $("#jobTypeError").text(errors.job_type[0]);
                        }
                        if (errors.vacancy) {
                            $("#vacancyError").text(errors.vacancy[0]);
                        }
                        if (errors.location) {
                            $("#locationError").text(errors.location[0]);
                        }
                        if (errors.description) {
                            $("#descriptionError").text(errors.description[0]);
                        }
                        if (errors.experience) {
                            $("#experienceError").text(errors.experience[0]);
                        }
                        if (errors.company_name) {
                            $("#companyNameError").text(errors.company_name[0]);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred: ' + error);
                }
            });
        });
    </script>
@endsection
