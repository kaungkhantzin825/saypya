<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\LessonController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [HomeController::class, 'search'])->name('search');
Route::get('/instructors/{user}', [HomeController::class, 'instructorProfile'])->name('instructors.profile');

// Language switching
Route::get('/language/{locale}', [App\Http\Controllers\LanguageController::class, 'switch'])->name('language.switch');

// Test route for language debugging
Route::get('/test-lang', function () {
    return view('test-lang');
})->name('test.lang');

// Course routes
Route::prefix('courses')->name('courses.')->group(function () {
    Route::get('/', [CourseController::class, 'index'])->name('index');
    Route::get('/{course:slug}', [CourseController::class, 'show'])->name('show');
    Route::get('/{course:slug}/checkout', [CourseController::class, 'checkout'])->name('checkout')->middleware('auth');
    Route::post('/{course}/enroll', [CourseController::class, 'enroll'])->name('enroll')->middleware('auth');
    Route::get('/{course:slug}/learn', [CourseController::class, 'learn'])->name('learn')->middleware('auth');
    Route::get('/{course:slug}/learn/{lesson}', [CourseController::class, 'lesson'])->name('lesson')->middleware('auth');
    Route::post('/{course}/wishlist', [CourseController::class, 'toggleWishlist'])->name('wishlist.toggle')->middleware('auth');
});

// Category routes
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

// Authentication routes
require __DIR__.'/auth.php';

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Dashboard
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
    
    // Student routes
    Route::prefix('my')->name('my.')->group(function () {
        Route::get('/courses', [StudentController::class, 'courses'])->name('courses');
        Route::get('/wishlist', [StudentController::class, 'wishlist'])->name('wishlist');
        Route::get('/certificates', [StudentController::class, 'certificates'])->name('certificates');
        Route::get('/progress', [StudentController::class, 'progress'])->name('progress');
    });
    
    // Lesson progress
    Route::post('/lessons/{lesson}/progress', [LessonController::class, 'updateProgress'])->name('lessons.progress');
    Route::post('/lessons/{lesson}/complete', [LessonController::class, 'markComplete'])->name('lessons.complete');
    
    // Reviews
    Route::post('/courses/{course}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    
    // Discussions
    Route::prefix('courses/{course}/discussions')->name('discussions.')->group(function () {
        Route::get('/', [DiscussionController::class, 'index'])->name('index');
        Route::post('/', [DiscussionController::class, 'store'])->name('store');
        Route::get('/{discussion}', [DiscussionController::class, 'show'])->name('show');
        Route::post('/{discussion}/replies', [DiscussionController::class, 'reply'])->name('reply');
        Route::patch('/{discussion}/resolve', [DiscussionController::class, 'resolve'])->name('resolve');
    });
});

// Instructor routes
Route::middleware(['auth', 'role:lecturer'])->prefix('instructor')->name('instructor.')->group(function () {
    Route::get('/dashboard', [InstructorController::class, 'dashboard'])->name('dashboard');
    Route::get('/courses', [InstructorController::class, 'courses'])->name('courses');
    Route::get('/courses/create', [InstructorController::class, 'createCourse'])->name('courses.create');
    Route::post('/courses', [InstructorController::class, 'storeCourse'])->name('courses.store');
    Route::get('/courses/{course}/edit', [InstructorController::class, 'editCourse'])->name('courses.edit');
    Route::put('/courses/{course}', [InstructorController::class, 'updateCourse'])->name('courses.update');
    Route::delete('/courses/{course}', [InstructorController::class, 'destroyCourse'])->name('courses.destroy');
    
    // Course content management
    Route::prefix('courses/{course}')->name('courses.')->group(function () {
        Route::get('/content', [InstructorController::class, 'courseContent'])->name('content');
        Route::post('/sections', [InstructorController::class, 'storeSection'])->name('sections.store');
        Route::put('/sections/{section}', [InstructorController::class, 'updateSection'])->name('sections.update');
        Route::delete('/sections/{section}', [InstructorController::class, 'destroySection'])->name('sections.destroy');
        Route::post('/sections/{section}/lessons', [InstructorController::class, 'storeLesson'])->name('sections.lessons.store');
        Route::put('/lessons/{lesson}', [InstructorController::class, 'updateLesson'])->name('lessons.update');
        Route::delete('/lessons/{lesson}', [InstructorController::class, 'destroyLesson'])->name('lessons.destroy');
    });
    
    // Analytics and reports
    Route::get('/analytics', [InstructorController::class, 'analytics'])->name('analytics');
    Route::get('/students', [InstructorController::class, 'students'])->name('students');
    Route::get('/earnings', [InstructorController::class, 'earnings'])->name('earnings');
    Route::get('/reviews', [InstructorController::class, 'reviews'])->name('reviews');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // User management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminController::class, 'users'])->name('index');
        Route::get('/{user}', [AdminController::class, 'showUser'])->name('show');
        Route::put('/{user}', [AdminController::class, 'updateUser'])->name('update');
        Route::delete('/{user}', [AdminController::class, 'destroyUser'])->name('destroy');
        Route::post('/{user}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('toggle-status');
    });
    
    // Course management
    Route::prefix('courses')->name('courses.')->group(function () {
        Route::get('/', [AdminController::class, 'courses'])->name('index');
        Route::get('/{course}', [AdminController::class, 'showCourse'])->name('show');
        Route::put('/{course}', [AdminController::class, 'updateCourse'])->name('update');
        Route::delete('/{course}', [AdminController::class, 'destroyCourse'])->name('destroy');
        Route::post('/{course}/approve', [AdminController::class, 'approveCourse'])->name('approve');
        Route::post('/{course}/reject', [AdminController::class, 'rejectCourse'])->name('reject');
        Route::post('/{course}/feature', [AdminController::class, 'toggleFeature'])->name('toggle-feature');
    });
    
    // Category management
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [AdminController::class, 'categories'])->name('index');
        Route::post('/', [AdminController::class, 'storeCategory'])->name('store');
        Route::put('/{category}', [AdminController::class, 'updateCategory'])->name('update');
        Route::delete('/{category}', [AdminController::class, 'destroyCategory'])->name('destroy');
    });
    
    // Review management
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [AdminController::class, 'reviews'])->name('index');
        Route::post('/{review}/approve', [AdminController::class, 'approveReview'])->name('approve');
        Route::post('/{review}/reject', [AdminController::class, 'rejectReview'])->name('reject');
        Route::delete('/{review}', [AdminController::class, 'destroyReview'])->name('destroy');
    });
    
    // Analytics and reports
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
});

// API routes for AJAX requests
Route::middleware('auth')->prefix('api')->name('api.')->group(function () {
    Route::get('/courses/search', [CourseController::class, 'searchApi'])->name('courses.search');
    Route::get('/categories', [CategoryController::class, 'apiIndex'])->name('categories.index');
    Route::post('/upload/image', [AdminController::class, 'uploadImage'])->name('upload.image');
    Route::post('/upload/video', [AdminController::class, 'uploadVideo'])->name('upload.video');
});