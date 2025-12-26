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

        if (request()->ajax()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('success', $message);
    }
}