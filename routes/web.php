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
use App\Http\Controllers\CommentController;
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

// Static pages
Route::get('/about', function () {
    return view('pages.about');
})->name('about');
Route::get('/team', function () {
    return view('pages.team');
})->name('team');
Route::get('/partners', function () {
    return view('pages.partners');
})->name('partners');
Route::get('/contact', function () {
    return view('pages.contact');
})->name('contact');
Route::post('/contact', [App\Http\Controllers\ContactController::class, 'submit'])->name('contact.submit');
Route::get('/help', function () {
    return view('pages.help');
})->name('help');
Route::get('/privacy', function () {
    return view('pages.privacy');
})->name('privacy');
Route::get('/terms', function () {
    return view('pages.terms');
})->name('terms');

// Language switching
Route::get('/language/{locale}', [App\Http\Controllers\LanguageController::class, 'switch'])->name('language.switch');

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
    
    // Comments
    Route::post('/courses/{course}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    
    // Exams (Student)
    Route::prefix('courses/{course}/exams')->name('exams.')->group(function () {
        Route::get('/', [App\Http\Controllers\ExamController::class, 'index'])->name('index');
    });
    Route::get('/exams/{exam}/start', [App\Http\Controllers\ExamController::class, 'start'])->name('exams.start');
    Route::post('/exam-attempts/{attempt}/submit', [App\Http\Controllers\ExamController::class, 'submit'])->name('exams.submit');
    Route::get('/exam-attempts/{attempt}/result', [App\Http\Controllers\ExamController::class, 'result'])->name('exams.result');
    Route::get('/my/exams', [App\Http\Controllers\ExamController::class, 'myExams'])->name('my.exams');
    
    // Discussions
    Route::prefix('courses/{course}/discussions')->name('discussions.')->group(function () {
        Route::get('/', [DiscussionController::class, 'index'])->name('index');
        Route::post('/', [DiscussionController::class, 'store'])->name('store');
        Route::get('/{discussion}', [DiscussionController::class, 'show'])->name('show');
        Route::post('/{discussion}/replies', [DiscussionController::class, 'reply'])->name('reply');
        Route::patch('/{discussion}/resolve', [DiscussionController::class, 'resolve'])->name('resolve');
    });
});

// Instructor routes (Lecturer Dashboard)
Route::middleware(['auth', 'role:lecturer'])->prefix('instructor')->name('instructor.')->group(function () {
    Route::get('/dashboard', [InstructorController::class, 'dashboard'])->name('dashboard');
    Route::get('/courses', [InstructorController::class, 'courses'])->name('courses');
    Route::get('/courses/create', [InstructorController::class, 'createCourse'])->name('courses.create');
    Route::post('/courses', [InstructorController::class, 'storeCourse'])->name('courses.store');
    Route::get('/courses/{course}/edit', [InstructorController::class, 'editCourse'])->name('courses.edit');
    Route::put('/courses/{course}', [InstructorController::class, 'updateCourse'])->name('courses.update');
    Route::delete('/courses/{course}', [InstructorController::class, 'destroyCourse'])->name('courses.destroy');
    
    // Course content management
    Route::get('/courses/{course}/content', [InstructorController::class, 'courseContent'])->name('courses.content');
    Route::post('/courses/{course}/sections', [InstructorController::class, 'storeSection'])->name('courses.sections.store');
    Route::put('/courses/{course}/sections/{section}', [InstructorController::class, 'updateSection'])->name('courses.sections.update');
    Route::delete('/courses/{course}/sections/{section}', [InstructorController::class, 'destroySection'])->name('courses.sections.destroy');
    Route::post('/courses/{course}/sections/{section}/lessons', [InstructorController::class, 'storeLesson'])->name('courses.sections.lessons.store');
    Route::put('/courses/{course}/sections/{section}/lessons/{lesson}', [InstructorController::class, 'updateLesson'])->name('courses.lessons.update');
    Route::delete('/courses/{course}/sections/{section}/lessons/{lesson}', [InstructorController::class, 'destroyLesson'])->name('courses.lessons.destroy');
    
    // Instructor features
    Route::get('/students', [InstructorController::class, 'students'])->name('students');
    Route::get('/reviews', [InstructorController::class, 'reviews'])->name('reviews');
    Route::get('/earnings', [InstructorController::class, 'earnings'])->name('earnings');
    Route::get('/analytics', [InstructorController::class, 'analytics'])->name('analytics');
    
    // Instructor Exam Management (same as admin but only for their courses)
    Route::get('/exams', [InstructorController::class, 'examsIndex'])->name('exams.index');
    Route::get('/exams/create', [InstructorController::class, 'examsCreate'])->name('exams.create');
    Route::post('/exams', [InstructorController::class, 'examsStore'])->name('exams.store');
    Route::get('/exams/{exam}/edit', [InstructorController::class, 'examsEdit'])->name('exams.edit');
    Route::put('/exams/{exam}', [InstructorController::class, 'examsUpdate'])->name('exams.update');
    Route::post('/exams/{exam}/questions', [InstructorController::class, 'examsAddQuestion'])->name('exams.questions.add');
    Route::delete('/exams/{exam}/questions/{question}', [InstructorController::class, 'examsDeleteQuestion'])->name('exams.questions.delete');
    Route::get('/exams/{exam}/results', [InstructorController::class, 'examsResults'])->name('exams.results');
    Route::get('/exam-attempts/{attempt}/grade', [InstructorController::class, 'examsGrade'])->name('exams.grade');
    Route::post('/exam-attempts/{attempt}/grade', [InstructorController::class, 'examsSubmitGrade'])->name('exams.submit-grade');
    Route::delete('/exams/{exam}', [InstructorController::class, 'examsDestroy'])->name('exams.destroy');
});

// Admin routes (Admin Dashboard)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // User management
    Route::get('/users', [AdminController::class, 'usersIndex'])->name('users.index');
    Route::get('/users/create', [AdminController::class, 'usersCreate'])->name('users.create');
    Route::post('/users', [AdminController::class, 'usersStore'])->name('users.store');
    Route::get('/users/{user}', [AdminController::class, 'usersShow'])->name('users.show');
    Route::get('/users/{user}/edit', [AdminController::class, 'usersEdit'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'usersUpdate'])->name('users.update');
    Route::patch('/users/{user}/toggle', [AdminController::class, 'usersToggle'])->name('users.toggle');
    Route::patch('/users/{user}/approve', [AdminController::class, 'usersApprove'])->name('users.approve');
    Route::patch('/users/{user}/reject', [AdminController::class, 'usersReject'])->name('users.reject');
    Route::delete('/users/{user}', [AdminController::class, 'usersDestroy'])->name('users.destroy');
    
    // Category management
    Route::get('/categories', [AdminController::class, 'categoriesIndex'])->name('categories.index');
    Route::get('/categories/create', [AdminController::class, 'categoriesCreate'])->name('categories.create');
    Route::post('/categories', [AdminController::class, 'categoriesStore'])->name('categories.store');
    Route::get('/categories/{category}/edit', [AdminController::class, 'categoriesEdit'])->name('categories.edit');
    Route::put('/categories/{category}', [AdminController::class, 'categoriesUpdate'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminController::class, 'categoriesDestroy'])->name('categories.destroy');
    
    // Course management
    Route::get('/courses', [AdminController::class, 'coursesIndex'])->name('courses.index');
    Route::get('/courses/create', [AdminController::class, 'coursesCreate'])->name('courses.create');
    Route::post('/courses', [AdminController::class, 'coursesStore'])->name('courses.store');
    Route::get('/courses/{course}', [AdminController::class, 'coursesShow'])->name('courses.show');
    Route::get('/courses/{course}/edit', [AdminController::class, 'coursesEdit'])->name('courses.edit');
    Route::put('/courses/{course}', [AdminController::class, 'coursesUpdate'])->name('courses.update');
    Route::patch('/courses/{course}/approve', [AdminController::class, 'coursesApprove'])->name('courses.approve');
    Route::patch('/courses/{course}/archive', [AdminController::class, 'coursesArchive'])->name('courses.archive');
    Route::patch('/courses/{course}/feature', [AdminController::class, 'coursesFeature'])->name('courses.feature');
    Route::delete('/courses/{course}', [AdminController::class, 'coursesDestroy'])->name('courses.destroy');
    
    // Course content management (sections & lessons)
    Route::get('/courses/{course}/content', [AdminController::class, 'coursesContent'])->name('courses.content');
    Route::post('/courses/{course}/sections', [AdminController::class, 'coursesStoreSection'])->name('courses.sections.store');
    Route::put('/courses/{course}/sections/{section}', [AdminController::class, 'coursesUpdateSection'])->name('courses.sections.update');
    Route::delete('/courses/{course}/sections/{section}', [AdminController::class, 'coursesDestroySection'])->name('courses.sections.destroy');
    Route::post('/courses/{course}/sections/{section}/lessons', [AdminController::class, 'coursesStoreLesson'])->name('courses.sections.lessons.store');
    Route::put('/courses/{course}/sections/{section}/lessons/{lesson}', [AdminController::class, 'coursesUpdateLesson'])->name('courses.lessons.update');
    Route::delete('/courses/{course}/sections/{section}/lessons/{lesson}', [AdminController::class, 'coursesDestroyLesson'])->name('courses.lessons.destroy');
    
    // Enrollment management
    Route::get('/enrollments', [AdminController::class, 'enrollmentsIndex'])->name('enrollments.index');
    Route::patch('/enrollments/{enrollment}/refund', [AdminController::class, 'enrollmentsRefund'])->name('enrollments.refund');
    Route::patch('/enrollments/{enrollment}/approve', [AdminController::class, 'enrollmentsApprove'])->name('enrollments.approve');
    Route::patch('/enrollments/{enrollment}/reject', [AdminController::class, 'enrollmentsReject'])->name('enrollments.reject');
    
    // Review management
    Route::get('/reviews', [AdminController::class, 'reviewsIndex'])->name('reviews.index');
    Route::patch('/reviews/{review}/approve', [AdminController::class, 'reviewsApprove'])->name('reviews.approve');
    Route::delete('/reviews/{review}', [AdminController::class, 'reviewsDestroy'])->name('reviews.destroy');
    
    // Exam management
    Route::get('/exams', [AdminController::class, 'examsIndex'])->name('exams.index');
    Route::get('/exams/create', [AdminController::class, 'examsCreate'])->name('exams.create');
    Route::post('/exams', [AdminController::class, 'examsStore'])->name('exams.store');
    Route::get('/exams/{exam}/edit', [AdminController::class, 'examsEdit'])->name('exams.edit');
    Route::put('/exams/{exam}', [AdminController::class, 'examsUpdate'])->name('exams.update');
    Route::post('/exams/{exam}/questions', [AdminController::class, 'examsAddQuestion'])->name('exams.questions.add');
    Route::put('/exams/{exam}/questions/{question}', [AdminController::class, 'examsUpdateQuestion'])->name('exams.questions.update');
    Route::delete('/exams/{exam}/questions/{question}', [AdminController::class, 'examsDeleteQuestion'])->name('exams.questions.delete');
    Route::get('/exams/{exam}/results', [AdminController::class, 'examsResults'])->name('exams.results');
    Route::get('/exam-attempts/{attempt}/grade', [AdminController::class, 'examsGrade'])->name('exams.grade');
    Route::post('/exam-attempts/{attempt}/grade', [AdminController::class, 'examsSubmitGrade'])->name('exams.submit-grade');
    Route::delete('/exams/{exam}', [AdminController::class, 'examsDestroy'])->name('exams.destroy');
    
    // Contact Messages
    Route::get('/contact-messages', [AdminController::class, 'contactMessages'])->name('contact-messages.index');
    Route::get('/contact-messages/{message}', [AdminController::class, 'contactMessageShow'])->name('contact-messages.show');
    Route::post('/contact-messages/{message}/reply', [AdminController::class, 'contactMessageReply'])->name('contact-messages.reply');
    Route::delete('/contact-messages/{message}', [AdminController::class, 'contactMessageDestroy'])->name('contact-messages.destroy');
    
    // Reports & Settings
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::put('/settings', [AdminController::class, 'settingsUpdate'])->name('settings.update');
    Route::post('/cache/clear', [AdminController::class, 'cacheClear'])->name('cache.clear');
});
