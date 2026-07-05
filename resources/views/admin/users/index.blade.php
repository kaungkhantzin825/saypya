@extends('layouts.admin')

@section('title', 'Users Management')
@section('page-title', 'Users Management')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Users</li>
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

{{-- ── Top Action Bar ──────────────────────────────────────────────────────── --}}
<div class="row mb-3">

    {{-- Registration Toggle --}}
    <div class="col-md-5">
        <div class="card card-outline card-{{ $registrationEnabled ? 'success' : 'danger' }} mb-0">
            <div class="card-body py-3 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 font-weight-bold">
                        <i class="fas fa-{{ $registrationEnabled ? 'unlock' : 'lock' }} mr-1"></i>
                        Public Registration
                    </h6>
                    <small class="text-muted">
                        {{ $registrationEnabled ? 'New users can register on the site.' : 'Registration is blocked. Only admin can create accounts.' }}
                    </small>
                </div>
                <form action="{{ route('admin.toggle-registration') }}" method="POST" class="ml-3">
                    @csrf
                    <button type="submit"
                            class="btn btn-sm btn-{{ $registrationEnabled ? 'danger' : 'success' }}"
                            onclick="return confirm('{{ $registrationEnabled ? 'Disable' : 'Enable' }} public registration?')">
                        <i class="fas fa-{{ $registrationEnabled ? 'ban' : 'check' }} mr-1"></i>
                        {{ $registrationEnabled ? 'Disable' : 'Enable' }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Quick Create Buttons --}}
    <div class="col-md-7 d-flex align-items-center justify-content-end gap-2" style="gap:8px;">
        <a href="{{ route('admin.create-lecturer') }}" class="btn btn-warning">
            <i class="fas fa-chalkboard-teacher mr-1"></i> Create Lecturer Account
        </a>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="fas fa-user-plus mr-1"></i> Add User
        </a>
    </div>
</div>

{{-- ── Users Table Card ─────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Users</h3>
    </div>
    <div class="card-body">

        {{-- Filters --}}
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <select name="role" class="form-control">
                        <option value="">All Roles</option>
                        <option value="admin"    {{ request('role') == 'admin'    ? 'selected' : '' }}>Admin</option>
                        <option value="lecturer" {{ request('role') == 'lecturer' ? 'selected' : '' }}>Lecturer</option>
                        <option value="student"  {{ request('role') == 'student'  ? 'selected' : '' }}>Student</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="active"   {{ request('status') == 'active'   ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control"
                           placeholder="Search by name or email..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-info btn-block">
                        <i class="fas fa-filter mr-1"></i>Filter
                    </button>
                </div>
            </div>
        </form>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>
                            <img src="{{ $user->avatar ? Storage::url($user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=30' }}"
                                 class="img-circle mr-2" style="width:30px;height:30px;object-fit:cover;">
                            {{ $user->name }}
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge badge-{{ $user->role == 'admin' ? 'danger' : ($user->role == 'lecturer' ? 'info' : 'success') }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td>
                            @if($user->status === 'active')
                                <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Active</span>
                            @else
                                <span class="badge badge-secondary"><i class="fas fa-ban mr-1"></i>Inactive</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('M d, Y') }}</td>
                        <td style="white-space:nowrap;">
                            {{-- View --}}
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-info btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            {{-- Edit --}}
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            {{-- Enable / Disable (not for yourself) --}}
                            @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            class="btn btn-sm btn-{{ $user->status === 'active' ? 'secondary' : 'success' }}"
                                            title="{{ $user->status === 'active' ? 'Disable account' : 'Enable account' }}"
                                            onclick="return confirm('{{ $user->status === 'active' ? 'Disable' : 'Enable' }} {{ $user->name }}\'s account?')">
                                        <i class="fas fa-{{ $user->status === 'active' ? 'ban' : 'check' }}"></i>
                                        {{ $user->status === 'active' ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                            @endif
                            {{-- Delete (super admin only) --}}
                            @if(auth()->user()->isSuperAdmin() && $user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete"
                                            onclick="return confirm('Permanently delete {{ $user->name }}?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No users found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $users->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
