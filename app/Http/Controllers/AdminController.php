<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Category;
use App\Models\Section;
use App\Models\Lesson;
use App\Models\Enrollment;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_courses' => Course::count(),
            'total_enrollments' => Enrollment::where('payment_status', 'completed')->count(),
            'total_revenue' => Enrollment::where('payment_status', 'completed')->sum('price_paid'),
            'pending_courses' => Course::where('status', 'draft')->count(),
            'active_instructors' => User::lecturers()->active()->count(),
            'total_categories' => Category::count(),
            'total_reviews' => Review::count(),
        ];

        $recentEnrollments = Enrollment::with(['user', 'course'])
            ->where('payment_status', 'completed')
            ->latest('enrolled_at')
            ->take(10)
            ->get();

        $topCourses = Course::published()
            ->withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentEnrollments', 'topCourses'));
    }

    // ==================== USER MANAGEMENT ====================
    
    public function usersIndex(Request $request)
    {
        $query = User::with(['courses', 'enrollments']);

        if ($request->role) {
            $query->where('role', $request->role);
        }
        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $users = $query->latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function usersCreate()
    {
        return view('admin.users.create');
    }

    public function usersStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:student,lecturer,admin',
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->only(['name', 'email', 'role', 'phone', 'country', 'bio']);
        $data['password'] = Hash::make($request->password);
        $data['is_active'] = $request->boolean('is_active');
        $data['email_verified_at'] = now();

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        User::create($data);
        return redirect()->route('admin.users.index')->with('success', 'User created successfully!');
    }

    public function usersShow(User $user)
    {
        $user->load(['courses', 'enrollments.course', 'reviews.course']);
        return view('admin.users.show', compact('user'));
    }

    public function usersEdit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function usersUpdate(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
            'role' => 'required|in:student,lecturer,admin',
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->only(['name', 'email', 'role', 'phone', 'country', 'bio']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);
        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }

    public function usersToggle(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "User {$status} successfully!");
    }

    public function usersDestroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete yourself!');
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully!');
    }

    // ==================== CATEGORY MANAGEMENT ====================

    public function categoriesIndex()
    {
        $categories = Category::withCount('courses')->ordered()->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function categoriesCreate()
    {
        return view('admin.categories.create');
    }

    public function categoriesStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->only(['name', 'description', 'icon', 'sort_order']);
        $data['slug'] = Str::slug($request->name);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data);
        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully!');
    }

    public function categoriesEdit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function categoriesUpdate(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->only(['name', 'description', 'icon', 'sort_order']);
        $data['slug'] = Str::slug($request->name);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);
        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully!');
    }

    public function categoriesDestroy(Category $category)
    {
        if ($category->courses()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete category with courses!');
        }
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully!');
    }

    // ==================== COURSE MANAGEMENT ====================

    public function coursesIndex(Request $request)
    {
        $query = Course::with(['instructor', 'category'])->withCount('enrollments');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->category) {
            $query->where('category_id', $request->category);
        }
        if ($request->featured !== null && $request->featured !== '') {
            $query->where('is_featured', $request->featured);
        }
        if ($request->search) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        $courses = $query->latest()->paginate(20);
        $categories = Category::active()->ordered()->get();

        return view('admin.courses.index', compact('courses', 'categories'));
    }

    public function coursesCreate()
    {
        $categories = Category::active()->ordered()->get();
        $instructors = User::lecturers()->active()->get();
        return view('admin.courses.create', compact('categories', 'instructors'));
    }

    public function coursesStore(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'instructor_id' => 'required|exists:users,id',
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
            'status' => 'required|in:draft,published,archived',
        ]);

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        $course = Course::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . uniqid(),
            'instructor_id' => $request->instructor_id,
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
            'status' => $request->status,
            'is_featured' => $request->boolean('is_featured'),
        ]);

        return redirect()->route('admin.courses.content', $course)
            ->with('success', 'Course created successfully! Now add sections and lessons.');
    }

    public function coursesEdit(Course $course)
    {
        $categories = Category::active()->ordered()->get();
        $instructors = User::lecturers()->active()->get();
        return view('admin.courses.edit', compact('course', 'categories', 'instructors'));
    }

    public function coursesUpdate(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'instructor_id' => 'required|exists:users,id',
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
            'status' => 'required|in:draft,published,archived',
        ]);

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            $course->thumbnail = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        $course->update([
            'title' => $request->title,
            'instructor_id' => $request->instructor_id,
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
            'status' => $request->status,
            'is_featured' => $request->boolean('is_featured'),
        ]);

        return redirect()->route('admin.courses.index')->with('success', 'Course updated successfully!');
    }

    public function coursesContent(Course $course)
    {
        $course->load(['sections.lessons']);
        return view('admin.courses.content', compact('course'));
    }

    public function coursesStoreSection(Request $request, Course $course)
    {
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

    public function coursesUpdateSection(Request $request, Course $course, Section $section)
    {
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

    public function coursesDestroySection(Course $course, Section $section)
    {
        $section->delete();
        
        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }
        return redirect()->back()->with('success', 'Section deleted!');
    }

    public function coursesStoreLesson(Request $request, Course $course, Section $section)
    {
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

    public function coursesUpdateLesson(Request $request, Course $course, Section $section, Lesson $lesson)
    {
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

    public function coursesDestroyLesson(Course $course, Section $section, Lesson $lesson)
    {
        $lesson->delete();
        
        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }
        return redirect()->back()->with('success', 'Lesson deleted!');
    }

    public function coursesShow(Course $course)
    {
        $course->load(['instructor', 'category', 'sections.lessons', 'enrollments.user', 'reviews.user']);
        return view('admin.courses.show', compact('course'));
    }

    public function coursesApprove(Course $course)
    {
        $course->update(['status' => 'published']);
        return redirect()->back()->with('success', 'Course approved and published!');
    }

    public function coursesArchive(Course $course)
    {
        $course->update(['status' => 'archived']);
        return redirect()->back()->with('success', 'Course archived!');
    }

    public function coursesFeature(Course $course)
    {
        $course->update(['is_featured' => !$course->is_featured]);
        $message = $course->is_featured ? 'Course featured!' : 'Course unfeatured!';
        return redirect()->back()->with('success', $message);
    }

    public function coursesDestroy(Course $course)
    {
        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }
        $course->delete();
        return redirect()->route('admin.courses.index')->with('success', 'Course deleted successfully!');
    }

    // ==================== ENROLLMENT MANAGEMENT ====================

    public function enrollmentsIndex(Request $request)
    {
        $query = Enrollment::with(['user', 'course']);

        if ($request->status) {
            $query->where('payment_status', $request->status);
        }
        if ($request->from_date) {
            $query->whereDate('enrolled_at', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('enrolled_at', '<=', $request->to_date);
        }

        $enrollments = $query->latest('enrolled_at')->paginate(20);

        $stats = [
            'completed' => Enrollment::where('payment_status', 'completed')->count(),
            'pending' => Enrollment::where('payment_status', 'pending')->count(),
            'failed' => Enrollment::where('payment_status', 'failed')->count(),
            'refunded' => Enrollment::where('payment_status', 'refunded')->count(),
            'revenue' => Enrollment::where('payment_status', 'completed')->sum('price_paid'),
        ];

        return view('admin.enrollments.index', compact('enrollments', 'stats'));
    }

    public function enrollmentsRefund(Enrollment $enrollment)
    {
        $enrollment->update(['payment_status' => 'refunded']);
        return redirect()->back()->with('success', 'Enrollment refunded successfully!');
    }

    // ==================== REVIEW MANAGEMENT ====================

    public function reviewsIndex(Request $request)
    {
        $query = Review::with(['user', 'course']);

        if ($request->rating) {
            $query->where('rating', $request->rating);
        }
        if ($request->approved !== null && $request->approved !== '') {
            $query->where('is_approved', $request->approved);
        }
        if ($request->search) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            })->orWhereHas('course', function($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%");
            });
        }

        $reviews = $query->latest()->paginate(20);
        return view('admin.reviews.index', compact('reviews'));
    }

    public function reviewsApprove(Review $review)
    {
        $review->update(['is_approved' => true]);
        return redirect()->back()->with('success', 'Review approved!');
    }

    public function reviewsDestroy(Review $review)
    {
        $review->delete();
        return redirect()->back()->with('success', 'Review deleted!');
    }

    // ==================== REPORTS ====================

    public function reports()
    {
        $stats = [
            'total_users' => User::count(),
            'total_courses' => Course::count(),
            'total_enrollments' => Enrollment::where('payment_status', 'completed')->count(),
            'total_revenue' => Enrollment::where('payment_status', 'completed')->sum('price_paid'),
        ];

        $userDistribution = [
            'students' => User::students()->count(),
            'lecturers' => User::lecturers()->count(),
            'admins' => User::admins()->count(),
        ];

        // Monthly revenue for last 12 months
        $monthlyRevenue = ['labels' => [], 'data' => []];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyRevenue['labels'][] = $date->format('M Y');
            $monthlyRevenue['data'][] = Enrollment::where('payment_status', 'completed')
                ->whereYear('enrolled_at', $date->year)
                ->whereMonth('enrolled_at', $date->month)
                ->sum('price_paid');
        }

        $topCourses = Course::withCount('enrollments')
            ->with('instructor')
            ->orderBy('enrollments_count', 'desc')
            ->take(10)
            ->get()
            ->map(function($course) {
                $course->revenue = $course->enrollments()->where('payment_status', 'completed')->sum('price_paid');
                return $course;
            });

        $topInstructors = User::lecturers()
            ->withCount('courses')
            ->get()
            ->map(function($instructor) {
                $instructor->students_count = Enrollment::whereIn('course_id', $instructor->courses->pluck('id'))
                    ->where('payment_status', 'completed')->count();
                $instructor->revenue = Enrollment::whereIn('course_id', $instructor->courses->pluck('id'))
                    ->where('payment_status', 'completed')->sum('price_paid');
                return $instructor;
            })
            ->sortByDesc('revenue')
            ->take(10);

        $categoryStats = Category::withCount('courses')->get()->map(function($category) {
            $courseIds = $category->courses->pluck('id');
            $category->students_count = Enrollment::whereIn('course_id', $courseIds)->where('payment_status', 'completed')->count();
            $category->revenue = Enrollment::whereIn('course_id', $courseIds)->where('payment_status', 'completed')->sum('price_paid');
            $category->avg_rating = Review::whereIn('course_id', $courseIds)->avg('rating');
            return $category;
        });

        return view('admin.reports', compact('stats', 'userDistribution', 'monthlyRevenue', 'topCourses', 'topInstructors', 'categoryStats'));
    }

    // ==================== SETTINGS ====================

    public function settings()
    {
        $settings = []; // Load from database or config
        return view('admin.settings', compact('settings'));
    }

    public function settingsUpdate(Request $request)
    {
        // Save settings to database or config
        return redirect()->back()->with('success', 'Settings updated successfully!');
    }

    public function cacheClear()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        return redirect()->back()->with('success', 'Cache cleared successfully!');
    }
}
