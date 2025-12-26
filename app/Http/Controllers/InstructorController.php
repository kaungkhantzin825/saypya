<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstructorController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        if (!$user->isLecturer()) {
            return redirect()->route('dashboard');
        }

        $courses = $user->courses()->withCount(['enrollments', 'reviews'])->get();
        $totalStudents = $user->courses()->withCount('enrollments')->get()->sum('enrollments_count');
        $totalRevenue = $user->courses()
            ->join('enrollments', 'courses.id', '=', 'enrollments.course_id')
            ->where('enrollments.payment_status', 'completed')
            ->sum('enrollments.price_paid');

        $stats = [
            'total_courses' => $courses->count(),
            'total_students' => $totalStudents,
            'total_revenue' => $totalRevenue,
            'average_rating' => $courses->avg('average_rating') ?? 0,
        ];

        return view('instructor.dashboard', compact('courses', 'stats'));
    }

    public function courses()
    {
        $user = Auth::user();
        $courses = $user->courses()->with(['category', 'enrollments', 'reviews'])->paginate(10);

        return view('instructor.courses', compact('courses'));
    }

    public function createCourse()
    {
        $categories = \App\Models\Category::active()->ordered()->get();
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
        
        // Handle thumbnail upload
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        $course = $user->courses()->create([
            'title' => $request->title,
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

    public function editCourse(\App\Models\Course $course)
    {
        $this->authorize('update', $course);
        $categories = \App\Models\Category::active()->ordered()->get();
        return view('instructor.courses.edit', compact('course', 'categories'));
    }

    public function updateCourse(Request $request, \App\Models\Course $course)
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

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($course->thumbnail) {
                \Storage::disk('public')->delete($course->thumbnail);
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

        return redirect()->route('instructor.courses')
            ->with('success', 'Course updated successfully!');
    }

    public function courseContent(\App\Models\Course $course)
    {
        $this->authorize('update', $course);
        $course->load(['sections.lessons']);
        return view('instructor.courses.content', compact('course'));
    }

    public function storeSection(Request $request, \App\Models\Course $course)
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

        return response()->json([
            'success' => true,
            'section' => $section,
            'message' => 'Section created successfully!'
        ]);
    }

    public function storeLesson(Request $request, \App\Models\Course $course, \App\Models\Section $section)
    {
        $this->authorize('update', $course);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:video,text,quiz,assignment',
            'video_url' => 'nullable|string|url',
            'video_file' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:102400', // 100MB max
            'video_duration' => 'nullable|integer|min:0',
            'content' => 'nullable|string',
            'is_preview' => 'boolean',
        ]);

        $videoUrl = null;
        
        // Handle video upload or CDN link
        if ($request->hasFile('video_file')) {
            $videoUrl = $request->file('video_file')->store('courses/videos', 'public');
        } elseif ($request->video_url) {
            $videoUrl = $request->video_url;
        }

        $lesson = $section->lessons()->create([
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'video_url' => $videoUrl,
            'video_duration' => $request->video_duration,
            'content' => $request->content,
            'is_preview' => $request->boolean('is_preview'),
            'sort_order' => $section->lessons()->count() + 1,
        ]);

        return response()->json([
            'success' => true,
            'lesson' => $lesson,
            'message' => 'Lesson created successfully!'
        ]);
    }

    public function analytics()
    {
        return view('instructor.analytics');
    }

    public function students()
    {
        return view('instructor.students');
    }

    public function earnings()
    {
        return view('instructor.earnings');
    }

    public function reviews()
    {
        return view('instructor.reviews');
    }
}