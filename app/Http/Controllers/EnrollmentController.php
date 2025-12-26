<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnrollmentController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $user = Auth::user();

        // Check if already enrolled
        if ($course->enrollments()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'သင်သည် ဤသင်ခန်းစာတွင် စာရင်းသွင်းပြီးပါပြီ။');
        }

        // Check if course is published
        if (!$course->is_published) {
            return back()->with('error', 'ဤသင်ခန်းစာကို လောလောဆယ် စာရင်းသွင်း၍မရပါ။');
        }

        // For free courses, enroll directly
        if ($course->price == 0) {
            Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'payment_status' => 'completed',
                'enrolled_at' => now(),
            ]);

            return redirect()->route('courses.learn', $course)
                ->with('success', 'အခမဲ့သင်ခန်းစာတွင် စာရင်းသွင်းပြီးပါပြီ။ သင်ယူခြင်းကို စတင်နိုင်ပါပြီ။');
        }

        // For paid courses, redirect to checkout
        return redirect()->route('courses.checkout', $course);
    }

    public function destroy(Enrollment $enrollment)
    {
        // Only allow user to unenroll from their own enrollments
        if ($enrollment->user_id !== Auth::id()) {
            abort(403);
        }

        $enrollment->delete();

        return back()->with('success', 'သင်ခန်းစာမှ စာရင်းပယ်ဖျက်ပြီးပါပြီ။');
    }
}