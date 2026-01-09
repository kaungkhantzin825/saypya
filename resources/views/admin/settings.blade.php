@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'Platform Settings')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Settings</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">General Settings</h3>
            </div>
            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="site_name">Site Name</label>
                        <input type="text" name="site_name" id="site_name" class="form-control" 
                               value="{{ $settings['site_name'] ?? config('app.name') }}">
                    </div>

                    <div class="form-group">
                        <label for="site_description">Site Description</label>
                        <textarea name="site_description" id="site_description" rows="3" class="form-control">{{ $settings['site_description'] ?? '' }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="contact_email">Contact Email</label>
                        <input type="email" name="contact_email" id="contact_email" class="form-control" 
                               value="{{ $settings['contact_email'] ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label for="contact_phone">Contact Phone</label>
                        <input type="text" name="contact_phone" id="contact_phone" class="form-control" 
                               value="{{ $settings['contact_phone'] ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea name="address" id="address" rows="2" class="form-control">{{ $settings['address'] ?? '' }}</textarea>
                    </div>

                    <hr>
                    <h5>Social Media Links</h5>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="facebook_url"><i class="fab fa-facebook"></i> Facebook</label>
                                <input type="url" name="facebook_url" id="facebook_url" class="form-control" 
                                       value="{{ $settings['facebook_url'] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="twitter_url"><i class="fab fa-twitter"></i> Twitter</label>
                                <input type="url" name="twitter_url" id="twitter_url" class="form-control" 
                                       value="{{ $settings['twitter_url'] ?? '' }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="youtube_url"><i class="fab fa-youtube"></i> YouTube</label>
                                <input type="url" name="youtube_url" id="youtube_url" class="form-control" 
                                       value="{{ $settings['youtube_url'] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="linkedin_url"><i class="fab fa-linkedin"></i> LinkedIn</label>
                                <input type="url" name="linkedin_url" id="linkedin_url" class="form-control" 
                                       value="{{ $settings['linkedin_url'] ?? '' }}">
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5>Payment Settings</h5>

                    <div class="form-group">
                        <label for="currency">Currency</label>
                        <select name="currency" id="currency" class="form-control">
                            <option value="USD" {{ ($settings['currency'] ?? 'USD') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                            <option value="MMK" {{ ($settings['currency'] ?? '') == 'MMK' ? 'selected' : '' }}>MMK - Myanmar Kyat</option>
                            <option value="EUR" {{ ($settings['currency'] ?? '') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="commission_rate">Platform Commission Rate (%)</label>
                        <input type="number" name="commission_rate" id="commission_rate" class="form-control" 
                               value="{{ $settings['commission_rate'] ?? 20 }}" min="0" max="100">
                        <small class="text-muted">Percentage taken from instructor earnings</small>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.cache.clear') }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-block">
                        <i class="fas fa-broom mr-2"></i>Clear Cache
                    </button>
                </form>

                <a href="{{ route('admin.users.create') }}" class="btn btn-info btn-block">
                    <i class="fas fa-user-plus mr-2"></i>Add New User
                </a>

                <a href="{{ route('admin.categories.create') }}" class="btn btn-success btn-block">
                    <i class="fas fa-folder-plus mr-2"></i>Add New Category
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">System Info</h3>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Laravel Version</span>
                        <span>{{ app()->version() }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>PHP Version</span>
                        <span>{{ phpversion() }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Environment</span>
                        <span>{{ app()->environment() }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
