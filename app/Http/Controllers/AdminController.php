<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Category;
use App\Models\Enrollment;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return redirect()->route('dashboard');
        }

        $stats = [
            'total_users' => User::count(),
            'total_courses' => Course::count(),
            'total_enrollments' => Enrollment::where('payment_status', 'completed')->count(),
            'total_revenue' => Enrollment::where('payment_status', 'completed')->sum('price_paid'),
            'pending_courses' => Course::where('status', 'draft')->count(),
            'active_instructors' => User::lecturers()->active()->count(),
        ];

        $recentEnrollments = Enrollment::with(['user', 'course'])
            ->where('payment_status', 'completed')
            ->latest()
            ->take(10)
            ->get();

        $topCourses = Course::published()
            ->withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentEnrollments', 'topCourses'));
    }

    public function users()
    {
        $users = User::with(['courses', 'enrollments'])
            ->latest()
            ->paginate(20);

        return view('admin.users', compact('users'));
    }

    public function courses()
    {
        $courses = Course::with(['instructor', 'category', 'enrollments'])
            ->latest()
            ->paginate(20);

        return view('admin.courses', compact('courses'));
    }

    public function showCourse(Course $course)
    {
        $course->load(['instructor', 'category', 'sections.lessons', 'enrollments.user', 'reviews.user']);
        return view('admin.courses.show', compact('course'));
    }

    public function updateCourse(Request $request, Course $course)
    {
        $request->validate([
            'status' => 'required|in:draft,published,archived',
            'is_featured' => 'boolean',
        ]);

        $course->update([
            'status' => $request->status,
            'is_featured' => $request->boolean('is_featured'),
        ]);

        return redirect()->back()->with('success', 'Course updated successfully!');
    }

    public function approveCourse(Course $course)
    {
        $course->update(['status' => 'published']);
        return redirect()->back()->with('success', 'Course approved and published!');
    }

    public function rejectCourse(Course $course)
    {
        $course->update(['status' => 'draft']);
        return redirect()->back()->with('success', 'Course rejected and moved to draft!');
    }

    public function toggleFeature(Course $course)
    {
        $course->update(['is_featured' => !$course->is_featured]);
        $message = $course->is_featured ? 'Course featured!' : 'Course unfeatured!';
        return redirect()->back()->with('success', $message);
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $path = $request->file('image')->store('uploads/images', 'public');
        
        return response()->json([
            'success' => true,
            'url' => asset('storage/' . $path),
            'path' => $path,
        ]);
    }

    public function uploadVideo(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,avi,mov,wmv|max:102400', // 100MB max
        ]);

        $path = $request->file('video')->store('uploads/videos', 'public');
        
        return response()->json([
            'success' => true,
            'url' => asset('storage/' . $path),
            'path' => $path,
        ]);
    }

    public function categories()
    {
        $categories = Category::withCount('courses')
            ->ordered()
            ->get();

        return view('admin.categories', compact('categories'));
    }

    public function reviews()
    {
        $reviews = Review::with(['user', 'course'])
            ->latest()
            ->paginate(20);

        return view('admin.reviews', compact('reviews'));
    }

    public function analytics()
    {
        return view('admin.analytics');
    }

    public function reports()
    {
        return view('admin.reports');
    }

    public function settings()
    {
        return view('admin.settings');
    }
}