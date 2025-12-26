<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Check if user is enrolled in the course
        if (!$course->enrollments()->where('user_id', Auth::id())->where('payment_status', 'completed')->exists()) {
            return back()->with('error', 'သင်ခန်းစာတွင် စာရင်းသွင်းပြီးမှသာ သုံးသပ်ချက်ပေးနိုင်ပါသည်။');
        }

        // Check if user already reviewed this course
        $existingReview = Review::where('course_id', $course->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingReview) {
            return back()->with('error', 'သင်သည် ဤသင်ခန်းစာအတွက် သုံးသပ်ချက်ပေးပြီးပါပြီ။');
        }

        Review::create([
            'course_id' => $course->id,
            'user_id' => Auth::id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'သုံးသပ်ချက်ပေးပြီးပါပြီ။ ကျေးးဇူးတင်ပါသည်။');
    }

    public function update(Request $request, Review $review)
    {
        // Check if user owns this review
        if ($review->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'သုံးသပ်ချက်ကို ပြင်ဆင်ပြီးပါပြီ။');
    }

    public function destroy(Review $review)
    {
        // Check if user owns this review
        if ($review->user_id !== Auth::id()) {
            abort(403);
        }

        $review->delete();

        return back()->with('success', 'သုံးသပ်ချက်ကို ဖျက်ပြီးပါပြီ။');
    }
}