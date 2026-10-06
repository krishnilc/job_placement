@extends('front.layouts.app')

@section('main')
    <section class="section-5">
        <div class="container my-5">
            <div class="py-lg-2">&nbsp;</div>
            <div class="row d-flex justify-content-center">
                <div class="col-md-5">
                    <div class="card shadow border-0 p-5">
                        <h1 class="h3">Register</h1>
                        <form name="registrationForm" id="registrationForm">
                            @csrf
                            <div class="mb-4">
                                <label class="mb-2 d-block">I am a*</label>
                                <div class="form-check-inline">
                                    <input class="form-check-input" type="radio" value="student" id="student_role"
                                        name="role" checked>
                                    <label class="form-check-label">
                                        Student
                                    </label>
                                </div>

                                <div class="form-check-inline">
                                    <input class="form-check-input" type="radio" value="alumni" id="alumni_role"
                                        name="role">
                                    <label class="form-check-label">
                                        Alumni
                                    </label>
                                </div>

                                <div class="form-check-inline">
                                    <input class="form-check-input" type="radio" value="employer" id="employer_role"
                                        name="role">
                                    <label class="form-check-label">
                                        Employer
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="" class="mb-2">Name*</label>
                                <input type="text" name="name" id="name" class="form-control"
                                    placeholder="Enter Name" required>
                                <p class="text-danger" id="nameError"></p>
                            </div>

                            <div class="mb-3">
                                <label for="" class="mb-2">Email*</label>
                                <input type="email" name="email" id="email" class="form-control"
                                    placeholder="Enter Email" required>
                                <p class="text-danger" id="emailError"></p>
                            </div>

                            <div class="mb-3">
                                <label for="mobile" class="mb-2">Mobile Number*</label>
                                <input type="text" name="mobile" id="mobile" class="form-control"
                                    placeholder="Enter 7-digit mobile number" required>
                                <p class="text-danger" id="mobileError"></p>
                            </div>

                            <div class="mb-3">
                                <label for="" class="mb-2">Password*</label>
                                <input type="password" name="password" id="password" class="form-control"
                                    placeholder="Enter Password" required>
                                <p class="text-danger" id="passwordError"></p>
                            </div>

                            <div class="mb-3">
                                <label for="" class="mb-2">Confirm Password*</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                                    placeholder="Please confirm Password" required>
                                <p class="text-danger" id="confirmPasswordError"></p>
                            </div>

                            {{-- Role-specific details --}}
                            <hr class="my-4">
                            <h2 class="h6 text-muted mb-3" id="roleSectionLabel">Student details</h2>

                            <div class="mb-3" id="studentIdGroup">
                                <label for="student_id" class="mb-2">Student ID<span id="studentIdRequired">*</span></label>
                                <input type="text" name="student_id" id="student_id" class="form-control"
                                    placeholder="Enter University Student ID">
                                {{-- <small class="text-muted" id="studentIdHint" style="display: none;">Optional — you can fill this in later from your profile.</small> --}}
                                <p class="text-danger" id="studentIdError"></p>
                            </div>

                            <div class="mb-3" id="dobGroup">
                                <label for="date_of_birth" class="mb-2">Date of Birth*</label>
                                <input type="date" name="date_of_birth" id="date_of_birth" class="form-control">
                                <p class="text-danger" id="dobError"></p>
                            </div>

                            <div class="mb-3" id="graduationYearGroup" style="display: none;">
                                <label for="graduation_year" class="mb-2">Year Graduated*</label>
                                <input type="number" name="graduation_year" id="graduation_year" class="form-control"
                                    placeholder="e.g. 2023" min="1950" max="{{ date('Y') }}">
                                <p class="text-danger" id="graduationYearError"></p>
                            </div>

                            <div id="employerFields" style="display: none;">
                                <div class="mb-3">
                                    <label for="designation" class="mb-2">Designation*</label>
                                    <input type="text" name="designation" id="designation" class="form-control"
                                        placeholder="e.g. HR Manager">
                                    <p class="text-danger" id="designationError"></p>
                                </div>
                                <input type="hidden" id="organization_mode" name="organization_mode" value="existing" disabled>
                                @include('organizations.picker')
                                <button type="button" class="btn btn-link p-0 mb-3" id="requestOrganizationButton">Can't find your organization? Request a new organization.</button>
                                <p class="text-danger" id="organization_modeError"></p>
                                <div id="newOrganizationFields" style="display: none;">
                                    <p class="text-muted small">Your contact account will remain pending while an administrator reviews the organization request.</p>
                                    <div class="mb-3">
                                        <label for="company_name" class="mb-2">Organization Name*</label>
                                        <input type="text" name="company_name" id="company_name" class="form-control"
                                            placeholder="Enter organization name">
                                        <p class="text-danger" id="companyNameError"></p>
                                    </div>
                                    <div class="mb-3">
                                        <label for="company_address" class="mb-2">Organization Address*</label>
                                        <textarea name="company_address" id="company_address" class="form-control"
                                            placeholder="Enter organization address" rows="3"></textarea>
                                        <p class="text-danger" id="companyAddressError"></p>
                                    </div>
                                    <div class="mb-3">
                                        <label for="company_phone" class="mb-2">Organization Phone*</label>
                                        <input type="tel" name="company_phone" id="company_phone" class="form-control"
                                            placeholder="Enter organization phone number">
                                        <p class="text-danger" id="companyPhoneError"></p>
                                    </div>
                                </div>
                            </div>

                            <p class="text-muted small">After registration, verify your email using the link we send you. Your account also requires administrator approval before you can log in.</p>
                            <button class="btn btn-primary mt-2">Register</button>
                        </form>
                    </div>
                    <div class="mt-4 text-center">
                        <p>Have an account? <a href="{{ route('account.login') }}">Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('customJS')
    <script src="{{ asset('assets/js/organization-picker.js') }}"></script>
    <script>
        $(function() {
            function toggleOrganizationFields() {
                var employer = $('input[name="role"]:checked').val() === 'employer';
                var newOrganization = $('#organization_mode').val() === 'request';
                $('#organization_mode').prop('disabled', !employer);
                $('#organization_id').prop('disabled', !employer || newOrganization).prop('required', employer && !newOrganization);
                $('#organizationPicker').toggle(!newOrganization);
                $('#newOrganizationFields').toggle(newOrganization);
                $('#company_name, #company_address, #company_phone').prop('disabled', !employer || !newOrganization).prop('required', employer && newOrganization);
                $('#requestOrganizationButton').text(newOrganization
                    ? 'Search existing organizations instead'
                    : "Can't find your organization? Request a new organization.");
            }
            $('#requestOrganizationButton').on('click', function() {
                $('#organization_mode').val($('#organization_mode').val() === 'existing' ? 'request' : 'existing');
                toggleOrganizationFields();
            });
            function toggleStudentField() {
                var role = $('input[name="role"]:checked').val();

                if (role === 'employer') {
                    // Employer: hide student/alumni fields
                    $('#roleSectionLabel').text('Employer contact and organization');
                    $('#studentIdGroup').hide();
                    $('#student_id').removeAttr('required');
                    $('#studentIdError').text('');
                    $('#dobGroup').hide();
                    $('#date_of_birth').removeAttr('required');
                    $('#dobError').text('');
                    $('#graduationYearGroup').hide();
                    $('#graduation_year').removeAttr('required');
                    $('#graduationYearError').text('');
                    $('#employerFields').show();
                    $('#designation').attr('required', true);
                } else if (role === 'alumni') {
                    // Alumni: student_id optional, DOB + graduation year mandatory
                    $('#roleSectionLabel').text('Alumni details');
                    $('#studentIdGroup').show();
                    $('#student_id').removeAttr('required');
                    $('#studentIdRequired').hide();
                    $('#studentIdHint').show();
                    $('#dobGroup').show();
                    $('#date_of_birth').attr('required', true);
                    $('#graduationYearGroup').show();
                    $('#graduation_year').attr('required', true);
                    $('#employerFields').hide();
                    $('#designation, #company_name, #company_address, #company_phone').removeAttr('required');
                } else {
                    // Student: student_id + DOB mandatory, no graduation year
                    $('#roleSectionLabel').text('Student details');
                    $('#studentIdGroup').show();
                    $('#student_id').attr('required', true);
                    $('#studentIdRequired').show();
                    $('#studentIdHint').hide();
                    $('#dobGroup').show();
                    $('#date_of_birth').attr('required', true);
                    $('#graduationYearGroup').hide();
                    $('#graduation_year').removeAttr('required');
                    $('#graduationYearError').text('');
                    $('#employerFields').hide();
                    $('#designation, #company_name, #company_address, #company_phone').removeAttr('required');
                }
                toggleOrganizationFields();
            }

            toggleStudentField();

            $('input[name="role"]').change(function() {
                toggleStudentField();
            });

            $("#registrationForm").submit(function(e) {
                e.preventDefault();

                $.ajax({
                    url: "{{ route('account.processRegistration') }}",
                    type: "POST",
                    data: $("#registrationForm").serialize(),
                    dataType: "json",

                    success: function(response) {
                        // Always clear all error messages first
                        $("#nameError").text('');
                        $("#emailError").text('');
                        $("#passwordError").text('');
                        $("#confirmPasswordError").text('');
                        $("#studentIdError").text('');
                        $("#dobError").text('');
                        $("#graduationYearError").text('');
                        $("#mobileError").text('');
                        $("#designationError").text('');
                        $("#companyNameError").text('');
                        $("#companyAddressError").text('');
                        $("#companyPhoneError").text('');
                        $("#organization_idError, #organization_modeError").text('');

                        if (response.status == false) {
                            var errors = response.errors;
                            if (errors.name) {
                                $("#nameError").text(errors.name[0]);
                            }
                            if (errors.email) {
                                $("#emailError").text(errors.email[0]);
                            }
                            if (errors.password) {
                                $("#passwordError").text(errors.password[0]);
                            }
                            if (errors.confirm_password) {
                                $("#confirmPasswordError").text(errors.confirm_password[0]);
                            }
                            if (errors.student_id) {
                                $("#studentIdError").text(errors.student_id[0]);
                            }
                            if (errors.date_of_birth) {
                                $("#dobError").text(errors.date_of_birth[0]);
                            }
                            if (errors.graduation_year) {
                                $("#graduationYearError").text(errors.graduation_year[0]);
                            }
                            if (errors.mobile) {
                                $("#mobileError").text(errors.mobile[0]);
                            }
                            if (errors.designation) {
                                $("#designationError").text(errors.designation[0]);
                            }
                            if (errors.company_name) {
                                $("#companyNameError").text(errors.company_name[0]);
                            }
                            if (errors.company_address) {
                                $("#companyAddressError").text(errors.company_address[0]);
                            }
                            if (errors.company_phone) {
                                $("#companyPhoneError").text(errors.company_phone[0]);
                            }
                            if (errors.organization_id) {
                                $("#organization_idError").text(errors.organization_id[0]);
                            }
                            if (errors.organization_mode) {
                                $("#organization_modeError").text(errors.organization_mode[0]);
                            }
                        } else {
                            window.location.href = response.redirect;
                            $("#registrationForm")[0].reset();
                        }
                    },
                    error: function() {
                        alert('Unable to register. Please try again.');
                    }
                });
            });
        });
    </script>
@endsection
