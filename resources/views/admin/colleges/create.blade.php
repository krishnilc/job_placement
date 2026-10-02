@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.colleges') }}">Colleges</a></li>
                            <li class="breadcrumb-item active">Add College</li>
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
                                <form action="" method="POST" id="collegeForm" name="collegeForm">
                                    @csrf
                                    <div class="card-body p-4">
                                        <h3 class="fs-4 mb-1">Add College</h3>
                                        <div class="mb-4">
                                            <label for="name" class="mb-2">Name*</label>
                                            <input type="text" name="name" id="name" class="form-control" required> 
                                            <p class="text-danger" id="nameError"></p>
                                        </div>
                                        <div class="mb-4">
                                            <label for="code" class="mb-2">Code*</label>
                                            <input type="text" name="code" id="code" class="form-control"
                                                placeholder="e.g. CAFF" maxlength="20" required>
                                            <p class="text-danger" id="codeError"></p>
                                        </div>
                                        <div class="mb-4">
                                            <label for="status" class="mb-2">Status*</label>
                                            <select name="status" id="status" class="form-control fw-bold text-success">
                                                <option value="1" class="text-success fw-bold">Active</option>
                                                <option value="0" class="text-danger fw-bold">Inactive</option>
                                            </select>
                                            <p class="text-danger" id="statusError"></p>
                                        </div>
                                    </div>
                                    <div class="card-footer p-4">
                                        <button type="submit" class="btn btn-primary">Create College</button>
                                        <a href="{{ route('admin.colleges') }}" class="btn btn-secondary">Cancel</a>
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
        function updateStatusColor() {
            var status = $('#status');
            status.removeClass('text-success text-danger');
            status.addClass(status.val() === '1' ? 'text-success' : 'text-danger');
        }

        $('#status').on('change', updateStatusColor);
        updateStatusColor();

        $('#collegeForm').submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('admin.colleges.store') }}",
                type: "POST",
                dataType: "json",
                data: $("#collegeForm").serializeArray(),

                success: function(response) {
                    $("#nameError").text('');
                    $("#codeError").text('');
                    $("#statusError").text('');

                    if (response.status == true) {
                        window.location.href = "{{ route('admin.colleges') }}";
                    } else {
                        var errors = response.errors;

                        if (errors.name) {
                            $("#nameError").text(errors.name[0]);
                        }
                        if (errors.code) {
                            $("#codeError").text(errors.code[0]);
                        }
                        if (errors.status) {
                            $("#statusError").text(errors.status[0]);
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
