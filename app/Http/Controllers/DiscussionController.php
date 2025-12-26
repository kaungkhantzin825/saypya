<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiscussionController extends Controller
{
    public function index(Course $course)
    {
        // Check if user is enrolled
        if (!$course->enrollments()->where('user_id', Auth::id())->where('payment_status', 'completed')->exists()) {
            abort(403, 'သင်ခန်းစာတွင် စာရင်းသွင်းပြီးမှသာ ဆွေးနွေးမှုများကို ကြည့်ရှုနိုင်ပါသည်။');
        }

        $discussions = $course->discussions()
            ->with(['user', 'replies.user'])
            ->latest()
            ->paginate(10);

        return view('courses.discussions.index', compact('course', 'discussions'));
    }

    public function store(Request $request, Course $course)
    {
        // Check if user is enrolled
        if (!$course->enrollments()->where('user_id', Auth::id())->where('payment_status', 'completed')->exists()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        Discussion::create([
            'course_id' => $course->id,
            'user_id' => Auth::id(),
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return back()->with('success', 'ဆွေးနွေးမှုကို ဖန်တီးပြီးပါပြီ။');
    }

    public function show(Course $course, Discussion $discussion)
    {
        // Check if user is enrolled
        if (!$course->enrollments()->where('user_id', Auth::id())->where('payment_status', 'completed')->exists()) {
            abort(403);
        }

        $discussion->load(['user', 'replies.user']);

        return view('courses.discussions.show', compact('course', 'discussion'));
    }

    public function reply(Request $request, Course $course, Discussion $discussion)
    {
        // Check if user is enrolled
        if (!$course->enrollments()->where('user_id', Auth::id())->where('payment_status', 'completed')->exists()) {
            abort(403);
        }

        $request->validate([
            'content' => 'required|string',
        ]);

        DiscussionReply::create([
            'discussion_id' => $discussion->id,
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        return back()->with('success', 'အကြောင်းပြန်ပြီးပါပြီ။');
    }

    public function resolve(Course $course, Discussion $discussion)
    {
        // Only course instructor or discussion creator can resolve
        if ($course->instructor_id !== Auth::id() && $discussion->user_id !== Auth::id()) {
            abort(403);
        }

        $discussion->update(['is_resolved' => !$discussion->is_resolved]);

        $message = $discussion->is_resolved ? 'ဆွေးနွေးမှုကို ဖြေရှင်းပြီးပါပြီ။' : 'ဆွေးနွေးမှုကို ပြန်ဖွင့်ပြီးပါပြီ။';

        return back()->with('success', $message);
    }
}