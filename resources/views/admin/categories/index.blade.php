@extends('layouts.admin')

@section('title', 'Categories Management')
@section('page-title', 'Categories Management')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Categories</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Categories</h3>
        <div class="card-tools">
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Category
            </a>
        </div>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th style="width: 50px">Order</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Courses</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr>
                    <td>{{ $category->sort_order }}</td>
                    <td>
                        @if($category->image)
                        <img src="{{ Storage::url($category->image) }}" class="img-thumbnail" style="max-width: 60px;">
                        @else
                        <span class="text-muted">No image</span>
                        @endif
                    </td>
                    <td>
                        @if($category->icon)
                        <i class="{{ $category->icon }} mr-2"></i>
                        @endif
                        {{ $category->name }}
                    </td>
                    <td><code>{{ $category->slug }}</code></td>
                    <td>
                        <span class="badge badge-info">{{ $category->courses_count ?? $category->courses->count() }} courses</span>
                    </td>
                    <td>
                        <span class="badge badge-{{ $category->is_active ? 'success' : 'secondary' }}">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" 
                                    onclick="return confirm('Are you sure? This will affect all courses in this category.')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">No categories found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
