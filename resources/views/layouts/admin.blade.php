@extends('layouts.adminlte')

@section('sidebar')
<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
    <!-- Dashboard -->
    <li class="nav-item">
        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
        </a>
    </li>

    <!-- Users Management -->
    <li class="nav-item {{ request()->routeIs('admin.users*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-users"></i>
            <p>Users <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>All Users</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.users.index', ['role' => 'student']) }}" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Students</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.users.index', ['role' => 'lecturer']) }}" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Lecturers</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.users.create') }}" class="nav-link {{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
                    <i class="far fa-plus-square nav-icon"></i>
                    <p>Add User</p>
                </a>
            </li>
        </ul>
    </li>

    <!-- Categories -->
    <li class="nav-item {{ request()->routeIs('admin.categories*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-folder"></i>
            <p>Categories <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.index') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>All Categories</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.categories.create') }}" class="nav-link {{ request()->routeIs('admin.categories.create') ? 'active' : '' }}">
                    <i class="far fa-plus-square nav-icon"></i>
                    <p>Add Category</p>
                </a>
            </li>
        </ul>
    </li>

    <!-- Courses -->
    <li class="nav-item {{ request()->routeIs('admin.courses*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->routeIs('admin.courses*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-book"></i>
            <p>Courses <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('admin.courses.index') }}" class="nav-link {{ request()->routeIs('admin.courses.index') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>All Courses</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.courses.index', ['status' => 'draft']) }}" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Pending Approval</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.courses.index', ['status' => 'published']) }}" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Published</p>
                </a>
            </li>
        </ul>
    </li>

    <!-- Enrollments -->
    <li class="nav-item">
        <a href="{{ route('admin.enrollments.index') }}" class="nav-link {{ request()->routeIs('admin.enrollments*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-graduate"></i>
            <p>Enrollments</p>
        </a>
    </li>

    <!-- Reviews -->
    <li class="nav-item">
        <a href="{{ route('admin.reviews.index') }}" class="nav-link {{ request()->routeIs('admin.reviews*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-star"></i>
            <p>Reviews</p>
        </a>
    </li>

    <!-- Reports -->
    <li class="nav-item">
        <a href="{{ route('admin.reports') }}" class="nav-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}">
            <i class="nav-icon fas fa-chart-bar"></i>
            <p>Reports</p>
        </a>
    </li>

    <!-- Settings -->
    <li class="nav-item">
        <a href="{{ route('admin.settings') }}" class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
            <i class="nav-icon fas fa-cog"></i>
            <p>Settings</p>
        </a>
    </li>
</ul>
@endsection
