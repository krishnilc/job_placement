@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                     <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            @if (auth()->user()->role == 'admin')
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            @elseif (auth()->user()->role == 'employer')
                                <li class="breadcrumb-item"><a href="{{ route('employer.dashboard') }}">Home</a></li>
                            @else
                                <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Home</a></li>
                            @endif
                            <li class="breadcrumb-item active" aria-current="page">Profile Update</li>
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
                                            value="{{ $user->name }}" required>
                                        <p class="text-danger" id="nameError"></p>
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
                                            <select name="designation" id="designation" class="form-control" required>
                                                <option value="">Select your status</option>
                                                <option value="Full-time Student" {{ $user->designation == 'Full-time Student' ? 'selected' : '' }}>Full-time Student</option>
                                                <option value="Part-time Student" {{ $user->designation == 'Part-time Student' ? 'selected' : '' }}>Part-time Student</option>
                                                <option value="Alumni" {{ $user->designation == 'Alumni' ? 'selected' : '' }}>
                                                    Alumni</option>
                                            </select>
                                            <p class="text-danger" id="designationError"></p>
                                        </div>
                                    @endif
                                    <div class="col-md-6 mb-4">
                                        <label for="email" class="mb-2">Email*</label>
                                        <input type="text" name="email" id="email" class="form-control"
                                            value="{{ $user->email }}" required>
                                        <p class="text-danger" id="emailError"></p>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label for="email_2" class="mb-2">Additional Email</label>
                                        <input type="email" name="email_2" id="email_2" class="form-control"
                                            value="{{ old('email_2', $user->email_2) }}">
                                        <p class="text-danger" id="email2Error"></p>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label for="mobile" class="mb-2">Mobile*</label>
                                        <input type="text" name="mobile" id="mobile" class="form-control"
                                            value="{{ $user->mobile }}" required>
                                        <p class="text-danger" id="mobileError"></p>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label for="mobile_2" class="mb-2">Additional Mobile</label>
                                        <input type="text" name="mobile_2" id="mobile_2" class="form-control"
                                            value="{{ old('mobile_2', $user->mobile_2) }}">
                                        <p class="text-danger" id="mobile2Error"></p>
                                    </div>
                                </div>

                                @if (auth()->user()->role === 'student')
                                    <div class="border-top mt-4 pt-4">
                                        <h4 class="fs-5 mb-3">Personal Details</h4>
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label for="date_of_birth" class="mb-2">Date of Birth*</label>
                                                <input type="date" name="date_of_birth" id="date_of_birth" class="form-control"
                                                    value="{{ old('date_of_birth', $user->date_of_birth) }}" required>
                                                <p class="text-danger" id="dateOfBirthError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="gender" class="mb-2">Gender*</label>
                                                <select name="gender" id="gender" class="form-control" required>
                                                    <option value="">Select gender</option>
                                                    <option value="Male" {{ $user->gender == 'Male' ? 'selected' : '' }}>Male
                                                    </option>
                                                    <option value="Female" {{ $user->gender == 'Female' ? 'selected' : '' }}>
                                                        Female</option>
                                                    <option value="Other" {{ $user->gender == 'Other' ? 'selected' : '' }}>Other
                                                    </option>
                                                </select>
                                                <p class="text-danger" id="genderError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="residential_address" class="mb-2">Permanent Residential Address*</label>
                                                <textarea name="residential_address" id="residential_address" rows="3"
                                                    class="form-control" required>{{ old('residential_address', $user->residential_address) }}</textarea>
                                                <p class="text-danger" id="residentialAddressError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="postal_address" class="mb-2">Postal Address</label>
                                                <textarea name="postal_address" id="postal_address" rows="3"
                                                    class="form-control" >{{ old('postal_address', $user->postal_address) }}</textarea>
                                                <p class="text-danger" id="postalAddressError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="city" class="mb-2">City*</label>
                                                <input type="text" name="city" id="city" class="form-control"
                                                    value="{{ old('city', $user->city) }}" required>
                                                <p class="text-danger" id="cityError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="country" class="mb-2">Country*</label>
                                                <input type="text" name="country" id="country" class="form-control"
                                                    value="{{ old('country', $user->country) }}" required>
                                                <p class="text-danger" id="countryError"></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border-top mt-4 pt-4">
                                        <h4 class="fs-5 mb-3">Educational Details*</h4>
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label for="high_school" class="mb-2">High School*</label>
                                                <input type="text" name="high_school" id="high_school" class="form-control"
                                                    value="{{ old('high_school', $user->high_school) }}" required>
                                                <p class="text-danger" id="highSchoolError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="high_school_graduation_year" class="mb-2">High School Graduation
                                                    Year*</label>
                                                <input type="text" name="high_school_graduation_year"
                                                    id="high_school_graduation_year" class="form-control"
                                                    value="{{ old('high_school_graduation_year', $user->high_school_graduation_year) }}"
                                                    placeholder="2023" required>
                                                <p class="text-danger" id="highSchoolGraduationYearError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="university" class="mb-2">University*</label>
                                                <input type="text" name="university" id="university" class="form-control"
                                                    value="{{ old('university', $user->university) }}"
                                                    placeholder="e.g. Fiji National University" required>
                                                <p class="text-danger" id="universityError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="degree" class="mb-2">Degree / Program*</label>
                                                <input type="text" name="degree" id="degree" class="form-control"
                                                    value="{{ old('degree', $user->degree) }}" required>
                                                <p class="text-danger" id="degreeError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="major" class="mb-2">Major / Specialization*</label>
                                                <input type="text" name="major" id="major" class="form-control"
                                                    value="{{ old('major', $user->major) }}" required>
                                                <p class="text-danger" id="majorError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="graduation_year" class="mb-2">Graduation Year*</label>
                                                <input type="text" name="graduation_year" id="graduation_year"
                                                    class="form-control"
                                                    value="{{ old('graduation_year', $user->graduation_year) }}"
                                                    placeholder="2027" required>
                                                <p class="text-danger" id="graduationYearError"></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border-top mt-4 pt-4">
                                        <h4 class="fs-5 mb-3">Other Details</h4>
                                        <div class="row g-4">
                                            <div class="col-md-12">
                                                <label for="skills" class="mb-2">Skills*</label>
                                                <textarea name="skills" id="skills" rows="3" class="form-control"
                                                    placeholder="PHP, Laravel, JavaScript, SQL" required>{{ old('skills', $user->skills) }} </textarea>
                                                <p class="text-danger" id="skillsError"></p>
                                            </div>
                                            <div class="col-md-12">
                                                <label for="bio" class="mb-2">Short Bio*</label>
                                                <textarea name="bio" id="bio" rows="3" class="form-control"
                                                    placeholder="Tell employers a little about yourself" required>{{ old('bio', $user->bio) }}</textarea>
                                                <p class="text-danger" id="bioError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="linkedin_url" class="mb-2">LinkedIn URL</label>
                                                <input type="url" name="linkedin_url" id="linkedin_url" class="form-control"
                                                    value="{{ old('linkedin_url', $user->linkedin_url) }}">
                                                <p class="text-danger" id="linkedinUrlError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="facebook_url" class="mb-2">Facebook URL</label>
                                                <input type="url" name="facebook_url" id="facebook_url" class="form-control"
                                                    value="{{ old('facebook_url', $user->facebook_url) }}">
                                                <p class="text-danger" id="facebookUrlError"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="availability" class="mb-2">Availability*</label>
                                                <input type="text" name="availability" id="availability" class="form-control"
                                                    value="{{ old('availability', $user->availability) }}"
                                                    placeholder="Available for internships / Ready for full time" required>
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
        $('#userForm').submit(function (e) {
            e.preventDefault();
            // var formData = $(this).serialize();
            $.ajax({
                url: "{{ route('account.updateProfile') }}",
                type: "PUT",
                dataType: "json",
                data: $("#userForm").serializeArray(),

                success: function (response) {
                    // Always clear all error messages first
                    $("#nameError").text('');
                    $("#emailError").text('');
                    $("#passwordError").text('');
                    $("#confirmPasswordError").text('');
                    $("#mobileError").text('');
                    $("#email2Error").text('');
                    $("#mobile2Error").text('');
                    $("#designationError").text('');
                    $("#dateOfBirthError").text('');
                    $("#genderError").text('');
                    $("#residentialAddressError").text('');
                    $("#postalAddressError").text('');
                    $("#cityError").text('');
                    $("#countryError").text('');
                    $("#highSchoolError").text('');
                    $("#highSchoolGraduationYearError").text('');
                    $("#universityError").text('');
                    $("#degreeError").text('');
                    $("#majorError").text('');
                    $("#graduationYearError").text('');
                    $("#skillsError").text('');
                    $("#bioError").text('');
                    $("#linkedinUrlError").text('');
                    $("#facebookUrlError").text('');
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
                        if (errors.email_2) {
                            $("#email2Error").text(errors.email_2[0]);
                        }
                        if (errors.mobile_2) {
                            $("#mobile2Error").text(errors.mobile_2[0]);
                        }
                        if (errors.date_of_birth) {
                            $("#dateOfBirthError").text(errors.date_of_birth[0]);
                        }
                        if (errors.gender) {
                            $("#genderError").text(errors.gender[0]);
                        }
                        if (errors.residential_address) {
                            $("#residentialAddressError").text(errors.residential_address[0]);
                        }
                        if (errors.postal_address) {
                            $("#postalAddressError").text(errors.postal_address[0]);
                        }
                        if (errors.city) {
                            $("#cityError").text(errors.city[0]);
                        }
                        if (errors.country) {
                            $("#countryError").text(errors.country[0]);
                        }
                        if (errors.high_school) {
                            $("#highSchoolError").text(errors.high_school[0]);
                        }
                        if (errors.high_school_graduation_year) {
                            $("#highSchoolGraduationYearError").text(errors.high_school_graduation_year[0]);
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
                        if (errors.skills) {
                            $("#skillsError").text(errors.skills[0]);
                        }
                        if (errors.bio) {
                            $("#bioError").text(errors.bio[0]);
                        }
                        if (errors.linkedin_url) {
                            $("#linkedinUrlError").text(errors.linkedin_url[0]);
                        }
                        if (errors.facebook_url) {
                            $("#facebookUrlError").text(errors.facebook_url[0]);
                        }
                        if (errors.availability) {
                            $("#availabilityError").text(errors.availability[0]);
                        }
                    }
                },
                error: function (xhr, status, error) {
                    alert('An error occurred: ' + error);
                }
            });
        });      
    </script>
@endsection