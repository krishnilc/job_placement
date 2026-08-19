@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            @if (auth()->user()->role == 'user')
                                <li class="breadcrumb-item"><a href="{{ route('account.dashboard') }}">Home</a></li>
                            @elseif (auth()->user()->role == 'admin')
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            @endif

                            <li class="breadcrumb-item active"><a href="{{ route('account.editProfile') }}">Account Settings</a>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3">
                    @if (auth()->user()->role == 'admin')
                        @include('admin.sidebar')
                    @elseif (auth()->user()->role == 'employer')
                        @include('employer.sidebar')
                    @else
                        @include('front.account.student-sidebar')
                    @endif
                </div>
                <div class="col-lg-9">
                    @include('front.message')
                    <div class="card border-0 shadow mb-4">
                        <form action="" method="POST" id="userForm" name="userForm">
                            @csrf
                            <div class="card-body p-4">
                                <h3 class="fs-4 mb-4">My Profile</h3>

                                <div class="row g-4">
                                    <div class="col-md-6 mb-4">
                                        <label for="name" class="mb-2">Name*</label>
                                        <input type="text" name="name" id="name" class="form-control"
                                            value="{{ $user->name }}">
                                        <p class="text-danger" id="nameError"></p>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label for="email" class="mb-2">Email*</label>
                                        <input type="text" name="email" id="email" class="form-control"
                                            value="{{ $user->email }}">
                                        <p class="text-danger" id="emailError"></p>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label for="mobile" class="mb-2">Mobile</label>
                                        <input type="text" name="mobile" id="mobile" class="form-control"
                                            value="{{ $user->mobile }}">
                                        <p class="text-danger" id="mobileError"></p>
                                    </div>

                                    @if (in_array(auth()->user()->role, ['admin', 'employer'], true))
                                        <div class="col-md-6 mb-4">
                                            <label for="designation" class="mb-2">Designation*</label>
                                            <input type="text" name="designation" id="designation" class="form-control"
                                                value="{{ $user->designation }}" placeholder="e.g. CEO, Manager, HR Specialist">
                                            <p class="text-danger" id="designationError"></p>
                                        </div>
                                    @elseif (auth()->user()->role === 'student')
                                        <div class="col-md-6 mb-4">
                                            <label for="designation" class="mb-2">Student Status*</label>
                                            <select name="designation" id="designation" class="form-control">
                                                <option value="">Select your status</option>
                                                <option value="Full-time Student" {{ $user->designation == 'Full-time Student' ? 'selected' : '' }}>Full-time Student</option>
                                                <option value="Part-time Student" {{ $user->designation == 'Part-time Student' ? 'selected' : '' }}>Part-time Student</option>
                                                <option value="Alumni" {{ $user->designation == 'Alumni' ? 'selected' : '' }}>Alumni</option>
                                            </select>
                                            <p class="text-danger" id="designationError"></p>
                                        </div>
                                    @endif
                                </div>

                                @if (auth()->user()->role === 'student')
                                    <div class="border-top mt-4 pt-4">
                                        <h4 class="fs-5 mb-3">Personal Details</h4>
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label for="date_of_birth" class="mb-2">Date of Birth</label>
                                                <input type="date" name="date_of_birth" id="date_of_birth" class="form-control" value="{{ old('date_of_birth', $user->date_of_birth) }}">
                                                <p class="text-danger" id="dateOfBirthError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="gender" class="mb-2">Gender</label>
                                                <select name="gender" id="gender" class="form-control">
                                                    <option value="">Select gender</option>
                                                    <option value="Male" {{ $user->gender == 'Male' ? 'selected' : '' }}>Male</option>
                                                    <option value="Female" {{ $user->gender == 'Female' ? 'selected' : '' }}>Female</option>
                                                    <option value="Other" {{ $user->gender == 'Other' ? 'selected' : '' }}>Other</option>
                                                </select>
                                                <p class="text-danger" id="genderError"></p>
                                            </div>
                                            <div class="col-md-12">
                                                <label for="address" class="mb-2">Address</label>
                                                <textarea name="address" id="address" rows="3" class="form-control">{{ old('address', $user->address) }}</textarea>
                                                <p class="text-danger" id="addressError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="city" class="mb-2">City</label>
                                                <input type="text" name="city" id="city" class="form-control" value="{{ old('city', $user->city) }}">
                                                <p class="text-danger" id="cityError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="country" class="mb-2">Country</label>
                                                <input type="text" name="country" id="country" class="form-control" value="{{ old('country', $user->country) }}">
                                                <p class="text-danger" id="countryError"></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border-top mt-4 pt-4">
                                        <h4 class="fs-5 mb-3">Educational Details</h4>
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label for="university" class="mb-2">University</label>
                                                <input type="text" name="university" id="university" class="form-control" value="{{ old('university', $user->university) }}" placeholder="e.g. Fiji National University">
                                                <p class="text-danger" id="universityError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="degree" class="mb-2">Degree / Program</label>
                                                <input type="text" name="degree" id="degree" class="form-control" value="{{ old('degree', $user->degree) }}">
                                                <p class="text-danger" id="degreeError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="major" class="mb-2">Major / Specialization</label>
                                                <input type="text" name="major" id="major" class="form-control" value="{{ old('major', $user->major) }}">
                                                <p class="text-danger" id="majorError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="graduation_year" class="mb-2">Graduation Year</label>
                                                <input type="text" name="graduation_year" id="graduation_year" class="form-control" value="{{ old('graduation_year', $user->graduation_year) }}" placeholder="2027">
                                                <p class="text-danger" id="graduationYearError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="cgpa" class="mb-2">CGPA</label>
                                                <input type="number" step="0.01" min="0" max="4" name="cgpa" id="cgpa" class="form-control" value="{{ old('cgpa', $user->cgpa) }}">
                                                <p class="text-danger" id="cgpaError"></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border-top mt-4 pt-4">
                                        <h4 class="fs-5 mb-3">Other Details</h4>
                                        <div class="row g-4">
                                            <div class="col-md-12">
                                                <label for="skills" class="mb-2">Skills</label>
                                                <textarea name="skills" id="skills" rows="3" class="form-control" placeholder="PHP, Laravel, JavaScript, SQL">{{ old('skills', $user->skills) }}</textarea>
                                                <p class="text-danger" id="skillsError"></p>
                                            </div>
                                            <div class="col-md-12">
                                                <label for="bio" class="mb-2">Short Bio</label>
                                                <textarea name="bio" id="bio" rows="3" class="form-control" placeholder="Tell employers a little about yourself">{{ old('bio', $user->bio) }}</textarea>
                                                <p class="text-danger" id="bioError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="linkedin_url" class="mb-2">LinkedIn URL</label>
                                                <input type="url" name="linkedin_url" id="linkedin_url" class="form-control" value="{{ old('linkedin_url', $user->linkedin_url) }}">
                                                <p class="text-danger" id="linkedinUrlError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="github_url" class="mb-2">GitHub URL</label>
                                                <input type="url" name="github_url" id="github_url" class="form-control" value="{{ old('github_url', $user->github_url) }}">
                                                <p class="text-danger" id="githubUrlError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="portfolio_url" class="mb-2">Portfolio URL</label>
                                                <input type="url" name="portfolio_url" id="portfolio_url" class="form-control" value="{{ old('portfolio_url', $user->portfolio_url) }}">
                                                <p class="text-danger" id="portfolioUrlError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="availability" class="mb-2">Availability</label>
                                                <input type="text" name="availability" id="availability" class="form-control" value="{{ old('availability', $user->availability) }}" placeholder="Available for internships / Ready for full time">
                                                <p class="text-danger" id="availabilityError"></p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                            </div>
                            <div class="card-footer  p-4">
                                <button type="submit" class="btn btn-primary">Update</button>
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
        $('#userForm').submit(function(e) {
            e.preventDefault();
            // var formData = $(this).serialize();
            $.ajax({
                url: "{{ route('account.updateProfile') }}",
                type: "PUT",
                dataType: "json",
                data: $("#userForm").serializeArray(),

                success: function(response) {
                    // Always clear all error messages first
                    $("#nameError").text('');
                    $("#emailError").text('');
                    $("#passwordError").text('');
                    $("#confirmPasswordError").text('');
                    $("#mobileError").text('');
                    $("#designationError").text('');
                    $("#dateOfBirthError").text('');
                    $("#genderError").text('');
                    $("#addressError").text('');
                    $("#cityError").text('');
                    $("#countryError").text('');
                    $("#universityError").text('');
                    $("#degreeError").text('');
                    $("#majorError").text('');
                    $("#graduationYearError").text('');
                    $("#cgpaError").text('');
                    $("#skillsError").text('');
                    $("#bioError").text('');
                    $("#linkedinUrlError").text('');
                    $("#githubUrlError").text('');
                    $("#portfolioUrlError").text('');
                    $("#availabilityError").text('');

                    if (response.status == true) {
                        window.location.href = "{{ route('account.editProfile') }}";
                    } else {
                        var errors = response.errors;

                        if (errors.name) {
                            $("#nameError").text(errors.name[0]);
                        }
                        if (errors.email) {
                            $("#emailError").text(errors.email[0]);
                        }
                        if (errors.designation) {
                            $("#designationError").text(errors.designation[0]);
                        }
                        if (errors.mobile) {
                            $("#mobileError").text(errors.mobile[0]);
                        }
                        if (errors.date_of_birth) {
                            $("#dateOfBirthError").text(errors.date_of_birth[0]);
                        }
                        if (errors.gender) {
                            $("#genderError").text(errors.gender[0]);
                        }
                        if (errors.address) {
                            $("#addressError").text(errors.address[0]);
                        }
                        if (errors.city) {
                            $("#cityError").text(errors.city[0]);
                        }
                        if (errors.country) {
                            $("#countryError").text(errors.country[0]);
                        }
                        if (errors.university) {
                            $("#universityError").text(errors.university[0]);
                        }
                        if (errors.degree) {
                            $("#degreeError").text(errors.degree[0]);
                        }
                        if (errors.major) {
                            $("#majorError").text(errors.major[0]);
                        }
                        if (errors.graduation_year) {
                            $("#graduationYearError").text(errors.graduation_year[0]);
                        }
                        if (errors.cgpa) {
                            $("#cgpaError").text(errors.cgpa[0]);
                        }
                        if (errors.skills) {
                            $("#skillsError").text(errors.skills[0]);
                        }
                        if (errors.bio) {
                            $("#bioError").text(errors.bio[0]);
                        }
                        if (errors.linkedin_url) {
                            $("#linkedinUrlError").text(errors.linkedin_url[0]);
                        }
                        if (errors.github_url) {
                            $("#githubUrlError").text(errors.github_url[0]);
                        }
                        if (errors.portfolio_url) {
                            $("#portfolioUrlError").text(errors.portfolio_url[0]);
                        }
                        if (errors.availability) {
                            $("#availabilityError").text(errors.availability[0]);
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
