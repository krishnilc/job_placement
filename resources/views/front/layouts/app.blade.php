<!DOCTYPE html>
<html class="no-js" lang="en_AU" />

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>FNU Job Placement | Find Best Jobs</title>
    <meta name="description" content="" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no, maximum-scale=1, user-scalable=no" />
    <meta name="HandheldFriendly" content="True" />
    <meta name="pinterest" content="nopin" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/ui/trumbowyg.min.css"
        integrity="sha512-Fm8kRNVGCBZn0sPmwJbVXlqfJmPC13zRsMElZenX6v721g/H7OukJd8XzDEBRQ2FSATK8xNF9UYvzsCtUpfeJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/style.css') }}" />
    <style>
        /* Keep navbar fixed at top and prevent content overlap */
        .navbar.fixed-top {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
        }

        body {
            padding-top: 80px;
        }

        @media (max-width: 576px) {
            body {
                padding-top: 110px;
            }
        }
    </style>
    <!-- Fav Icon -->
    <link rel="shortcut icon" type="image/x-icon" href="#" />
</head>

<body data-instant-intensity="mousedown">
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow py-2 fixed-top">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="https://www.fnu.ac.fj/" target="_blank">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="FNU Job Placement" style="height:80px;">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-0 ms-sm-0 me-auto mb-2 mb-lg-0 ms-lg-4">
                        <li class="nav-item">
                            <a @class(['nav-link', 'main-nav-active' => request()->routeIs('home')]) aria-current="page" href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a @class(['nav-link', 'main-nav-active' => request()->routeIs('front.jobs', 'jobDetail')]) aria-current="page" href="{{ route('front.jobs') }}">Find Jobs</a>
                        </li>
                        <li class="nav-item">
                            <a @class(['nav-link', 'main-nav-active' => request()->routeIs('front.contact')]) aria-current="page" href="{{ route('front.contact') }}">Contact Us</a>
                        </li>
                    </ul>

                    @if (Auth::check())
                        <div class="dropdown ms-lg-3">
                            <button class="btn btn-primary dropdown-toggle px-4 py-2 fw-semibold rounded-pill"
                                type="button" id="accountDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ Auth::user()->name ?? 'Account' }}
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2"
                                aria-labelledby="accountDropdown">
                                @if (Auth::user()->role == 'admin')
                                    <li>
                                        <a @class(['dropdown-item py-2', 'account-dropdown-active' => request()->routeIs('admin.dashboard')]) href="{{ route('admin.dashboard') }}">
                                            Admin Dashboard
                                        </a>
                                    </li>
                                @endif
                                @if (Auth::user()->role == 'employer')
                                    <li>
                                        <a @class(['dropdown-item py-2', 'account-dropdown-active' => request()->routeIs('employer.dashboard')]) href="{{ route('employer.dashboard') }}">
                                            Employer Dashboard
                                        </a>
                                    </li>
                                @endif

                                @if (Auth::user()->role == 'student')
                                    <li>
                                        <a @class(['dropdown-item py-2', 'account-dropdown-active' => request()->routeIs('student.dashboard')]) href="{{ route('student.dashboard') }}">
                                            Student Dashboard
                                        </a>
                                    </li>
                                @endif

                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a @class(['dropdown-item py-2', 'account-dropdown-active' => request()->routeIs('admin.account.*', 'employer.account.*', 'account.profile', 'account.editProfile', 'account.editPassword')])
                                        href="{{ Auth::user()->role == 'admin' ? route('admin.account.profile') : (Auth::user()->role == 'employer' ? route('employer.account.profile') : route('account.profile')) }}">
                                        Account Settings
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 text-danger" href="{{ route('account.logout') }}">
                                        Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a class="btn btn-primary px-4 py-2 fw-semibold rounded-pill"
                            href="{{ route('account.login') }}">
                            Login
                        </a>

                        <a class="btn btn-primary px-4 py-2 fw-semibold rounded-pill ms-2"
                            href="{{ route('account.registration') }}">
                            Register
                        </a>
                    @endif
                </div>
            </div>
        </nav>
    </header>

    @yield('main')

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title pb-0" id="exampleModalLabel">Change Profile Picture</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="profilePicForm" name="profilePicForm" action="" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="exampleInputEmail1" class="form-label">Profile Image</label>
                            <input type="file" class="form-control" id="profile_pic" name="profile_pic" required>
                            <p class="text-danger" id="image-error"></p>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary mx-3">Update</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark py-2 bg-2 text-white">
        <div class="container">
            <!-- <div class="row">
                <div class="col-md-6 mb-4 mb-md-0">
                    <h5 class="text-white mb-3">FNU Job Placement</h5>
                    <p class="mb-1">Fiji National University</p>
                    <p class="mb-1">Email: <a class="text-white text-decoration-underline" href="mailto:info@fnu.ac.fj">info@fnu.ac.fj</a></p>
                    <p class="mb-0">Phone: +679 1234 567</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5 class="text-white mb-3">Quick Links</h5>
                    <a class="text-white d-block mb-2" href="{{ route('home') }}">Home</a>
                    <a class="text-white d-block mb-2" href="{{ route('front.jobs') }}">Find Jobs</a>
                    <a class="text-white d-block" href="{{ route('front.contact') }}">Contact Us</a>
                </div>
            </div> -->
            <p class="text-center text-white pt-3 fw-bold fs-6 mb-0">© 2026 Fiji National University, all right
                reserved</p>
        </div>
    </footer>
    <script src="{{ asset('assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.5.1.3.min.js') }}"></script>
    <script src="{{ asset('assets/js/instantpages.5.1.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/lazyload.17.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/trumbowyg.min.js"
        integrity="sha512-YJgZG+6o3xSc0k5wv774GS+W1gx0vuSI/kr0E0UylL/Qg/noNspPtYwHPN9q6n59CTR/uhgXfjDXLTRI+uIryg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>


    <script>
        $('.textarea').trumbowyg();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#profilePicForm').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                type: 'POST',
                url: "{{ route('account.updateProfilePic') }}",
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,

                success: function(response) {
                    if (response.status == false) {
                        var errors = response.errors;

                        if (errors.profile_pic) {
                            $('#image-error').html(errors.profile_pic[0]);
                        }
                    } else {
                        // Success - hide modal and reload to show new image
                        $('#exampleModal').modal('hide');
                        location.reload(); // Reload page to show updated profile picture
                    }
                },


            });
        });
    </script>

    @yield('customJS')
</body>

</html>
