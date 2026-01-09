<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Category;
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
