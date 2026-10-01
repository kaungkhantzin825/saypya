<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function show(Request $request, Category $category)
    {
        $query = Course::published()
            ->where('category_id', $category->id)
            ->with(['instructor', 'category', 'reviews'])
            ->withCount('lessons');

        if ($request->level) {
            $query->where('level', $request->level);
        }

        switch ($request->sort) {
            case 'popular':
                $query->withCount('enrollments')->orderByDesc('enrollments_count');
                break;
            case 'price_low':
                $query->orderBy('price');
                break;
            case 'price_high':
                $query->orderByDesc('price');
                break;
            default:
                $query->latest();
        }

        $totalStudents = Enrollment::whereIn(
            'course_id',
            Course::where('category_id', $category->id)->pluck('id')
        )->where('payment_status', 'completed')->distinct('user_id')->count('user_id');

        $otherCategories = Category::active()
            ->where('id', '!=', $category->id)
            ->withCount(['courses' => function ($query) {
                $query->published();
            }])
            ->ordered()
            ->take(6)
            ->get();

        return Inertia::render('Categories/Show', [
            'category' => $category,
            'courses' => $query->paginate(12)->withQueryString(),
            'totalStudents' => $totalStudents,
            'otherCategories' => $otherCategories,
            'level' => $request->level,
            'sort' => $request->sort,
        ]);
    }

    public function apiIndex()
    {
        return response()->json(Category::active()->ordered()->get());
    }
}
