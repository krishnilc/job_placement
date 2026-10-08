@extends('front.layouts.app')

@php
    $fields = [
        'name' => ['label' => 'Name*', 'max' => 20],
        'student_id' => ['label' => 'Student ID*', 'max' => 9],
        'email' => ['label' => 'Email*'],
        'email_2' => ['label' => 'Alternate Email'],
        'mobile' => ['label' => 'Mobile', 'max' => 7],
        'mobile_2' => ['label' => 'Alternate Mobile', 'max' => 7],
        'designation' => ['label' => 'Designation', 'max' => 100],
        'date_of_birth' => ['label' => 'Date of Birth', 'type' => 'date'],
        'gender' => ['label' => 'Gender', 'type' => 'select', 'options' => ['' => 'Select gender', 'Male' => 'Male', 'Female' => 'Female', 'Other' => 'Other']],
        'residential_address' => ['label' => 'Residential Address'],
        'postal_address' => ['label' => 'Postal Address'],
        'city' => ['label' => 'City', 'max' => 100],
        'country' => ['label' => 'Country', 'max' => 100],
        'high_school' => ['label' => 'High School'],
        'high_school_graduation_year' => ['label' => 'High School Graduation Year', 'max' => 10],
        'college_id' => ['label' => 'College/Center', 'type' => 'select', 'options' => ['' => 'Select college'] + $colleges->pluck('display_name', 'id')->all()],
        'degree' => ['label' => 'Degree / Program'],
        'major' => ['label' => 'Major'],
        'graduation_year' => ['label' => 'Graduation Year', 'max' => 10],
        'availability' => ['label' => 'Availability'],
        'linkedin_url' => ['label' => 'LinkedIn URL'],
        'facebook_url' => ['label' => 'Facebook URL'],
        'skills' => ['label' => 'Skills', 'type' => 'textarea', 'full' => true],
        'bio' => ['label' => 'Bio', 'type' => 'textarea', 'full' => true],
    ];
@endphp

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.users.students') }}">Students</a></li>
                            <li class="breadcrumb-item active">Edit Student</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3">
                    @include('admin.sidebar')
                </div>
                <div class="col-lg-9">
                    @include('front.message')
                    <div class="card border-0 shadow mb-4">
                        <div class="card-body card-form">
                            <div class="card border-0 shadow mb-4">
                                <form action="" method="POST" id="userForm" name="userForm">
                                    @csrf
                                    <div class="card-body  p-4">
                                        <h3 class="fs-4 mb-1">Student/Edit</h3>
                                        <div class="row g-3 mt-1">
                                            @foreach ($fields as $name => $f)
                                                <div class="{{ $f['full'] ?? false ? 'col-12' : 'col-md-6' }}">
                                                    <label for="{{ $name }}" class="mb-2">
                                                        @if (str_ends_with($f['label'], '*'))
                                                            {{ substr($f['label'], 0, -1) }}<span class="text-danger">*</span>
                                                        @else
                                                            {{ $f['label'] }}
                                                        @endif
                                                    </label>
                                                    @if (($f['type'] ?? 'text') === 'select')
                                                        <select name="{{ $name }}" id="{{ $name }}" class="form-control">
                                                            @foreach ($f['options'] as $value => $text)
                                                                <option value="{{ $value }}" @selected((string) $user->{$name} === (string) $value)>{{ $text }}</option>
                                                            @endforeach
                                                        </select>
                                                    @elseif (($f['type'] ?? 'text') === 'textarea')
                                                        <textarea name="{{ $name }}" id="{{ $name }}" rows="3" class="form-control">{{ $user->{$name} }}</textarea>
                                                    @else
                                                        <input type="{{ $f['type'] ?? 'text' }}" name="{{ $name }}" id="{{ $name }}"
                                                            class="form-control" maxlength="{{ $f['max'] ?? 255 }}"
                                                            value="{{ ($f['type'] ?? 'text') === 'date' && $user->{$name} ? \Illuminate\Support\Carbon::parse($user->{$name})->format('Y-m-d') : $user->{$name} }}">
                                                    @endif
                                                    <p class="text-danger mb-0" id="{{ $name }}Error"></p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="card-footer  p-4">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <a href="{{ route('admin.users.students') }}" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
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

            $.ajax({
                url: "{{ route('admin.users.update', $user->id) }}",
                type: "PUT",
                dataType: "json",
                data: $("#userForm").serializeArray(),

                success: function(response) {
                    $('#userForm .text-danger').text('');

                    if (response.status == true) {
                        window.location.href = "{{ route('admin.users.students') }}";
                    } else {
                        $.each(response.errors, function(field, messages) {
                            $('#' + field + 'Error').text(messages[0]);
                        });
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred: ' + error);
                }
            });
        });
    </script>
@endsection
