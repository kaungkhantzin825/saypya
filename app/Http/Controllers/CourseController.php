<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Category;
use App\Models\Review;
use App\Models\Discussion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->get('category');
        $level = $request->get('level');
        $search = $request->get('search');
        $sort = $request->get('sort', 'newest');

        $courses = Course::published()
            ->with(['instructor', 'category', 'reviews']);

        if ($search) {
            $courses->where(function($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('short_description', 'like', "%{$search}%");
            });
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
            case 'oldest':
                $courses->orderBy('created_at');
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
                $courses->orderByDesc('created_at');
        }

        $courses = $courses->paginate(12);
        $categories = Category::active()->ordered()->get();

        return view('courses.index', compact(
            'courses',
            'categories',
            'category',
            'level',
            'sort'
        ));
    }

    public function show(Course $course)
    {
        $course->load([
            'instructor',
            'category',
            'sections.lessons',
            'reviews.user',
            'discussions.user'
        ]);

        $isEnrolled = Auth::check() && $course->isEnrolledBy(Auth::id());
        $isInWishlist = Auth::check() && $course->isInWishlistOf(Auth::id());
        $userReview = Auth::check() ? $course->reviews()->where('user_id', Auth::id())->first() : null;

        $relatedCourses = Course::published()
            ->where('category_id', $course->category_id)
            ->where('id', '!=', $course->id)
            ->with(['instructor', 'reviews'])
            ->take(4)
            ->get();

        $previewLessons = $course->lessons()
            ->where('is_preview', true)
            ->with('section')
            ->get();

        return view('courses.show', compact(
            'course',
            'isEnrolled',
            'isInWishlist',
            'userReview',
            'relatedCourses',
            'previewLessons'
        ));
    }

    public function enroll(Course $course)
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('message', 'Please login to enroll in this course.');
        }

        $user = Auth::user();

        if ($course->isEnrolledBy($user->id)) {
            return redirect()->back()
                ->with('error', 'You are already enrolled in this course.');
        }

        // For free courses, enroll directly
        if ($course->isFree()) {
            $user->enrollments()->create([
                'course_id' => $course->id,
                'price_paid' => 0,
                'payment_status' => 'completed',
                'enrolled_at' => now(),
            ]);

            return redirect()->route('courses.learn', $course)
                ->with('success', 'Successfully enrolled in the course!');
        }

        // For paid courses, redirect to payment
        return redirect()->route('courses.checkout', $course);
    }

    public function checkout(Course $course)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($course->isEnrolledBy($user->id)) {
            return redirect()->route('courses.learn', $course);
        }

        if ($course->isFree()) {
            return redirect()->route('courses.enroll', $course);
        }

        return view('courses.checkout', compact('course'));
    }

    public function learn(Course $course)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $enrollment = $user->enrollments()
            ->where('course_id', $course->id)
            ->where('payment_status', 'completed')
            ->first();

        if (!$enrollment) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'You need to enroll in this course first.');
        }

        $course->load([
            'sections.lessons' => function ($query) use ($user) {
                $query->with(['progress' => function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                }]);
            },
            'instructor'
        ]);

        // Get current lesson (first incomplete or first lesson)
        $currentLesson = null;
        foreach ($course->sections as $section) {
            foreach ($section->lessons as $lesson) {
                if (!$lesson->isCompletedBy($user->id)) {
                    $currentLesson = $lesson;
                    break 2;
                }
            }
        }

        if (!$currentLesson) {
            $currentLesson = $course->sections->first()?->lessons->first();
        }

        // Update last accessed
        $enrollment->update(['last_accessed_at' => now()]);

        return view('courses.learn', compact('course', 'enrollment', 'currentLesson'));
    }

    public function lesson(Course $course, $lessonId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $enrollment = $user->enrollments()
            ->where('course_id', $course->id)
            ->where('payment_status', 'completed')
            ->first();

        if (!$enrollment) {
            return redirect()->route('courses.show', $course);
        }

        $lesson = $course->lessons()->findOrFail($lessonId);
        $lesson->load(['section', 'discussions.user.replies']);

        $course->load([
            'sections.lessons' => function ($query) use ($user) {
                $query->with(['progress' => function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                }]);
            }
        ]);

        $progress = $lesson->getProgressFor($user->id);

        return view('courses.lesson', compact('course', 'lesson', 'enrollment', 'progress'));
    }

    public function toggleWishlist(Course $course)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Please login first'], 401);
        }

        $user = Auth::user();
        $wishlist = $user->wishlist()->where('course_id', $course->id)->first();

        if ($wishlist) {
            $wishlist->delete();
            $inWishlist = false;
        } else {
            $user->wishlist()->attach($course->id);
            $inWishlist = true;
        }

        return response()->json([
            'success' => true,
            'in_wishlist' => $inWishlist,
            'message' => $inWishlist ? 'Added to wishlist' : 'Removed from wishlist'
        ]);
    }
}