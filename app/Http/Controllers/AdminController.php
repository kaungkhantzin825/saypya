<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Category;
use App\Models\Section;
use App\Models\Lesson;
use App\Models\Enrollment;
use App\Models\Review;
use App\Models\HeroSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;

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

        // `enrollments` is eager-loaded because Course::getTotalStudentsAttribute
        // falls back to a COUNT query per row otherwise (N+1); `enrollments_count`
        // is only used to rank the list.
        $topCourses = Course::published()
            ->with(['instructor', 'enrollments:id,course_id,payment_status'])
            ->withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->take(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentEnrollments' => $recentEnrollments,
            'topCourses' => $topCourses,
            'pendingUsers' => User::where('status', 'pending')->count(),
            'title' => 'Dashboard',
        ]);
    }

    // ==================== USER MANAGEMENT ====================
    
    public function usersIndex(Request $request)
    {
        $query = User::with(['courses', 'enrollments']);

        if ($request->role) {
            $query->where('role', $request->role);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        // Whitelist the sort column — it is interpolated into orderBy().
        $sortable = ['id', 'name', 'email', 'role', 'status', 'created_at'];
        $sort = in_array($request->sort, $sortable, true) ? $request->sort : 'created_at';
        $direction = $request->direction === 'asc' ? 'asc' : 'desc';

        $users = $query->orderBy($sort, $direction)->paginate(20)->withQueryString();
        $registrationEnabled = \App\Models\Setting::get('registration_enabled', '1') === '1';

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'registrationEnabled' => $registrationEnabled,
            // Echo the active filters back so the form stays populated.
            'filters' => [
                'role' => $request->role,
                'status' => $request->status,
                'search' => $request->search,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'title' => 'Users',
            'description' => 'Manage students, lecturers and administrators.',
        ]);
    }

    public function usersCreate()
    {
        return Inertia::render('Admin/Users/Form', [
            'user' => null,
            'title' => 'Add user',
            'description' => 'Create a new account. It is activated immediately.',
        ]);
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
        $data['status'] = 'active';
        $data['is_active'] = true;
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
        return Inertia::render('Admin/Users/Form', [
            'user' => $user->only(['id', 'name', 'email', 'role', 'phone', 'country', 'bio', 'status', 'is_active', 'avatar_url']),
            'title' => 'Edit user',
            'description' => 'Update ' . $user->name . '’s details. Leave the password blank to keep it unchanged.',
        ]);
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

    public function usersToggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot change your own status.');
        }
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus, 'is_active' => $newStatus === 'active']);
        $label = $newStatus === 'active' ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "User account {$label} successfully!");
    }

    public function usersDestroy(User $user)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Only super admins can delete users.');
        }
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete yourself!');
        }
        // User uses SoftDeletes: this sets deleted_at and keeps the row (and its
        // enrollments/history) recoverable. It is NOT a hard delete.
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully!');
    }

    public function usersApprove(User $user)
    {
        $user->update(['status' => 'active']);
        return redirect()->back()->with('success', 'User approved successfully! They can now login.');
    }

    public function usersReject(User $user)
    {
        $user->update(['status' => 'inactive']);
        return redirect()->back()->with('success', 'User rejected.');
    }

    // ==================== CATEGORY MANAGEMENT ====================

    public function categoriesIndex()
    {
        // The filtered count keeps Category::getCoursesCountAttribute() from
        // issuing a query per row once `courses_count` is appended.
        $categories = Category::withCount(['courses as courses_count' => fn ($query) => $query->published()])
            ->ordered()
            ->get();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            'title' => 'Categories Management',
            'description' => 'Organise courses into the categories shown across the site.',
        ]);
    }

    public function categoriesCreate()
    {
        return Inertia::render('Admin/Categories/Form', [
            'category' => null,
            'title' => 'Add category',
            'description' => 'Create a category to group courses under.',
        ]);
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
        return Inertia::render('Admin/Categories/Form', [
            'category' => $category->only([
                'id', 'name', 'description', 'icon', 'sort_order', 'is_active', 'image_url',
            ]),
            'title' => 'Edit category',
            'description' => 'Update ' . $category->name . '.',
        ]);
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
            if ($category->hasLocalImage()) {
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
        if ($category->hasLocalImage()) {
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

        // Whitelist the sort column — it is interpolated into orderBy().
        $sortable = ['id', 'title', 'price', 'status', 'is_featured', 'enrollments_count', 'created_at'];
        $sort = in_array($request->sort, $sortable, true) ? $request->sort : 'created_at';
        $direction = $request->direction === 'asc' ? 'asc' : 'desc';

        $courses = $query->orderBy($sort, $direction)->paginate(20)->withQueryString();
        $categories = Category::active()->ordered()->get();

        return Inertia::render('Admin/Courses/Index', [
            'courses' => $courses,
            'categories' => $categories,
            // Echo the active filters back so the form stays populated.
            'filters' => [
                'status' => $request->status,
                'category' => $request->category,
                'featured' => $request->featured,
                'search' => $request->search,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'title' => 'Courses Management',
            'description' => 'Review, publish and feature courses across the academy.',
        ]);
    }

    public function coursesCreate()
    {
        return Inertia::render('Admin/Courses/Form', [
            'course' => null,
            'categories' => Category::active()->ordered()->get(),
            'instructors' => User::lecturers()->active()->get(),
            'title' => 'Create course',
            'description' => 'Add a new course to the catalogue, then build its curriculum.',
        ]);
    }

    public function coursesStore(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
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
        // Eager-load the two relations the form's <Select>s need to preselect.
        $course->load(['category', 'instructor']);

        return Inertia::render('Admin/Courses/Form', [
            'course' => $course,
            'categories' => Category::active()->ordered()->get(),
            'instructors' => User::lecturers()->active()->get(),
            'title' => 'Edit course',
            'description' => $course->title,
        ]);
    }

    public function coursesUpdate(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
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
        $course->load(['sections.lessons', 'instructor', 'category']);

        return Inertia::render('Admin/Courses/Content', [
            'course' => $course,
            'title' => 'Course content',
            'description' => $course->title,
        ]);
    }

    public function coursesStoreSection(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $section = $course->sections()->create([
            'title' => $request->title,
            'description' => $request->description,
            'sort_order' => $course->sections()->count() + 1,
        ]);

        if ($request->ajax() && ! $request->header('X-Inertia')) {
            return response()->json(['success' => true, 'section' => $section]);
        }
        return redirect()->back()->with('success', 'Section created!');
    }

    public function coursesUpdateSection(Request $request, Course $course, Section $section)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $section->update($request->only(['title', 'description']));

        if ($request->ajax() && ! $request->header('X-Inertia')) {
            return response()->json(['success' => true, 'section' => $section]);
        }
        return redirect()->back()->with('success', 'Section updated!');
    }

    public function coursesDestroySection(Course $course, Section $section)
    {
        $section->delete();
        
        if (request()->ajax() && ! request()->header('X-Inertia')) {
            return response()->json(['success' => true]);
        }
        return redirect()->back()->with('success', 'Section deleted!');
    }

    public function coursesStoreLesson(Request $request, Course $course, Section $section)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
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

        if ($request->ajax() && ! $request->header('X-Inertia')) {
            return response()->json(['success' => true, 'lesson' => $lesson]);
        }
        return redirect()->back()->with('success', 'Lesson created!');
    }

    public function coursesUpdateLesson(Request $request, Course $course, Section $section, Lesson $lesson)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
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

        if ($request->ajax() && ! $request->header('X-Inertia')) {
            return response()->json(['success' => true, 'lesson' => $lesson]);
        }
        return redirect()->back()->with('success', 'Lesson updated!');
    }

    public function coursesDestroyLesson(Course $course, Section $section, Lesson $lesson)
    {
        $lesson->delete();
        
        if (request()->ajax() && ! request()->header('X-Inertia')) {
            return response()->json(['success' => true]);
        }
        return redirect()->back()->with('success', 'Lesson deleted!');
    }

    public function coursesShow(Course $course)
    {
        $course->load(['instructor', 'category', 'sections.lessons', 'enrollments.user', 'reviews.user']);

        return Inertia::render('Admin/Courses/Show', [
            'course' => $course,
            'title' => $course->title ?: 'Course details',
            'description' => 'Overview, curriculum and reviews.',
        ]);
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

        // Whitelist the sort column — it is interpolated into orderBy().
        $sortable = ['id', 'price_paid', 'payment_status', 'progress_percentage', 'enrolled_at'];
        $sort = in_array($request->sort, $sortable, true) ? $request->sort : 'enrolled_at';
        $direction = $request->direction === 'asc' ? 'asc' : 'desc';

        $enrollments = $query->orderBy($sort, $direction)->paginate(20)->withQueryString();

        // One grouped query instead of five COUNT/SUM round-trips.
        $totals = Enrollment::query()
            ->selectRaw('payment_status, COUNT(*) as aggregate, SUM(price_paid) as revenue')
            ->groupBy('payment_status')
            ->get()
            ->keyBy('payment_status');

        $stats = [
            'completed' => (int) ($totals['completed']->aggregate ?? 0),
            'pending' => (int) ($totals['pending']->aggregate ?? 0),
            'failed' => (int) ($totals['failed']->aggregate ?? 0),
            'refunded' => (int) ($totals['refunded']->aggregate ?? 0),
            'revenue' => (float) ($totals['completed']->revenue ?? 0),
        ];

        return Inertia::render('Admin/Enrollments/Index', [
            'enrollments' => $enrollments,
            'stats' => $stats,
            // Echo the active filters back so the form stays populated.
            'filters' => [
                'status' => $request->status,
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'title' => 'Enrollments',
            'description' => 'Approve payments, issue refunds and track student progress.',
        ]);
    }

    public function enrollmentsRefund(Enrollment $enrollment)
    {
        $enrollment->update(['payment_status' => 'refunded']);
        return redirect()->back()->with('success', 'Enrollment refunded successfully!');
    }

    public function enrollmentsApprove(Enrollment $enrollment)
    {
        $enrollment->update(['payment_status' => 'completed']);
        return redirect()->back()->with('success', 'Enrollment approved successfully! Student can now access the course.');
    }

    public function enrollmentsReject(Enrollment $enrollment)
    {
        $enrollment->update(['payment_status' => 'failed']);
        return redirect()->back()->with('success', 'Enrollment rejected.');
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
            $search = $request->search;
            // The match must be grouped, otherwise the OR escapes the rating /
            // approval filters and returns rows that should have been excluded.
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('course', fn ($course) => $course->where('title', 'like', "%{$search}%"));
            });
        }

        // Whitelist the sort column — it is interpolated into orderBy().
        $sortable = ['id', 'rating', 'is_approved', 'created_at'];
        $sort = in_array($request->sort, $sortable, true) ? $request->sort : 'created_at';
        $direction = $request->direction === 'asc' ? 'asc' : 'desc';

        $reviews = $query->orderBy($sort, $direction)->paginate(20)->withQueryString();

        return Inertia::render('Admin/Reviews/Index', [
            'reviews' => $reviews,
            'filters' => [
                'search' => $request->search,
                'rating' => $request->rating,
                'approved' => $request->approved,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'title' => 'Reviews',
            'description' => 'Moderate student feedback before it appears on the public site.',
        ]);
    }

    public function reviewsApprove(Review $review)
    {
        $review->update(['is_approved' => true]);
        return redirect()->back()->with('success', 'Review approved!');
    }

    public function reviewsEdit(Review $review)
    {
        $review->load(['user', 'course.instructor']);

        return Inertia::render('Admin/Reviews/Form', [
            'review' => $review,
            'title' => 'Edit review',
            'description' => 'Adjust the rating or hide the review from the public course page.',
        ]);
    }

    public function reviewsUpdate(Request $request, Review $review)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_approved' => $request->has('is_approved') ? 1 : 0,
        ]);

        return redirect()->route('admin.reviews.index')->with('success', 'Review updated successfully!');
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
            'total_revenue' => (float) Enrollment::where('payment_status', 'completed')->sum('price_paid'),
        ];

        $userDistribution = [
            'students' => User::students()->count(),
            'lecturers' => User::lecturers()->count(),
            'admins' => User::admins()->count(),
        ];

        // Last 12 months of revenue in ONE grouped query — the blade ran twelve
        // sequential SUM() calls, one per month.
        $revenueByMonth = Enrollment::where('payment_status', 'completed')
            ->where('enrolled_at', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(enrolled_at, '%Y-%m') as ym, SUM(price_paid) as revenue")
            ->groupBy('ym')
            ->get()
            ->pluck('revenue', 'ym');

        $monthlyRevenue = ['labels' => [], 'data' => []];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyRevenue['labels'][] = $date->format('M Y');
            $monthlyRevenue['data'][] = (float) ($revenueByMonth[$date->format('Y-m')] ?? 0);
        }

        // Revenue per course via a correlated subquery instead of one query per row.
        $topCourses = Course::query()
            ->select('courses.*')
            ->with('instructor:id,name')
            ->withCount('enrollments')
            ->selectSub(function ($query) {
                $query->from('enrollments')
                    ->whereColumn('enrollments.course_id', 'courses.id')
                    ->where('payment_status', 'completed')
                    ->selectRaw('COALESCE(SUM(price_paid), 0)');
            }, 'revenue')
            ->orderByDesc('enrollments_count')
            // Tie-break on id so the "top" ordering is stable between requests.
            ->orderBy('courses.id')
            ->take(10)
            ->get()
            ->map(function ($course) {
                $course->revenue = (float) $course->revenue;
                return $course;
            });

        // Per-instructor totals in a single pass, keyed by instructor id.
        $instructorTotals = Enrollment::query()
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->where('enrollments.payment_status', 'completed')
            ->selectRaw('courses.instructor_id as instructor_id, COUNT(*) as students_count, SUM(enrollments.price_paid) as revenue')
            ->groupBy('courses.instructor_id')
            ->get()
            ->keyBy('instructor_id');

        $topInstructors = User::lecturers()
            ->withCount('courses')
            ->get()
            ->map(function ($instructor) use ($instructorTotals) {
                $totals = $instructorTotals->get($instructor->id);
                $instructor->students_count = (int) ($totals->students_count ?? 0);
                $instructor->revenue = (float) ($totals->revenue ?? 0);
                return $instructor;
            })
            ->sortByDesc('revenue')
            ->take(10)
            // `values()` is load-bearing: without it the JSON payload is an object
            // keyed by the original collection offsets, not an array.
            ->values();

        // Category rollups — two grouped queries instead of three per category.
        $enrollmentTotals = Enrollment::query()
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->where('enrollments.payment_status', 'completed')
            ->selectRaw('courses.category_id as category_id, COUNT(*) as students_count, SUM(enrollments.price_paid) as revenue')
            ->groupBy('courses.category_id')
            ->get()
            ->keyBy('category_id');

        $ratingAverages = Review::query()
            ->join('courses', 'courses.id', '=', 'reviews.course_id')
            ->selectRaw('courses.category_id as category_id, AVG(reviews.rating) as avg_rating')
            ->groupBy('courses.category_id')
            ->get()
            ->keyBy('category_id');

        $categoryStats = Category::withCount('courses')
            ->orderBy('id')
            ->get()
            ->map(function ($category) use ($enrollmentTotals, $ratingAverages) {
                $totals = $enrollmentTotals->get($category->id);
                $ratings = $ratingAverages->get($category->id);
                $category->students_count = (int) ($totals->students_count ?? 0);
                $category->revenue = (float) ($totals->revenue ?? 0);
                $category->avg_rating = $ratings ? round((float) $ratings->avg_rating, 1) : 0;
                return $category;
            })
            ->values();

        return Inertia::render('Admin/Reports', [
            'stats' => $stats,
            'userDistribution' => $userDistribution,
            'monthlyRevenue' => $monthlyRevenue,
            'topCourses' => $topCourses,
            'topInstructors' => $topInstructors,
            'categoryStats' => $categoryStats,
            'title' => 'Reports',
            'description' => 'Platform activity, revenue and course performance.',
        ]);
    }

    // ==================== SETTINGS ====================
    // Settings methods moved to Site Settings section below

    public function cacheClear()
    {
        \Artisan::call('cache:clear');
        \Artisan::call('view:clear');
        \Artisan::call('config:clear');
        
        return redirect()->back()->with('success', 'Cache cleared successfully!');
    }

    // ==================== EXAM MANAGEMENT ====================

    public function examsIndex(Request $request)
    {
        // `withCount` replaces the blade's `$exam->questions->count()`, which
        // lazy-loaded the whole question set once per row.
        $query = \App\Models\Exam::with(['course', 'creator'])
            ->withCount(['questions', 'attempts']);

        if ($request->course_id) {
            $query->where('course_id', $request->course_id);
        }
        if ($request->is_published !== null && $request->is_published !== '') {
            $query->where('is_published', $request->is_published);
        }
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Whitelist — only these column names may reach orderBy().
        $sortable = ['title', 'duration_minutes', 'is_published', 'created_at'];
        $sort = in_array($request->sort, $sortable, true) ? $request->sort : 'created_at';
        $direction = $request->direction === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $direction);

        $exams = $query->paginate(20)->withQueryString();
        $courses = Course::published()->orderBy('title')->get();

        return Inertia::render('Admin/Exams/Index', [
            'exams' => $exams,
            'courses' => $courses,
            'filters' => [
                'search' => $request->search,
                'course_id' => $request->course_id,
                'is_published' => $request->is_published,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'title' => 'Exam management',
            'description' => 'Create exams, add questions and review student attempts.',
        ]);
    }

    public function examsCreate()
    {
        $courses = Course::published()->orderBy('title')->get();

        return Inertia::render('Admin/Exams/Form', [
            'exam' => null,
            'courses' => $courses,
            'title' => 'Create exam',
            'description' => 'Set up the exam settings first — questions are added on the next screen.',
        ]);
    }

    public function examsStore(Request $request)
    {
        // `title` is NOT NULL in the schema, so `nullable` here meant an empty
        // title reached the INSERT and blew up with a 1048 integrity error.
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'max_attempts' => 'required|integer|min:1',
        ]);

        $exam = \App\Models\Exam::create([
            'course_id' => $request->course_id,
            'created_by' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'duration_minutes' => $request->duration_minutes,
            'passing_score' => $request->passing_score,
            'max_attempts' => $request->max_attempts,
            'show_results' => $request->boolean('show_results', true),
            'show_correct_answers' => $request->boolean('show_correct_answers', true),
            'is_published' => $request->boolean('is_published', false),
        ]);

        return redirect()->route('admin.exams.edit', $exam)
            ->with('success', 'Exam created! Now add questions.');
    }

    public function examsEdit(\App\Models\Exam $exam)
    {
        $exam->load('questions');

        $courses = Course::published()->orderBy('title')->get();

        // The course picker only lists published courses, but an exam can be
        // attached to one that was later archived. Without this the select would
        // render empty and saving would silently reassign the exam.
        $current = $exam->course;
        if ($current && ! $courses->contains('id', $current->id)) {
            $courses->push($current);
        }

        return Inertia::render('Admin/Exams/Form', [
            'exam' => $exam,
            'courses' => $courses,
            'title' => 'Edit exam',
            'description' => $exam->title,
        ]);
    }

    public function examsUpdate(Request $request, \App\Models\Exam $exam)
    {
        // See examsStore() — `title` is NOT NULL in the schema.
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'max_attempts' => 'required|integer|min:1',
        ]);

        $exam->update([
            'course_id' => $request->course_id,
            'title' => $request->title,
            'description' => $request->description,
            'duration_minutes' => $request->duration_minutes,
            'passing_score' => $request->passing_score,
            'max_attempts' => $request->max_attempts,
            'show_results' => $request->boolean('show_results'),
            'show_correct_answers' => $request->boolean('show_correct_answers'),
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->back()->with('success', 'Exam updated successfully!');
    }

    public function examsAddQuestion(Request $request, \App\Models\Exam $exam)
    {
        $request->validate([
            'question' => 'required|string',
            'type' => 'required|in:multiple_choice,essay,true_false',
            'points' => 'required|integer|min:1',
            // `exclude_unless` rather than `required_if`: the latter still runs
            // `array|min:2` on an empty list, so a true/false or essay question
            // only passed because the old blade leaked two blank option inputs.
            'options' => 'exclude_unless:type,multiple_choice|required|array|min:2',
            'correct_answer' => 'required_if:type,multiple_choice,true_false',
        ]);

        $question = $exam->questions()->create([
            'question' => $request->question,
            'type' => $request->type,
            'points' => $request->points,
            'options' => $request->type === 'multiple_choice' ? array_values(array_filter($request->options)) : null,
            'correct_answer' => $request->correct_answer,
            'order' => $exam->questions()->count() + 1,
        ]);

        if ($request->ajax() && ! $request->header('X-Inertia')) {
            return response()->json(['success' => true, 'question' => $question]);
        }

        return redirect()->back()->with('success', 'Question added successfully!');
    }

    public function examsUpdateQuestion(Request $request, \App\Models\Exam $exam, \App\Models\ExamQuestion $question)
    {
        $request->validate([
            'question' => 'required|string',
            'type' => 'required|in:multiple_choice,essay,true_false',
            'points' => 'required|integer|min:1',
            // See examsAddQuestion() — `exclude_unless` keeps `min:2` off
            // non-multiple-choice questions.
            'options' => 'exclude_unless:type,multiple_choice|required|array|min:2',
            'correct_answer' => 'required_if:type,multiple_choice,true_false',
        ]);

        $question->update([
            'question' => $request->question,
            'type' => $request->type,
            'points' => $request->points,
            'options' => $request->type === 'multiple_choice' ? array_values(array_filter($request->options)) : null,
            'correct_answer' => $request->correct_answer,
        ]);

        if ($request->ajax() && ! $request->header('X-Inertia')) {
            return response()->json(['success' => true, 'question' => $question]);
        }

        return redirect()->back()->with('success', 'Question updated successfully!');
    }

    public function examsDeleteQuestion(\App\Models\Exam $exam, \App\Models\ExamQuestion $question)
    {
        $question->delete();

        if (request()->ajax() && ! request()->header('X-Inertia')) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Question deleted successfully!');
    }

    public function examsResults(\App\Models\Exam $exam)
    {
        $exam->load('course');

        $attempts = $exam->attempts()
            ->with('user')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // `percentage` is an accessor and Eloquent does not serialise accessors by
        // default. Appending it here also uses the accessor's divide-by-zero guard,
        // which the blade lacked when it did `score / total_points` inline.
        $attempts->getCollection()->each->append('percentage');

        // Stats over ALL attempts. The blade derived these from the paginator, so
        // every number was wrong once an exam passed 20 attempts.
        // Aliases deliberately avoid `passed`/`total_points` — those are in
        // ExamAttempt::$casts and would be coerced to booleans.
        $stats = $exam->attempts()
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('SUM(CASE WHEN passed = 1 THEN 1 ELSE 0 END) as passed_count')
            ->selectRaw('SUM(CASE WHEN passed = 0 THEN 1 ELSE 0 END) as failed_count')
            ->selectRaw('AVG(CASE WHEN total_points > 0 THEN (score / total_points) * 100 ELSE 0 END) as average_score')
            ->first();

        return Inertia::render('Admin/Exams/Results', [
            'exam' => $exam,
            'attempts' => $attempts,
            'stats' => [
                'total' => (int) ($stats->total_count ?? 0),
                'passed' => (int) ($stats->passed_count ?? 0),
                'failed' => (int) ($stats->failed_count ?? 0),
                'average' => (int) round((float) ($stats->average_score ?? 0)),
            ],
            'title' => 'Exam results',
            'description' => $exam->title . ($exam->course ? ' · ' . $exam->course->title : ''),
        ]);
    }

    public function examsGrade(\App\Models\ExamAttempt $attempt)
    {
        $attempt->load(['exam.course', 'user', 'answers.question']);

        return Inertia::render('Admin/Exams/Grade', [
            'attempt' => $attempt,
            'title' => 'Grade exam attempt',
            'description' => $attempt->exam->title . ' · ' . $attempt->user->name,
        ]);
    }

    public function examsSubmitGrade(Request $request, \App\Models\ExamAttempt $attempt)
    {
        $request->validate([
            'grades' => 'required|array',
            'grades.*.answer_id' => 'required|exists:exam_answers,id',
            'grades.*.points' => 'required|integer|min:0',
            'grades.*.feedback' => 'nullable|string',
        ]);

        foreach ($request->grades as $grade) {
            $answer = \App\Models\ExamAnswer::find($grade['answer_id']);
            $answer->update([
                'points_earned' => (int) $grade['points'],
                'feedback' => $grade['feedback'] ?? null,
            ]);
        }

        // Sum all answers (auto-graded MCQ/TF + manually graded essay)
        $totalScore = $attempt->answers()->sum('points_earned');

        $percentage = ($attempt->total_points > 0) ? ($totalScore / $attempt->total_points) * 100 : 0;

        $attempt->update([
            'score' => $totalScore,
            'passed' => $percentage >= $attempt->exam->passing_score,
            'status' => 'graded',
        ]);

        return redirect()->route('admin.exams.results', $attempt->exam)
            ->with('success', 'Exam graded successfully!');
    }

    public function examsDestroy(\App\Models\Exam $exam)
    {
        $exam->delete();
        return redirect()->route('admin.exams.index')->with('success', 'Exam deleted successfully!');
    }

    // ==================== CONTACT MESSAGES ====================

    public function contactMessages(Request $request)
    {
        $query = \App\Models\ContactMessage::query();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Whitelist the sort column — it is interpolated into orderBy().
        $sortable = ['id', 'name', 'status', 'created_at'];
        $sort = in_array($request->sort, $sortable, true) ? $request->sort : 'created_at';
        $direction = $request->direction === 'asc' ? 'asc' : 'desc';

        $messages = $query->orderBy($sort, $direction)->paginate(20)->withQueryString();

        // One grouped query instead of three COUNT round-trips.
        $totals = \App\Models\ContactMessage::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $stats = [
            'new' => (int) ($totals['new'] ?? 0),
            'read' => (int) ($totals['read'] ?? 0),
            'replied' => (int) ($totals['replied'] ?? 0),
        ];

        return Inertia::render('Admin/ContactMessages/Index', [
            'messages' => $messages,
            'stats' => $stats,
            'filters' => [
                'status' => $request->status,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'title' => 'Messages',
            'description' => 'Enquiries submitted through the public contact form.',
        ]);
    }

    public function contactMessageShow(\App\Models\ContactMessage $message)
    {
        // Mark as read if it's new
        if ($message->status === 'new') {
            $message->update(['status' => 'read']);
        }

        return Inertia::render('Admin/ContactMessages/Show', [
            'message' => $message,
            'title' => 'Message',
            'description' => $message->subject ?: 'Contact form enquiry',
        ]);
    }

    public function contactMessageReply(Request $request, \App\Models\ContactMessage $message)
    {
        $request->validate([
            'reply' => 'required|string',
        ]);

        $message->update([
            'admin_reply' => $request->reply,
            'status' => 'replied',
            'replied_at' => now(),
        ]);

        // Here you could also send an email to the user
        // Mail::to($message->email)->send(new ContactReplyMail($message));

        return redirect()->back()->with('success', 'Reply sent successfully!');
    }

    public function contactMessageDestroy(\App\Models\ContactMessage $message)
    {
        $message->delete();
        return redirect()->route('admin.contact-messages.index')->with('success', 'Message deleted successfully!');
    }

    public function contactMessageBulkDelete(\Illuminate\Http\Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'No messages selected.');
        }
        \App\Models\ContactMessage::whereIn('id', $ids)->delete();
        return redirect()->route('admin.contact-messages.index')
            ->with('success', count($ids) . ' message(s) deleted successfully!');
    }

    // ==================== SITE SETTINGS ====================

    public function settings()
    {
        $groups = \App\Models\SiteSetting::orderBy('group')->orderBy('label')->get()
            ->map(function ($setting) {
                // Resolved server-side so the Vue layer never has to build a
                // storage URL — SiteSetting::imageUrl() handles both uploads and
                // full external URLs.
                $setting->image_url = $setting->type === 'image'
                    ? \App\Models\SiteSetting::imageUrl($setting->key)
                    : null;
                return $setting;
            })
            ->groupBy('group')
            ->map(fn ($items, $group) => [
                'key' => $group,
                'label' => str_replace('_', ' ', $group),
                'settings' => $items->values(),
            ])
            ->values();

        return Inertia::render('Admin/Settings', [
            'groups' => $groups,
            'title' => 'Site settings',
            'description' => 'Editable content and statistics used across the public site.',
        ]);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'settings' => 'nullable|array',
            'settings.*' => 'nullable',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|max:4096',
        ]);

        foreach ($request->input('settings', []) as $key => $value) {
            $setting = \App\Models\SiteSetting::where('key', $key)->first();
            if ($setting) {
                $setting->update(['value' => $value]);
            }
        }

        foreach ($request->file('images', []) as $key => $file) {
            $setting = \App\Models\SiteSetting::where('key', $key)->where('type', 'image')->first();
            if ($setting && $file) {
                if ($setting->value && !str_starts_with($setting->value, 'http')) {
                    Storage::disk('public')->delete($setting->value);
                }
                $setting->update(['value' => $file->store('settings', 'public')]);
            }
        }

        \App\Models\SiteSetting::clearCache();

        return redirect()->route('admin.settings')->with('success', 'Settings updated successfully!');
    }

    // ==================== HERO SLIDES (Homepage Carousel) ====================

    public function heroSlidesIndex()
    {
        $slides = HeroSlide::ordered()->get();

        return Inertia::render('Admin/HeroSlides/Index', [
            'slides' => $slides,
            'title' => 'Hero slides',
            'description' => 'The carousel shown at the top of the public homepage.',
        ]);
    }

    public function heroSlidesCreate()
    {
        return Inertia::render('Admin/HeroSlides/Form', [
            'slide' => null,
            'title' => 'Add slide',
            'description' => 'Upload an image or point at an external URL.',
        ]);
    }

    public function heroSlidesStore(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'image_url' => 'nullable|url',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if (!$request->hasFile('image') && !$request->filled('image_url')) {
            return back()->withInput()->with('error', 'Please provide an image URL or upload an image file.');
        }

        $data = $request->only(['title', 'subtitle', 'button_text', 'button_link', 'sort_order']);
        $data['is_active'] = $request->boolean('is_active');
        $data['image'] = $request->hasFile('image')
            ? $request->file('image')->store('hero-slides', 'public')
            : $request->image_url;

        HeroSlide::create($data);
        return redirect()->route('admin.hero-slides.index')->with('success', 'Slide created successfully!');
    }

    public function heroSlidesEdit(HeroSlide $heroSlide)
    {
        return Inertia::render('Admin/HeroSlides/Form', [
            'slide' => $heroSlide->only([
                'id', 'title', 'subtitle', 'button_text', 'button_link', 'sort_order', 'is_active', 'image_url',
            ]) + [
                // The form needs to know whether `image` is a local file or an
                // external URL, so it can show the right hint and not pre-fill
                // the URL box with a storage path.
                'is_external' => str_starts_with((string) $heroSlide->image, 'http'),
            ],
            'title' => 'Edit slide',
            'description' => $heroSlide->title ?: 'Image-only slide',
        ]);
    }

    public function heroSlidesUpdate(Request $request, HeroSlide $heroSlide)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'image_url' => 'nullable|url',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->only(['title', 'subtitle', 'button_text', 'button_link', 'sort_order']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            if ($heroSlide->image && !str_starts_with($heroSlide->image, 'http')) {
                Storage::disk('public')->delete($heroSlide->image);
            }
            $data['image'] = $request->file('image')->store('hero-slides', 'public');
        } elseif ($request->filled('image_url')) {
            if ($heroSlide->image && !str_starts_with($heroSlide->image, 'http')) {
                Storage::disk('public')->delete($heroSlide->image);
            }
            $data['image'] = $request->image_url;
        }

        $heroSlide->update($data);
        return redirect()->route('admin.hero-slides.index')->with('success', 'Slide updated successfully!');
    }

    public function heroSlidesDestroy(HeroSlide $heroSlide)
    {
        if ($heroSlide->image && !str_starts_with($heroSlide->image, 'http')) {
            Storage::disk('public')->delete($heroSlide->image);
        }
        $heroSlide->delete();
        return redirect()->route('admin.hero-slides.index')->with('success', 'Slide deleted successfully!');
    }

    public function heroSlidesToggle(HeroSlide $heroSlide)
    {
        $heroSlide->update(['is_active' => !$heroSlide->is_active]);
        return redirect()->back()->with('success', 'Slide status updated!');
    }

    // ==================== BLOG POSTS ====================

    public function blogPosts()
    {
        $posts = \App\Models\BlogPost::with('author')->latest()->paginate(20);

        return Inertia::render('Admin/Blog/Index', [
            'posts' => $posts,
            'title' => 'Blog',
            'description' => 'Write and publish articles for the public blog.',
        ]);
    }

    public function createBlogPost()
    {
        return Inertia::render('Admin/Blog/Form', [
            'post' => null,
            'title' => 'New post',
            'description' => 'Draft a new article for the public blog.',
        ]);
    }

    public function storeBlogPost(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published',
        ]);

        $validated['author_id'] = auth()->id();
        $validated['slug'] = $this->uniqueBlogSlug($validated['title']);

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('blog', 'public');
        }

        if ($validated['status'] === 'published' && !$request->published_at) {
            $validated['published_at'] = now();
        }

        \App\Models\BlogPost::create($validated);

        return redirect()->route('admin.blog.index')->with('success', 'Blog post created successfully!');
    }

    public function editBlogPost(\App\Models\BlogPost $post)
    {
        $post->load('author');

        return Inertia::render('Admin/Blog/Form', [
            'post' => $post,
            'title' => 'Edit post',
            'description' => $post->title,
        ]);
    }

    public function updateBlogPost(Request $request, \App\Models\BlogPost $post)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published',
        ]);

        $validated['slug'] = $this->uniqueBlogSlug($validated['title'], $post->id);

        // Inertia serialises a `null` field to an empty string, and
        // ConvertEmptyStringsToNull turns that into null — so `featured_image`
        // would arrive as null whenever no new file was picked and silently
        // wipe the stored path. Keep it out of the update array and only touch
        // it when a real file is present.
        unset($validated['featured_image']);

        if ($request->hasFile('featured_image')) {
            // Delete old image
            if ($post->featured_image) {
                \Storage::disk('public')->delete($post->featured_image);
            }
            $validated['featured_image'] = $request->file('featured_image')->store('blog', 'public');
        }

        if ($validated['status'] === 'published' && !$post->published_at) {
            $validated['published_at'] = now();
        }

        $post->update($validated);

        return redirect()->route('admin.blog.index')->with('success', 'Blog post updated successfully!');
    }

    /**
     * Build a slug that is unique across `blog_posts`.
     *
     * The previous code used `Str::slug($title)` verbatim, so a second post with
     * the same title (or a second untitled draft) collided on the unique slug
     * index and the save died with a QueryException.
     */
    private function uniqueBlogSlug(?string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug((string) $title) ?: 'post-' . uniqid();
        $slug = $base;
        $suffix = 1;

        while (\App\Models\BlogPost::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . (++$suffix);
        }

        return $slug;
    }

    public function destroyBlogPost(\App\Models\BlogPost $post)
    {
        if ($post->featured_image) {
            \Storage::disk('public')->delete($post->featured_image);
        }

        $post->delete();

        return redirect()->route('admin.blog.index')->with('success', 'Blog post deleted successfully!');
    }

    // ==================== ADMIN TOOLS ====================

    public function createLecturer()
    {
        return view('admin.users.create-lecturer');
    }

    public function storeLecturer(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'phone'    => 'nullable|string|max:20',
            'bio'      => 'nullable|string|max:1000',
        ]);

        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'role'              => 'lecturer',
            'status'            => 'active',
            'is_active'         => true,
            'phone'             => $request->phone,
            'bio'               => $request->bio,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index', ['role' => 'lecturer'])
            ->with('success', "Lecturer account created! Name: {$user->name} | Email: {$user->email}");
    }

    public function toggleRegistration()
    {
        $current = \App\Models\Setting::get('registration_enabled', '1');
        $new     = $current === '1' ? '0' : '1';
        \App\Models\Setting::set('registration_enabled', $new);
        $msg = $new === '1' ? 'User registration is now ENABLED.' : 'User registration is now DISABLED.';
        return redirect()->back()->with('success', $msg);
    }
}
