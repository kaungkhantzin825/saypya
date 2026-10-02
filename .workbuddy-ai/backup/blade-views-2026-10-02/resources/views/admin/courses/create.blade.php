@extends('layouts.admin')

@section('title', 'Create Course')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Create New Course</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.courses.index') }}">Courses</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form action="{{ route('admin.courses.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Course Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="title">Course Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="category_id">Category <span class="text-danger">*</span></label>
                                        <select class="form-control @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="instructor_id">Instructor <span class="text-danger">*</span></label>
                                        <select class="form-control @error('instructor_id') is-invalid @enderror" id="instructor_id" name="instructor_id" required>
                                            <option value="">Select Instructor</option>
                                            @foreach($instructors as $instructor)
                                                <option value="{{ $instructor->id }}" {{ old('instructor_id') == $instructor->id ? 'selected' : '' }}>{{ $instructor->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('instructor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="short_description">Short Description</label>
                                <textarea class="form-control @error('short_description') is-invalid @enderror" id="short_description" name="short_description" rows="2" maxlength="500">{{ old('short_description') }}</textarea>
                                @error('short_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group">
                                <label for="description">Full Description <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="6" required>{{ old('description') }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group">
                                <label>What You'll Learn</label>
                                <div id="what-you-learn-container">
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="what_you_learn[]" placeholder="Enter learning outcome">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-success add-field" data-target="what-you-learn-container"><i class="fas fa-plus"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Requirements</label>
                                <div id="requirements-container">
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="requirements[]" placeholder="Enter requirement">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-success add-field" data-target="requirements-container"><i class="fas fa-plus"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Course Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="thumbnail">Thumbnail <span class="text-danger">*</span></label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('thumbnail') is-invalid @enderror" id="thumbnail" name="thumbnail" accept="image/*" required>
                                    <label class="custom-file-label" for="thumbnail">Choose file</label>
                                </div>
                                @error('thumbnail')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="price">Price <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', 0) }}" min="0" step="0.01" required>
                                        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="discount_price">Discount Price</label>
                                        <input type="number" class="form-control @error('discount_price') is-invalid @enderror" id="discount_price" name="discount_price" value="{{ old('discount_price') }}" min="0" step="0.01">
                                        @error('discount_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="level">Level <span class="text-danger">*</span></label>
                                <select class="form-control @error('level') is-invalid @enderror" id="level" name="level" required>
                                    <option value="beginner" {{ old('level') == 'beginner' ? 'selected' : '' }}>Beginner</option>
                                    <option value="intermediate" {{ old('level') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                    <option value="advanced" {{ old('level') == 'advanced' ? 'selected' : '' }}>Advanced</option>
                                </select>
                                @error('level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group">
                                <label for="language">Language <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('language') is-invalid @enderror" id="language" name="language" value="{{ old('language', 'Myanmar') }}" required>
                                @error('language')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group">
                                <label for="preview_video">Preview Video URL</label>
                                <input type="url" class="form-control @error('preview_video') is-invalid @enderror" id="preview_video" name="preview_video" value="{{ old('preview_video') }}" placeholder="https://youtube.com/...">
                                @error('preview_video')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_featured">Featured Course</label>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary btn-block">Create Course</button>
                            <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary btn-block">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Custom file input label
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });

    // Add dynamic fields
    $(document).on('click', '.add-field', function() {
        let container = $('#' + $(this).data('target'));
        let fieldName = container.find('input').first().attr('name');
        let placeholder = container.find('input').first().attr('placeholder');
        container.append(`
            <div class="input-group mb-2">
                <input type="text" class="form-control" name="${fieldName}" placeholder="${placeholder}">
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger remove-field"><i class="fas fa-minus"></i></button>
                </div>
            </div>
        `);
    });

    $(document).on('click', '.remove-field', function() {
        $(this).closest('.input-group').remove();
    });
});
</script>
@endpush
