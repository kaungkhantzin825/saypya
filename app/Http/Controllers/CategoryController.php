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

    public function show(Category $category)
    {
        $courses = Course::published()
            ->where('category_id', $category->id)
            ->with(['instructor', 'reviews'])
            ->paginate(12);

        return view('categories.show', compact('category', 'courses'));
    }

    public function apiIndex()
    {
        $categories = Category::active()->ordered()->get();
        
        return response()->json($categories);
    }
}