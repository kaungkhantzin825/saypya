@extends('layouts.adminlte')

@section('sidebar')
<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
    <!-- Dashboard -->
    <li class="nav-item">
        <a href="{{ route('instructor.dashboard') }}" class="nav-link {{ request()->routeIs('instructor.dashboard') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
        </a>
    </li>

    <!-- My Courses -->
    <li class="nav-item {{ request()->routeIs('instructor.courses*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->routeIs('instructor.courses*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-book"></i>
            <p>My Courses <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('instructor.courses') }}" class="nav-link {{ request()->routeIs('instructor.courses') && !request()->routeIs('instructor.courses.create') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>All Courses</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('instructor.courses.create') }}" class="nav-link {{ request()->routeIs('instructor.courses.create') ? 'active' : '' }}">
                    <i class="far fa-plus-square nav-icon"></i>
                    <p>Create Course</p>
                </a>
            </li>
        </ul>
    </li>

    <!-- My Students -->
    <li class="nav-item">
        <a href="{{ route('instructor.students') }}" class="nav-link {{ request()->routeIs('instructor.students*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-users"></i>
            <p>My Students</p>
        </a>
    </li>

    <!-- Reviews -->
    <li class="nav-item">
        <a href="{{ route('instructor.reviews') }}" class="nav-link {{ request()->routeIs('instructor.reviews*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-star"></i>
            <p>Reviews</p>
        </a>
    </li>

    <!-- Earnings -->
    <li class="nav-item">
        <a href="{{ route('instructor.earnings') }}" class="nav-link {{ request()->routeIs('instructor.earnings*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-dollar-sign"></i>
            <p>Earnings</p>
        </a>
    </li>

    <!-- Analytics -->
    <li class="nav-item">
        <a href="{{ route('instructor.analytics') }}" class="nav-link {{ request()->routeIs('instructor.analytics*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-chart-line"></i>
            <p>Analytics</p>
        </a>
    </li>

    <!-- Profile -->
    <li class="nav-item">
        <a href="{{ route('profile.edit') }}" class="nav-link">
            <i class="nav-icon fas fa-user-cog"></i>
            <p>My Profile</p>
        </a>
    </li>
</ul>
@endsection
