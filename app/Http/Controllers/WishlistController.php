<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function toggle(Course $course)
    {
        $user = Auth::user();
        
        if ($user->wishlist()->where('course_id', $course->id)->exists()) {
            $user->wishlist()->detach($course->id);
            $message = 'သင်ခန်းစာကို စိတ်ကြိုက်စာရင်းမှ ဖယ်ရှားပြီးပါပြီ။';
        } else {
            $user->wishlist()->attach($course->id);
            $message = 'သင်ခန်းစာကို စိတ်ကြိုက်စာရင်းသို့ ထည့်ပြီးပါပြီ။';
        }

        // Inertia sends `X-Requested-With: XMLHttpRequest` too, so check the
        // Inertia header first and hand those requests a redirect + flash.
        if (request()->header('X-Inertia')) {
            return back()->with('success', $message);
        }

        if (request()->ajax()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('success', $message);
    }
}