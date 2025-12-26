<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        // Redirect based on role
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        
        if ($user->isLecturer()) {
            return redirect()->route('instructor.dashboard');
        }
        
        // Student dashboard
        $enrollments = $user->enrollments()
            ->with(['course.instructor', 'course.category'])
            ->where('payment_status', 'completed')
            ->latest('enrolled_at')
            ->take(6)
            ->get();
        
        $wishlist = $user->wishlist()
            ->with(['instructor', 'category'])
            ->take(6)
            ->get();
        
        $recentActivity = $user->lessonProgress()
            ->with(['lesson.section.course'])
            ->latest()
            ->take(5)
            ->get();
        
        $stats = [
            'enrolled_courses' => $user->enrollments()->where('payment_status', 'completed')->count(),
            'completed_courses' => $user->enrollments()->where('payment_status', 'completed')->whereNotNull('completed_at')->count(),
            'certificates' => $user->enrollments()->where('payment_status', 'completed')->whereNotNull('completed_at')->count(),
            'total_hours' => $user->enrollments()->with('course')->get()->sum('course.duration_hours'),
        ];
        
        return view('dashboard.student', compact('enrollments', 'wishlist', 'recentActivity', 'stats'));
    }
    
    public function courses()
    {
        $user = Auth::user();
        
        $enrollments = $user->enrollments()
            ->with(['course.instructor', 'course.category'])
            ->where('payment_status', 'completed')
            ->latest('enrolled_at')
            ->paginate(12);
        
        return view('student.courses', compact('enrollments'));
    }
    
    public function wishlist()
    {
        $user = Auth::user();
        
        $wishlist = $user->wishlist()
            ->with(['instructor', 'category', 'reviews'])
            ->paginate(12);
        
        return view('student.wishlist', compact('wishlist'));
    }
}