<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\HeroSlide;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $heroSlides = HeroSlide::active()->ordered()->get();

        $featuredCourses = Course::published()
            ->featured()
            ->with(['instructor', 'category', 'reviews'])
            ->withCount('lessons')
            ->take(8)
            ->get();

        $popularCourses = Course::published()
            ->with(['instructor', 'category', 'reviews'])
            ->withCount(['enrollments', 'lessons'])
            ->orderByDesc('enrollments_count')
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
            ->orderByDesc('courses_count')
            ->take(6)
            ->get()
            ->map(function (User $instructor) {
                $publishedCourseIds = $instructor->courses()->published()->select('id');

                return [
                    'id' => $instructor->id,
                    'name' => $instructor->name,
                    'avatar_url' => $instructor->avatar_url,
                    'bio' => $instructor->bio,
                    'courses_count' => $instructor->courses_count,
                    'average_rating' => round((float) Review::whereIn('course_id', $publishedCourseIds)->avg('rating'), 1),
                ];
            });

        $stats = [
            'total_courses' => Course::published()->count(),
            'total_students' => User::students()->active()->count(),
            'total_instructors' => User::lecturers()->active()->count(),
            'total_enrollments' => Enrollment::completed()->count(),
        ];

        return Inertia::render('Home', [
            'heroSlides' => $heroSlides,
            'featuredCourses' => $featuredCourses,
            'popularCourses' => $popularCourses,
            'categories' => $categories,
            'topInstructors' => $topInstructors,
            'stats' => $stats,
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        $category = $request->get('category');
        $level = $request->get('level');
        $sort = $request->get('sort', 'relevance');

        $courses = Course::published()
            ->with(['instructor', 'category', 'reviews'])
            ->withCount('lessons');

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

        return Inertia::render('Search', [
            'courses' => $courses->paginate(12)->withQueryString(),
            'categories' => Category::active()->ordered()->get(),
            'query' => $query,
            'category' => $category,
            'level' => $level,
            'sort' => $sort,
        ]);
    }

    public function instructorProfile(User $user)
    {
        // Only lecturers have a public profile.
        if (!$user->isLecturer()) {
            abort(404);
        }

        $instructor = $user;

        $courses = $instructor->courses()
            ->published()
            ->with(['category', 'reviews'])
            ->withCount('lessons')
            ->paginate(12);

        $stats = [
            'total_courses' => $instructor->courses()->published()->count(),
            'total_students' => $instructor->courses()
                ->join('enrollments', 'courses.id', '=', 'enrollments.course_id')
                ->where('enrollments.payment_status', 'completed')
                ->distinct('enrollments.user_id')
                ->count('enrollments.user_id'),
            'average_rating' => round((float) ($instructor->courses()
                ->published()
                ->withAvg('reviews', 'rating')
                ->get()
                ->avg('reviews_avg_rating') ?? 0), 1),
            'total_reviews' => $instructor->courses()
                ->join('reviews', 'courses.id', '=', 'reviews.course_id')
                ->count(),
        ];

        $recentReviews = Review::whereIn('course_id', $instructor->courses()->pluck('id'))
            ->with(['user', 'course'])
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Instructors/Profile', [
            'instructor' => [
                'id' => $instructor->id,
                'name' => $instructor->name,
                'avatar_url' => $instructor->avatar_url,
                'bio' => $instructor->bio,
                'created_at' => $instructor->created_at,
            ],
            'courses' => $courses,
            'stats' => $stats,
            'recentReviews' => $recentReviews,
        ]);
    }
}
