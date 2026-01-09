<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Category;
use App\Models\Section;
use App\Models\Lesson;
use App\Models\Enrollment;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InstructorController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        $courses = $user->courses()->withCount(['enrollments', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->with('enrollments')
            ->get();
            
        $totalStudents = $courses->sum('enrollments_count');
        $totalRevenue = Enrollment::whereIn('course_id', $courses->pluck('id'))
            ->where('payment_status', 'completed')
            ->sum('price_paid');

        $stats = [
            'total_courses' => $courses->count(),
            'total_students' => $totalStudents,
            'total_revenue' => $totalRevenue,
            'average_rating' => $courses->avg('reviews_avg_rating') ?? 0,
        ];

        $recentReviews = Review::whereIn('course_id', $courses->pluck('id'))
            ->with(['user', 'course'])
            ->latest()
            ->take(5)
            ->get();

        $recentStudents = Enrollment::whereIn('course_id', $courses->pluck('id'))
            ->with(['user', 'course'])
            ->where('payment_status', 'completed')
            ->latest('enrolled_at')
            ->take(10)
            ->get();

        return view('instructor.dashboard', compact('courses', 'stats', 'recentReviews', 'recentStudents'));
    }

    public function courses()
    {
        $user = Auth::user();
        $courses = $user->courses()
            ->withCount(['enrollments', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->with(['category', 'enrollments'])
            ->paginate(12);

        return view('instructor.courses.index', compact('courses'));
    }

    public function createCourse()
    {
        $categories = Category::active()->ordered()->get();
        return view('instructor.courses.create', compact('categories'));
    }

    public function storeCourse(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'level' => 'required|in:beginner,intermediate,advanced',
            'language' => 'required|string|max:50',
            'requirements' => 'nullable|array',
            'what_you_learn' => 'nullable|array',
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'preview_video' => 'nullable|string|url',
        ]);

        $user = Auth::user();
        
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        $course = $user->courses()->create([
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . uniqid(),
            'category_id' => $request->category_id,
            'description' => $request->description,
            'short_description' => $request->short_description,
            'thumbnail' => $thumbnailPath,
            'preview_video' => $request->preview_video,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'level' => $request->level,
            'language' => $request->language,
            'requirements' => $request->requirements ? array_filter($request->requirements) : null,
            'what_you_learn' => $request->what_you_learn ? array_filter($request->what_you_learn) : null,
            'status' => 'draft',
        ]);

        return redirect()->route('instructor.courses.content', $course)
            ->with('success', 'Course created successfully! Now add sections and lessons.');
    }

    public function editCourse(Course $course)
    {
        $this->authorize('update', $course);
        $categories = Category::active()->ordered()->get();
        return view('instructor.courses.edit', compact('course', 'categories'));
    }

    public function updateCourse(Request $request, Course $course)
    {
        $this->authorize('update', $course);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'level' => 'required|in:beginner,intermediate,advanced',
            'language' => 'required|string|max:50',
            'requirements' => 'nullable|array',
            'what_you_learn' => 'nullable|array',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'preview_video' => 'nullable|string|url',
        ]);

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            $course->thumbnail = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        $course->update([
            'title' => $request->title,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'short_description' => $request->short_description,
            'preview_video' => $request->preview_video,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'level' => $request->level,
            'language' => $request->language,
            'requirements' => $request->requirements ? array_filter($request->requirements) : null,
            'what_you_learn' => $request->what_you_learn ? array_filter($request->what_you_learn) : null,
        ]);

        return redirect()->route('instructor.courses')->with('success', 'Course updated successfully!');
    }

    public function destroyCourse(Course $course)
    {
        $this->authorize('delete', $course);
        
        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }
        $course->delete();
        
        return redirect()->route('instructor.courses')->with('success', 'Course deleted successfully!');
    }

    public function courseContent(Course $course)
    {
        $this->authorize('update', $course);
        $course->load(['sections.lessons']);
        return view('instructor.courses.content', compact('course'));
    }

    public function storeSection(Request $request, Course $course)
    {
        $this->authorize('update', $course);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $section = $course->sections()->create([
            'title' => $request->title,
            'description' => $request->description,
            'sort_order' => $course->sections()->count() + 1,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'section' => $section]);
        }
        return redirect()->back()->with('success', 'Section created!');
    }

    public function updateSection(Request $request, Course $course, Section $section)
    {
        $this->authorize('update', $course);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $section->update($request->only(['title', 'description']));

        if ($request->ajax()) {
            return response()->json(['success' => true, 'section' => $section]);
        }
        return redirect()->back()->with('success', 'Section updated!');
    }

    public function destroySection(Course $course, Section $section)
    {
        $this->authorize('update', $course);
        $section->delete();
        
        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }
        return redirect()->back()->with('success', 'Section deleted!');
    }

    public function storeLesson(Request $request, Course $course, Section $section)
    {
        $this->authorize('update', $course);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:video,text,quiz,assignment',
            'video_url' => 'nullable|string',
            'video_duration' => 'nullable|integer|min:0',
            'content' => 'nullable|string',
            'is_preview' => 'boolean',
        ]);

        $lesson = $section->lessons()->create([
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'video_url' => $request->video_url,
            'video_duration' => $request->video_duration,
            'content' => $request->content,
            'is_preview' => $request->boolean('is_preview'),
            'sort_order' => $section->lessons()->count() + 1,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'lesson' => $lesson]);
        }
        return redirect()->back()->with('success', 'Lesson created!');
    }

    public function updateLesson(Request $request, Course $course, Section $section, Lesson $lesson)
    {
        $this->authorize('update', $course);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:video,text,quiz,assignment',
            'video_url' => 'nullable|string',
            'video_duration' => 'nullable|integer|min:0',
            'content' => 'nullable|string',
            'is_preview' => 'boolean',
        ]);

        $lesson->update([
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'video_url' => $request->video_url,
            'video_duration' => $request->video_duration,
            'content' => $request->content,
            'is_preview' => $request->boolean('is_preview'),
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'lesson' => $lesson]);
        }
        return redirect()->back()->with('success', 'Lesson updated!');
    }

    public function destroyLesson(Course $course, Section $section, Lesson $lesson)
    {
        $this->authorize('update', $course);
        $lesson->delete();
        
        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }
        return redirect()->back()->with('success', 'Lesson deleted!');
    }

    // ==================== STUDENTS ====================

    public function students(Request $request)
    {
        $user = Auth::user();
        $courses = $user->courses;
        
        $query = Enrollment::whereIn('course_id', $courses->pluck('id'))
            ->with(['user', 'course'])
            ->where('payment_status', 'completed');

        if ($request->course) {
            $query->where('course_id', $request->course);
        }
        if ($request->search) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $enrollments = $query->latest('enrolled_at')->paginate(20);

        return view('instructor.students', compact('enrollments', 'courses'));
    }

    // ==================== REVIEWS ====================

    public function reviews(Request $request)
    {
        $user = Auth::user();
        $courses = $user->courses;
        
        $query = Review::whereIn('course_id', $courses->pluck('id'))
            ->with(['user', 'course']);

        if ($request->course) {
            $query->where('course_id', $request->course);
        }
        if ($request->rating) {
            $query->where('rating', $request->rating);
        }

        $reviews = $query->latest()->paginate(15);

        $avgRating = Review::whereIn('course_id', $courses->pluck('id'))->avg('rating');
        $totalReviews = Review::whereIn('course_id', $courses->pluck('id'))->count();
        $fiveStarCount = Review::whereIn('course_id', $courses->pluck('id'))->where('rating', 5)->count();
        $lowRatingCount = Review::whereIn('course_id', $courses->pluck('id'))->whereIn('rating', [1, 2])->count();

        return view('instructor.reviews', compact('reviews', 'courses', 'avgRating', 'totalReviews', 'fiveStarCount', 'lowRatingCount'));
    }

    // ==================== EARNINGS ====================

    public function earnings()
    {
        $user = Auth::user();
        $courses = $user->courses;
        $courseIds = $courses->pluck('id');

        $totalEarnings = Enrollment::whereIn('course_id', $courseIds)
            ->where('payment_status', 'completed')
            ->sum('price_paid');

        $thisMonthEarnings = Enrollment::whereIn('course_id', $courseIds)
            ->where('payment_status', 'completed')
            ->whereMonth('enrolled_at', now()->month)
            ->whereYear('enrolled_at', now()->year)
            ->sum('price_paid');

        $totalSales = Enrollment::whereIn('course_id', $courseIds)
            ->where('payment_status', 'completed')
            ->count();

        $avgOrderValue = $totalSales > 0 ? $totalEarnings / $totalSales : 0;

        // Monthly data for chart
        $monthlyData = ['labels' => [], 'data' => []];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyData['labels'][] = $date->format('M Y');
            $monthlyData['data'][] = Enrollment::whereIn('course_id', $courseIds)
                ->where('payment_status', 'completed')
                ->whereYear('enrolled_at', $date->year)
                ->whereMonth('enrolled_at', $date->month)
                ->sum('price_paid');
        }

        // Earnings by course
        $courseEarnings = $courses->map(function($course) {
            $course->earnings = $course->enrollments()->where('payment_status', 'completed')->sum('price_paid');
            return $course;
        })->sortByDesc('earnings')->take(10);

        // Recent transactions
        $recentTransactions = Enrollment::whereIn('course_id', $courseIds)
            ->with(['user', 'course'])
            ->latest('enrolled_at')
            ->take(20)
            ->get();

        return view('instructor.earnings', compact(
            'totalEarnings', 'thisMonthEarnings', 'totalSales', 'avgOrderValue',
            'monthlyData', 'courseEarnings', 'recentTransactions'
        ));
    }

    // ==================== ANALYTICS ====================

    public function analytics()
    {
        $user = Auth::user();
        $courses = $user->courses;
        $courseIds = $courses->pluck('id');

        $totalViews = 0; // Implement view tracking if needed
        $newStudents = Enrollment::whereIn('course_id', $courseIds)
            ->where('payment_status', 'completed')
            ->where('enrolled_at', '>=', now()->subDays(30))
            ->count();

        $avgCompletionRate = Enrollment::whereIn('course_id', $courseIds)
            ->where('payment_status', 'completed')
            ->avg('progress_percentage');

        $avgRating = Review::whereIn('course_id', $courseIds)->avg('rating');

        // Top courses
        $topCourses = $courses->map(function($course) {
            $course->enrollments_count = $course->enrollments()->where('payment_status', 'completed')->count();
            return $course;
        })->sortByDesc('enrollments_count')->take(5);

        // Course stats
        $courseStats = $courses->map(function($course) {
            $enrollments = $course->enrollments()->where('payment_status', 'completed');
            $course->enrollments_count = $enrollments->count();
            $course->completion_rate = $enrollments->where('progress_percentage', 100)->count() / max($enrollments->count(), 1) * 100;
            $course->avg_progress = $enrollments->avg('progress_percentage') ?? 0;
            $course->avg_rating = $course->reviews()->avg('rating') ?? 0;
            $course->reviews_count = $course->reviews()->count();
            $course->revenue = $enrollments->sum('price_paid');
            return $course;
        });

        // Enrollment trend (last 30 days)
        $enrollmentTrend = ['labels' => [], 'data' => []];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $enrollmentTrend['labels'][] = $date->format('M d');
            $enrollmentTrend['data'][] = Enrollment::whereIn('course_id', $courseIds)
                ->where('payment_status', 'completed')
                ->whereDate('enrolled_at', $date)
                ->count();
        }

        return view('instructor.analytics', compact(
            'totalViews', 'newStudents', 'avgCompletionRate', 'avgRating',
            'topCourses', 'courseStats', 'enrollmentTrend'
        ));
    }
}
