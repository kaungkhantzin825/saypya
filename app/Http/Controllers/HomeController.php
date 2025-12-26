<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use App\Models\Review;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredCourses = Course::published()
            ->featured()
            ->with(['instructor', 'category', 'reviews'])
            ->take(8)
            ->get();

        $popularCourses = Course::published()
            ->withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->with(['instructor', 'category', 'reviews'])
            ->take(8)
            ->get();

        $categories = Category::active()
            ->withCount('courses')
            ->ordered()
            ->take(8)
            ->get();

        $topInstructors = User::lecturers()
            ->active()
            ->withCount(['courses' => function ($query) {
                $query->published();
            }])
            ->having('courses_count', '>', 0)
            ->orderBy('courses_count', 'desc')
            ->take(6)
            ->get();

        $stats = [
            'total_courses' => Course::published()->count(),
            'total_students' => User::students()->active()->count(),
            'total_instructors' => User::lecturers()->active()->count(),
            'total_enrollments' => \App\Models\Enrollment::completed()->count(),
        ];

        return view('home', compact(
            'featuredCourses',
            'popularCourses',
            'categories',
            'topInstructors',
            'stats'
        ));
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        $category = $request->get('category');
        $level = $request->get('level');
        $sort = $request->get('sort', 'relevance');

        $courses = Course::published()
            ->with(['instructor', 'category', 'reviews']);

        if ($query) {
            $courses->search($query);
        }

        if ($category) {
            $courses->byCategory($category);
        }

        if ($level) {
            $courses->byLevel($level);
        }

        switch ($sort) {
            case 'price_low':
                $courses->orderBy('price');
                break;
            case 'price_high':
                $courses->orderByDesc('price');
                break;
            case 'newest':
                $courses->orderByDesc('created_at');
                break;
            case 'rating':
                $courses->withAvg('reviews', 'rating')
                       ->orderByDesc('reviews_avg_rating');
                break;
            case 'popular':
                $courses->withCount('enrollments')
                       ->orderByDesc('enrollments_count');
                break;
            default:
                $courses->orderByDesc('is_featured')
                       ->orderByDesc('created_at');
        }

        $courses = $courses->paginate(12);
        $categories = Category::active()->ordered()->get();

        return view('courses.search', compact(
            'courses',
            'categories',
            'query',
            'category',
            'level',
            'sort'
        ));
    }

    public function instructorProfile(User $user)
    {
        // Ensure the user is an instructor
        if (!$user->isLecturer()) {
            abort(404);
        }

        $instructor = $user;
        
        // Get instructor's courses
        $courses = $instructor->courses()
            ->published()
            ->with(['category', 'reviews', 'enrollments'])
            ->paginate(12);

        // Calculate instructor stats
        $stats = [
            'total_courses' => $instructor->courses()->published()->count(),
            'total_students' => $instructor->courses()
                ->join('enrollments', 'courses.id', '=', 'enrollments.course_id')
                ->where('enrollments.payment_status', 'completed')
                ->distinct('enrollments.user_id')
                ->count('enrollments.user_id'),
            'average_rating' => $instructor->courses()
                ->published()
                ->withAvg('reviews', 'rating')
                ->get()
                ->avg('reviews_avg_rating') ?? 0,
            'total_reviews' => $instructor->courses()
                ->join('reviews', 'courses.id', '=', 'reviews.course_id')
                ->count(),
        ];

        // Get recent reviews
        $recentReviews = Review::whereIn('course_id', $instructor->courses()->pluck('id'))
            ->with(['user', 'course'])
            ->latest()
            ->take(5)
            ->get();

        return view('instructors.profile', compact('instructor', 'courses', 'stats', 'recentReviews'));
    }
}