<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::active()
            ->withCount(['courses' => function ($query) {
                $query->published();
            }])
            ->ordered()
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function show(Request $request, Category $category)
    {
        $query = Course::published()
            ->where('category_id', $category->id)
            ->with(['instructor', 'reviews']);

        // Apply filters
        if ($request->level) {
            $query->where('level', $request->level);
        }

        // Apply sorting
        switch ($request->sort) {
            case 'popular':
                $query->withCount('enrollments')->orderBy('enrollments_count', 'desc');
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            default:
                $query->latest();
        }

        $courses = $query->paginate(12);

        // Get total students in this category
        $totalStudents = \App\Models\Enrollment::whereIn('course_id', 
            Course::where('category_id', $category->id)->pluck('id')
        )->where('payment_status', 'completed')->distinct('user_id')->count();

        // Get other categories
        $otherCategories = Category::active()
            ->where('id', '!=', $category->id)
            ->withCount(['courses' => function ($query) {
                $query->published();
            }])
            ->ordered()
            ->take(6)
            ->get();

        return view('categories.show', compact('category', 'courses', 'totalStudents', 'otherCategories'));
    }

    public function apiIndex()
    {
        $categories = Category::active()->ordered()->get();
        
        return response()->json($categories);
    }
}