<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        // Admins and lecturers have their own (still Blade-based) panels.
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isLecturer()) {
            return redirect()->route('instructor.dashboard');
        }

        $enrollments = $user->enrollments()
            ->with(['course.instructor', 'course.category'])
            ->where('payment_status', 'completed')
            ->latest('enrolled_at')
            ->take(6)
            ->get();

        $wishlist = $user->wishlist()
            ->with(['instructor', 'category', 'reviews'])
            ->withCount('lessons')
            ->take(6)
            ->get();

        $recentActivity = $user->lessonProgress()
            ->with(['lesson.section.course'])
            ->latest()
            ->take(5)
            ->get();

        $completed = $user->enrollments()
            ->where('payment_status', 'completed')
            ->whereNotNull('completed_at')
            ->count();

        $stats = [
            'enrolled_courses' => $user->enrollments()->where('payment_status', 'completed')->count(),
            'completed_courses' => $completed,
            'certificates' => $completed,
            'total_hours' => $user->enrollments()->with('course')->get()->sum('course.duration_hours'),
        ];

        return Inertia::render('Dashboard', [
            'enrollments' => $enrollments,
            'wishlist' => $wishlist,
            'recentActivity' => $recentActivity,
            'stats' => $stats,
            'title' => 'Dashboard',
            'description' => 'Welcome back, ' . $user->name . '.',
        ]);
    }

    public function courses()
    {
        $user = Auth::user();

        $enrollments = $user->enrollments()
            ->with(['course.instructor', 'course.category'])
            ->where('payment_status', 'completed')
            ->latest('enrolled_at')
            ->paginate(12);

        return Inertia::render('Student/Courses', [
            'enrollments' => $enrollments,
            'title' => 'My courses',
            'description' => 'Pick up where you left off.',
        ]);
    }

    public function wishlist()
    {
        $user = Auth::user();

        $wishlist = $user->wishlist()
            ->with(['instructor', 'category', 'reviews'])
            ->withCount('lessons')
            ->paginate(12);

        return Inertia::render('Student/Wishlist', [
            'wishlist' => $wishlist,
            'title' => 'Wishlist',
            'description' => 'Courses you have saved for later.',
        ]);
    }

    /**
     * Courses the learner has completed — certificates are issued for these.
     *
     * NOTE: `my.certificates` previously pointed at a method that did not exist,
     * so the route 500'd. It is implemented here.
     */
    public function certificates()
    {
        $user = Auth::user();

        $certificates = $user->enrollments()
            ->with(['course.instructor'])
            ->where('payment_status', 'completed')
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->get()
            ->map(function ($enrollment) {
                $course = $enrollment->course;

                return [
                    'id' => $enrollment->id,
                    'course' => [
                        'id' => $course?->id,
                        'title' => $course?->title,
                        'slug' => $course?->slug,
                        'instructor' => $course?->instructor
                            ? ['name' => $course->instructor->name]
                            : null,
                    ],
                    'completed_at' => $enrollment->completed_at,
                    'progress_percentage' => $enrollment->progress_percentage,
                ];
            });

        return Inertia::render('Student/Certificates', [
            'certificates' => $certificates,
            'title' => 'Certificates',
            'description' => 'Awarded when you finish a course and pass its exam.',
        ]);
    }

    /**
     * Per-course completion breakdown.
     *
     * NOTE: `my.progress` previously pointed at a method that did not exist.
     */
    public function progress()
    {
        $user = Auth::user();

        $enrollments = $user->enrollments()
            ->with('course')
            ->where('payment_status', 'completed')
            ->latest('enrolled_at')
            ->get()
            ->map(function ($enrollment) {
                $course = $enrollment->course;

                return [
                    'id' => $enrollment->id,
                    'course' => [
                        'id' => $course?->id,
                        'title' => $course?->title,
                        'slug' => $course?->slug,
                        'total_lessons' => $course?->total_lessons ?? 0,
                    ],
                    'progress_percentage' => $enrollment->progress_percentage,
                    'completed_at' => $enrollment->completed_at,
                    'enrolled_at' => $enrollment->enrolled_at,
                ];
            });

        return Inertia::render('Student/Progress', [
            'enrollments' => $enrollments,
            'title' => 'Progress',
            'description' => 'How far you have got in each course.',
        ]);
    }
}
