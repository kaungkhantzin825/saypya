@extends('layouts.admin')

@section('title', 'Add Slide')
@section('page-title', 'Add Hero Slide')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.hero-slides.index') }}">Hero Slides</a></li>
<li class="breadcrumb-item active">Add Slide</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Slide Information</h3>
            </div>
            <form action="{{ route('admin.hero-slides.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title') }}">
                        @error('title')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="text-muted">Leave everything below blank too for an image-only slide (no text overlay).</small>
                    </div>

                    <div class="form-group">
                        <label for="subtitle">Subtitle</label>
                        <textarea name="subtitle" id="subtitle" rows="2" class="form-control @error('subtitle') is-invalid @enderror">{{ old('subtitle') }}</textarea>
                        @error('subtitle')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="button_text">Button Text</label>
                                <input type="text" name="button_text" id="button_text" class="form-control @error('button_text') is-invalid @enderror"
                                       value="{{ old('button_text') }}" placeholder="Browse Courses">
                                @error('button_text')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="button_link">Button Link</label>
                                <input type="text" name="button_link" id="button_link" class="form-control @error('button_link') is-invalid @enderror"
                                       value="{{ old('button_link') }}" placeholder="/courses">
                                @error('button_link')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="image_url">Image URL</label>
                        <input type="text" name="image_url" id="image_url" class="form-control @error('image_url') is-invalid @enderror"
                               value="{{ old('image_url') }}" placeholder="https://example.com/image.jpg">
                        @error('image_url')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="text-muted">Paste an image URL, or upload a file below. Uploading overrides the URL.</small>
                    </div>

                    <div class="form-group">
                        <label for="image">Upload Image</label>
                        <input type="file" name="image" id="image" accept="image/*" class="form-control-file @error('image') is-invalid @enderror">
                        @error('image')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sort_order">Sort Order</label>
                                <input type="number" name="sort_order" id="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                                       value="{{ old('sort_order', 0) }}" min="0">
                                <small class="text-muted">Slides rotate in ascending order.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="is_active" id="is_active" class="custom-control-input" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Create Slide</button>
                    <a href="{{ route('admin.hero-slides.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
