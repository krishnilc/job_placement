@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.categories') }}">Categories</a></li>
                            <li class="breadcrumb-item active">Edit Category</li>
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
                                <form action="" method="POST" id="categoryForm" name="categoryForm">
                                    @csrf
                                    @method('PUT')
                                    <div class="card-body p-4">
                                        <h3 class="fs-4 mb-1">Edit Category</h3>
                                        <div class="mb-4">
                                            <label for="name" class="mb-2">Name*</label>
                                            <input type="text" name="name" id="name" class="form-control"
                                                value="{{ old('name', $category->name) }}">
                                            <p class="text-danger" id="nameError"></p>
                                        </div>
                                        <div class="mb-4">
                                            <label for="college_id" class="mb-2">College*</label>
                                            <select name="college_id" id="college_id" class="form-control">
                                                <option value="">Select a College</option>
                                                @foreach ($colleges as $college)
                                                    <option value="{{ $college->id }}"
                                                        {{ old('college_id', $category->college_id) == $college->id ? 'selected' : '' }}>
                                                        {{ $college->display_name }}</option>
                                                @endforeach
                                            </select>
                                            <p class="text-danger" id="college_idError"></p>
                                        </div>
                                        <div class="mb-4">
                                            <label for="status" class="mb-2">Status*</label>
                                            <select name="status" id="status" class="form-control fw-bold {{ old('status', $category->status) == 1 ? 'text-success' : 'text-danger' }}">
                                                <option value="1" class="text-success fw-bold" {{ old('status', $category->status) == 1 ? 'selected' : '' }}>Active</option>
                                                <option value="0" class="text-danger fw-bold" {{ old('status', $category->status) == 0 ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                            <p class="text-danger" id="statusError"></p>
                                        </div>
                                    </div>
                                    <div class="card-footer p-4">
                                        <button type="submit" class="btn btn-primary">Update Category</button>
                                        <a href="{{ route('admin.categories') }}" class="btn btn-secondary">Cancel</a>
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

        $('#categoryForm').submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('admin.categories.update', $category->id) }}",
                type: "POST",
                dataType: "json",
                data: $("#categoryForm").serializeArray(),

                success: function(response) {
                    $("#nameError").text('');
                    $("#college_idError").text('');
                    $("#statusError").text('');

                    if (response.status == true) {
                        window.location.href = "{{ route('admin.categories') }}";
                    } else {
                        var errors = response.errors;

                        if (errors.name) {
                            $("#nameError").text(errors.name[0]);
                        }
                        if (errors.college_id) {
                            $("#college_idError").text(errors.college_id[0]);
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
