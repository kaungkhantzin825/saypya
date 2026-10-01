<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->get('category');
        $level = $request->get('level');
        $search = $request->get('search');
        $sort = $request->get('sort', 'newest');

        $courses = Course::published()
            ->with(['instructor', 'category', 'reviews'])
            ->withCount('lessons');

        if ($search) {
            $courses->where(function ($query) use ($search) {
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

        return Inertia::render('Courses/Index', [
            'courses' => $courses->paginate(12)->withQueryString(),
            'categories' => Category::active()->ordered()->get(),
            'category' => $category,
            'level' => $level,
            'sort' => $sort,
        ]);
    }

    public function show(Course $course)
    {
        $course->load([
            'instructor',
            'category',
            'sections.lessons',
            'reviews.user',
            'discussions.user',
        ]);

        $course->loadCount('lessons');

        $isEnrolled = Auth::check() && $course->isEnrolledBy(Auth::id());
        $isInWishlist = Auth::check() && $course->isInWishlistOf(Auth::id());
        $userReview = Auth::check()
            ? $course->reviews()->where('user_id', Auth::id())->first()
            : null;

        $enrollment = Auth::check()
            ? Auth::user()->enrollments()->where('course_id', $course->id)->first()
            : null;

        $relatedCourses = Course::published()
            ->where('category_id', $course->category_id)
            ->where('id', '!=', $course->id)
            ->with(['instructor', 'reviews'])
            ->withCount('lessons')
            ->take(4)
            ->get();

        $previewLessons = $course->lessons()
            ->where('is_preview', true)
            ->with('section')
            ->get();

        return Inertia::render('Courses/Show', [
            'course' => $course,
            'isEnrolled' => $isEnrolled,
            'isInWishlist' => $isInWishlist,
            'userReview' => $userReview,
            'relatedCourses' => $relatedCourses,
            'previewLessons' => $previewLessons,
            'enrollment' => $enrollment,
        ]);
    }

    public function enroll(Request $request, Course $course)
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Please login to enroll in this course.');
        }

        $user = Auth::user();

        $existingEnrollment = $user->enrollments()->where('course_id', $course->id)->first();
        if ($existingEnrollment) {
            if ($existingEnrollment->payment_status === 'completed') {
                return redirect()->route('courses.learn', $course)
                    ->with('info', 'You are already enrolled in this course.');
            }

            return redirect()->route('courses.show', $course)
                ->with('info', 'Your enrollment request is already pending admin approval.');
        }

        // Free courses enrol immediately.
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

        // Paid courses submitted from checkout — pending manual confirmation.
        if ($request->has('payment_method')) {
            $user->enrollments()->create([
                'course_id' => $course->id,
                'price_paid' => $course->current_price,
                'payment_status' => 'pending',
                'payment_method' => $request->input('payment_method'),
                'enrolled_at' => now(),
            ]);

            return redirect()->route('courses.show', $course)
                ->with('success', 'Enrollment request submitted! Please wait for admin approval to access the course.');
        }

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

        return Inertia::render('Courses/Checkout', [
            'course' => $course->load(['instructor', 'category'])->loadCount('lessons'),
        ]);
    }

    public function learn(Course $course)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $enrollment = $user->enrollments()
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'You need to enroll in this course first.');
        }

        if ($enrollment->payment_status !== 'completed') {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Your enrollment is pending admin approval. Please wait for approval to access the course.');
        }

        $course->load([
            'sections.lessons' => function ($query) use ($user) {
                $query->with(['progress' => function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                }]);
            },
            'instructor',
            'exams' => function ($query) {
                $query->withCount('questions')->with('questions');
            },
        ]);

        // First incomplete lesson, falling back to the very first lesson.
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

        $enrollment->update(['last_accessed_at' => now()]);

        return Inertia::render('Courses/Learn', [
            'course' => $course,
            'enrollment' => $enrollment,
            'currentLesson' => $currentLesson,
            'title' => $course->title,
            'description' => 'Course player',
        ]);
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

        $course->load([
            'sections.lessons' => function ($query) use ($user) {
                $query->with(['progress' => function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                }]);
            },
            'exams' => function ($query) {
                $query->withCount('questions');
            },
        ]);

        return Inertia::render('Courses/Lesson', [
            'course' => $course,
            'lesson' => $lesson,
            'enrollment' => $enrollment,
            'progress' => $lesson->getProgressFor($user->id),
            'title' => $lesson->title,
            'description' => $course->title,
        ]);
    }

    /**
     * Toggle a course in the current user's wishlist.
     *
     * Responds with a redirect + flash for Inertia visits, and keeps the original
     * JSON shape for the legacy `fetch()` caller in resources/js/app.js.
     */
    public function toggleWishlist(Course $course)
    {
        if (!Auth::check()) {
            if (request()->header('X-Inertia')) {
                return redirect()->route('login');
            }

            return response()->json(['error' => 'Please login first'], 401);
        }

        $user = Auth::user();

        if ($user->wishlist()->where('course_id', $course->id)->exists()) {
            $user->wishlist()->detach($course->id);
            $inWishlist = false;
            $message = 'Removed from wishlist';
        } else {
            $user->wishlist()->attach($course->id);
            $inWishlist = true;
            $message = 'Added to wishlist';
        }

        if (request()->header('X-Inertia')) {
            return back()->with('success', $message);
        }

        return response()->json([
            'success' => true,
            'in_wishlist' => $inWishlist,
            'message' => $message,
        ]);
    }
}
