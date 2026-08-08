@extends('layouts.admin')

@section('title', 'Hero Slides')
@section('page-title', 'Hero Slides')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Hero Slides</li>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="card-title mb-0">Homepage Hero Carousel</h3>
    <a href="{{ route('admin.hero-slides.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Slide
    </a>
</div>

<div class="row">
    @forelse($slides as $slide)
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="position-relative">
                <img src="{{ $slide->image_url }}" class="card-img-top" style="height: 220px; object-fit: cover;" alt="{{ $slide->title ?: 'Slide image' }}">
                <span class="badge badge-{{ $slide->is_active ? 'success' : 'secondary' }} position-absolute" style="top: 10px; right: 10px; font-size: 0.85rem;">
                    {{ $slide->is_active ? 'Active' : 'Inactive' }}
                </span>
                <span class="badge badge-dark position-absolute" style="top: 10px; left: 10px; font-size: 0.85rem;">
                    Order: {{ $slide->sort_order }}
                </span>
            </div>
            <div class="card-body">
                @if($slide->title)
                    <h5 class="card-title">{{ $slide->title }}</h5>
                @else
                    <h5 class="card-title text-muted font-italic">Image only (no text)</h5>
                @endif
                @if($slide->subtitle)
                    <p class="card-text text-muted small">{{ \Illuminate\Support\Str::limit($slide->subtitle, 90) }}</p>
                @endif
                @if($slide->button_text)
                    <span class="badge badge-info">Button: {{ $slide->button_text }}</span>
                @endif
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('admin.hero-slides.edit', $slide) }}" class="btn btn-warning btn-sm" title="Edit">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <div>
                    <form action="{{ route('admin.hero-slides.toggle', $slide) }}" method="POST" class="d-inline">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-{{ $slide->is_active ? 'secondary' : 'success' }}" title="{{ $slide->is_active ? 'Deactivate' : 'Activate' }}">
                            <i class="fas fa-{{ $slide->is_active ? 'eye-slash' : 'eye' }}"></i>
                        </button>
                    </form>
                    <form action="{{ route('admin.hero-slides.destroy', $slide) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this slide?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5 text-muted">
                No slides yet. The homepage will show a default banner until you add one.
            </div>
        </div>
    </div>
    @endforelse
</div>
@endsection
