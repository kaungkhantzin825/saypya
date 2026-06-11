<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\StudentApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public
Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/register', [AuthApiController::class, 'register']);
Route::get('/test-image-url', function() {
    $course = \App\Models\Course::first();
    return response()->json([
        'thumbnail' => $course->thumbnail,
        'thumbnail_url' => $course->thumbnail_url,
        'app_url' => config('app.url'),
    ]);
});
Route::get('/courses', [StudentApiController::class, 'courses']);
Route::get('/courses/{id}', [StudentApiController::class, 'courseDetail']);

// Protected (auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/me', [AuthApiController::class, 'me']);
    Route::post('/profile/update', [AuthApiController::class, 'updateProfile']);
    Route::get('/dashboard', [StudentApiController::class, 'dashboard']);
    Route::get('/my-courses', [StudentApiController::class, 'myCourses']);
    Route::get('/courses/{courseId}/lessons/{lessonId}', [StudentApiController::class, 'lessonDetail']);
    Route::post('/lessons/{lessonId}/complete', [StudentApiController::class, 'markLessonComplete']);
    Route::post('/lessons/{lessonId}/uncomplete', [StudentApiController::class, 'markLessonUncomplete']);
    Route::get('/courses/{courseId}/exams', [StudentApiController::class, 'courseExams']);
    Route::post('/exams/{examId}/start', [StudentApiController::class, 'startExam']);
    Route::post('/exam-attempts/{attemptId}/submit', [StudentApiController::class, 'submitExam']);
    Route::get('/exam-attempts/{attemptId}/result', [StudentApiController::class, 'examResult']);
    Route::get('/my-exams', [StudentApiController::class, 'myExams']);
});
