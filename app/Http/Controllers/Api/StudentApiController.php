<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Category;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentApiController extends Controller
{
    // ─────────────────────────────────────────────────────────
    //  DASHBOARD
    // ─────────────────────────────────────────────────────────

    public function dashboard(Request $request)
    {
        $user = $request->user();

        $enrollments = Enrollment::where('user_id', $user->id)
            ->where('payment_status', 'completed')
            ->with(['course.category', 'course.instructor'])
            ->get();

        $totalEnrolled   = $enrollments->count();
        $completed       = $enrollments->where('progress_percentage', 100)->count();
        $inProgress      = $enrollments->where('progress_percentage', '>', 0)
                                       ->where('progress_percentage', '<', 100)->count();
        $examAttempts    = ExamAttempt::where('user_id', $user->id)->count();
        $passedExams     = ExamAttempt::where('user_id', $user->id)->where('passed', true)->count();

        // Recent courses (last 3)
        $recentCourses = $enrollments->sortByDesc('last_accessed_at')
            ->take(3)
            ->map(fn($e) => $this->formatEnrollment($e))
            ->values();

        // Featured courses not yet enrolled
        $enrolledIds = $enrollments->pluck('course_id')->toArray();
        $featured = Course::published()
            ->featured()
            ->whereNotIn('id', $enrolledIds)
            ->with(['category', 'instructor'])
            ->take(4)
            ->get()
            ->map(fn($c) => $this->formatCourse($c, $user->id));

        return response()->json([
            'success' => true,
            'stats' => [
                'total_enrolled'  => $totalEnrolled,
                'completed'       => $completed,
                'in_progress'     => $inProgress,
                'exam_attempts'   => $examAttempts,
                'passed_exams'    => $passedExams,
            ],
            'recent_courses'  => $recentCourses,
            'featured_courses' => $featured,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    //  COURSES
    // ─────────────────────────────────────────────────────────

    public function courses(Request $request)
    {
        $user     = auth('sanctum')->user();
        $userId   = $user ? $user->id : null;
        $query    = Course::published()->with(['category', 'instructor', 'reviews']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) =>
                $q->where('title', 'like', "%$s%")
                  ->orWhere('description', 'like', "%$s%")
            );
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'popular' => $query->withCount('enrollments')->orderByDesc('enrollments_count'),
            'rating'  => $query->withAvg('reviews', 'rating')->orderByDesc('reviews_avg_rating'),
            'price_low'  => $query->orderBy('price'),
            'price_high' => $query->orderByDesc('price'),
            default      => $query->orderByDesc('created_at'),
        };

        $courses    = $query->paginate($request->get('per_page', 12));
        $categories = Category::active()->ordered()->get()->map(fn($c) => [
            'id'   => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'icon' => $c->icon,
        ]);

        return response()->json([
            'success'    => true,
            'categories' => $categories,
            'courses'    => [
                'data'          => collect($courses->items())->map(fn($c) => $this->formatCourse($c, $userId)),
                'current_page'  => $courses->currentPage(),
                'last_page'     => $courses->lastPage(),
                'total'         => $courses->total(),
            ],
        ]);
    }

    public function courseDetail(Request $request, $id)
    {
        $user   = auth('sanctum')->user();
        $course = Course::published()
            ->with(['category', 'instructor', 'sections.lessons', 'reviews.user'])
            ->findOrFail($id);

        $enrollment = $user
            ? Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first()
            : null;

        $isEnrolled    = $enrollment && $enrollment->payment_status === 'completed';
        $isPending     = $enrollment && $enrollment->payment_status === 'pending';

        // Build sections with lesson progress if enrolled
        $sections = $course->sections->map(function ($section) use ($user, $isEnrolled) {
            return [
                'id'         => $section->id,
                'title'      => $section->title,
                'sort_order' => $section->sort_order,
                'lessons'    => $section->lessons->map(function ($lesson) use ($user, $isEnrolled) {
                    $completed = ($user && $isEnrolled)
                        ? LessonProgress::where('user_id', $user->id)
                            ->where('lesson_id', $lesson->id)
                            ->where('is_completed', true)->exists()
                        : false;
                    return [
                        'id'               => $lesson->id,
                        'title'            => $lesson->title,
                        'type'             => $lesson->type ?? 'video',
                        'video_duration'   => $lesson->video_duration,
                        'formatted_duration' => $lesson->formatted_duration,
                        'is_preview'       => $lesson->is_preview,
                        'is_completed'     => $completed,
                        'sort_order'       => $lesson->sort_order,
                    ];
                }),
            ];
        });

        // Reviews
        $reviews = $course->reviews->take(5)->map(fn($r) => [
            'id'         => $r->id,
            'rating'     => $r->rating,
            'review'     => $r->review,
            'user_name'  => $r->user->name ?? 'Anonymous',
            'avatar_url' => $r->user->avatar_url ?? null,
            'created_at' => $r->created_at?->diffForHumans(),
        ]);

        return response()->json([
            'success' => true,
            'course'  => [
                ...$this->formatCourse($course, $user ? $user->id : null),
                'description'      => $course->description,
                'requirements'     => $course->requirements ?? [],
                'what_you_learn'   => $course->what_you_learn ?? [],
                'sections'         => $sections,
                'reviews'          => $reviews,
                'total_reviews'    => $course->total_reviews,
                'average_rating'   => round($course->average_rating, 1),
                'total_lessons'    => $course->total_lessons,
                'total_duration'   => $course->total_duration,
                'is_enrolled'      => $isEnrolled,
                'is_pending'       => $isPending,
                'progress'         => $enrollment?->progress_percentage ?? 0,
            ],
        ]);
    }

    public function myCourses(Request $request)
    {
        $user = $request->user();

        $enrollments = Enrollment::where('user_id', $user->id)
            ->where('payment_status', 'completed')
            ->with(['course.category', 'course.instructor'])
            ->orderByDesc('last_accessed_at')
            ->get();

        return response()->json([
            'success' => true,
            'courses' => $enrollments->map(fn($e) => $this->formatEnrollment($e)),
        ]);
    }

    // ─────────────────────────────────────────────────────────
    //  LESSONS
    // ─────────────────────────────────────────────────────────

    public function lessonDetail(Request $request, $courseId, $lessonId)
    {
        $user   = $request->user();
        $course = Course::findOrFail($courseId);

        // Must be enrolled
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->where('payment_status', 'completed')
            ->firstOrFail();

        $lesson = Lesson::where('section_id', function ($q) use ($courseId) {
            $q->select('id')->from('sections')->where('course_id', $courseId);
        })->findOrFail($lessonId);

        $progress = LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        return response()->json([
            'success' => true,
            'lesson'  => [
                'id'                => $lesson->id,
                'title'             => $lesson->title,
                'description'       => $lesson->description,
                'type'              => $lesson->type ?? 'video',
                'video_url'         => $lesson->video_url,
                'youtube_embed_url' => $lesson->youtube_embed_url,
                'video_url_full'    => $lesson->video_url_full,
                'content'           => $lesson->content,
                'video_duration'    => $lesson->video_duration,
                'formatted_duration'=> $lesson->formatted_duration,
                'is_preview'        => $lesson->is_preview,
                'is_completed'      => $progress?->is_completed ?? false,
            ],
            'enrollment_progress' => $enrollment->progress_percentage,
        ]);
    }

    public function markLessonComplete(Request $request, $lessonId)
    {
        $user   = $request->user();
        $lesson = Lesson::findOrFail($lessonId);

        // Verify enrollment
        $courseId = DB::table('sections')
            ->where('id', $lesson->section_id)
            ->value('course_id');

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->where('payment_status', 'completed')
            ->firstOrFail();

        LessonProgress::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['is_completed' => true, 'completed_at' => now()]
        );

        $enrollment->updateProgress();

        return response()->json([
            'success'  => true,
            'message'  => 'Lesson marked as complete.',
            'progress' => $enrollment->fresh()->progress_percentage,
        ]);
    }

    public function markLessonUncomplete(Request $request, $lessonId)
    {
        $user   = $request->user();
        $lesson = Lesson::findOrFail($lessonId);

        LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->update(['is_completed' => false, 'completed_at' => null]);

        $courseId = DB::table('sections')
            ->where('id', $lesson->section_id)
            ->value('course_id');

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->where('payment_status', 'completed')
            ->first();

        $enrollment?->updateProgress();

        return response()->json([
            'success'  => true,
            'message'  => 'Lesson marked as incomplete.',
            'progress' => $enrollment?->fresh()->progress_percentage ?? 0,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    //  EXAMS
    // ─────────────────────────────────────────────────────────

    public function courseExams(Request $request, $courseId)
    {
        $user   = $request->user();
        $course = Course::findOrFail($courseId);

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->where('payment_status', 'completed')
            ->firstOrFail();

        $exams = Exam::where('course_id', $courseId)
            ->where('is_published', true)
            ->withCount('questions')
            ->get()
            ->map(function ($exam) use ($user, $enrollment) {
                $attempts = ExamAttempt::where('exam_id', $exam->id)
                    ->where('user_id', $user->id)
                    ->latest()
                    ->get();
                $bestAttempt = $attempts->where('status', 'graded')->sortByDesc('score')->first();

                return [
                    'id'              => $exam->id,
                    'title'           => $exam->title,
                    'description'     => $exam->description,
                    'duration_minutes'=> $exam->duration_minutes,
                    'passing_score'   => $exam->passing_score,
                    'max_attempts'    => $exam->max_attempts,
                    'questions_count' => $exam->questions_count,
                    'total_points'    => $exam->total_points,
                    'attempts_used'   => $attempts->count(),
                    'can_attempt'     => $exam->canUserAttempt($user->id),
                    'is_unlocked'     => $enrollment->progress_percentage >= 100,
                    'best_score'      => $bestAttempt?->percentage ?? null,
                    'passed'          => $bestAttempt?->passed ?? false,
                ];
            });

        return response()->json([
            'success'  => true,
            'progress' => $enrollment->progress_percentage,
            'exams'    => $exams,
        ]);
    }

    public function startExam(Request $request, $examId)
    {
        $user = $request->user();
        $exam = Exam::with('questions')->findOrFail($examId);

        // Guard: enrollment + 100% progress
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $exam->course_id)
            ->where('payment_status', 'completed')
            ->firstOrFail();

        if ($enrollment->progress_percentage < 100) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete all lessons before taking the exam.',
            ], 403);
        }

        if (!$exam->canUserAttempt($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You have reached the maximum number of attempts.',
            ], 403);
        }

        $attempt = ExamAttempt::create([
            'exam_id'      => $exam->id,
            'user_id'      => $user->id,
            'started_at'   => now(),
            'total_points' => $exam->total_points,
            'status'       => 'in_progress',
        ]);

        $questions = $exam->questions->map(fn($q) => [
            'id'       => $q->id,
            'question' => $q->question,
            'type'     => $q->type,
            'points'   => $q->points,
            'order'    => $q->order,
            'options'  => $q->options ?? [],
        ]);

        return response()->json([
            'success'   => true,
            'attempt_id'=> $attempt->id,
            'exam'      => [
                'id'               => $exam->id,
                'title'            => $exam->title,
                'duration_minutes' => $exam->duration_minutes,
                'passing_score'    => $exam->passing_score,
            ],
            'questions' => $questions,
        ]);
    }

    public function submitExam(Request $request, $attemptId)
    {
        $user    = $request->user();
        $attempt = ExamAttempt::with('exam.questions')->findOrFail($attemptId);

        if ($attempt->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        if ($attempt->status !== 'in_progress') {
            return response()->json(['success' => false, 'message' => 'This exam has already been submitted.'], 422);
        }

        DB::beginTransaction();
        try {
            $totalScore        = 0;
            $needsManualGrading = false;

            foreach ($attempt->exam->questions as $question) {
                $answer = $request->input('answers.' . $question->id);

                $examAnswer = ExamAnswer::create([
                    'attempt_id'  => $attempt->id,
                    'question_id' => $question->id,
                    'answer'      => $answer ?? '',
                ]);

                if (in_array($question->type, ['multiple_choice', 'true_false'])) {
                    $isCorrect    = $question->isCorrect($answer);
                    $pointsEarned = $isCorrect ? $question->points : 0;
                    $examAnswer->update([
                        'is_correct'    => $isCorrect,
                        'points_earned' => $pointsEarned,
                    ]);
                    $totalScore += $pointsEarned;
                } else {
                    $needsManualGrading = true;
                }
            }

            $percentage = $attempt->total_points > 0
                ? ($totalScore / $attempt->total_points) * 100 : 0;

            $attempt->update([
                'submitted_at' => now(),
                'score'        => $totalScore,
                'passed'       => $percentage >= $attempt->exam->passing_score,
                'status'       => $needsManualGrading ? 'submitted' : 'graded',
            ]);

            DB::commit();

            return response()->json([
                'success'    => true,
                'message'    => 'Exam submitted successfully!',
                'attempt_id' => $attempt->id,
                'result'     => [
                    'score'           => $totalScore,
                    'total_points'    => $attempt->total_points,
                    'percentage'      => round($percentage, 1),
                    'passed'          => $attempt->fresh()->passed,
                    'passing_score'   => $attempt->exam->passing_score,
                    'needs_grading'   => $needsManualGrading,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error submitting exam: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function examResult(Request $request, $attemptId)
    {
        $user    = $request->user();
        $attempt = ExamAttempt::with(['exam', 'answers.question'])->findOrFail($attemptId);

        if ($attempt->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        $answers = $attempt->answers->map(fn($a) => [
            'question'      => $a->question->question,
            'type'          => $a->question->type,
            'your_answer'   => $a->answer,
            'correct_answer'=> $attempt->exam->show_correct_answers ? $a->question->correct_answer : null,
            'is_correct'    => $a->is_correct,
            'points_earned' => $a->points_earned,
            'max_points'    => $a->question->points,
        ]);

        return response()->json([
            'success' => true,
            'result'  => [
                'exam_title'    => $attempt->exam->title,
                'score'         => $attempt->score,
                'total_points'  => $attempt->total_points,
                'percentage'    => $attempt->percentage,
                'passed'        => $attempt->passed,
                'passing_score' => $attempt->exam->passing_score,
                'status'        => $attempt->status,
                'submitted_at'  => $attempt->submitted_at?->format('Y-m-d H:i'),
                'answers'       => $attempt->exam->show_results ? $answers : [],
            ],
        ]);
    }

    public function myExams(Request $request)
    {
        $user = $request->user();

        $attempts = ExamAttempt::with('exam.course')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return response()->json([
            'success'  => true,
            'attempts' => collect($attempts->items())->map(fn($a) => [
                'id'           => $a->id,
                'exam_title'   => $a->exam->title,
                'course_title' => $a->exam->course->title ?? 'N/A',
                'score'        => $a->score,
                'total_points' => $a->total_points,
                'percentage'   => $a->percentage,
                'passed'       => $a->passed,
                'status'       => $a->status,
                'submitted_at' => $a->submitted_at?->format('Y-m-d H:i'),
            ]),
            'current_page' => $attempts->currentPage(),
            'last_page'    => $attempts->lastPage(),
        ]);
    }

    // ─────────────────────────────────────────────────────────
    //  HELPERS
    // ─────────────────────────────────────────────────────────

    private function formatCourse(Course $course, ?int $userId = null): array
    {
        // Ensure full URL for thumbnail - HARDCODED FIX
        $thumbnailUrl = $course->thumbnail;
        if ($thumbnailUrl) {
            if (str_starts_with($thumbnailUrl, 'http')) {
                // Already full URL
                $thumbnailUrl = $thumbnailUrl;
            } else {
                // Build full URL
                $path = ltrim($thumbnailUrl, '/');
                if (str_starts_with($path, 'storage/')) {
                    $thumbnailUrl = 'https://sanpyalearning.com/' . $path;
                } else {
                    $thumbnailUrl = 'https://sanpyalearning.com/storage/' . $path;
                }
            }
        } else {
            $thumbnailUrl = 'https://placehold.co/400x300/3498db/ffffff?text=Course';
        }
        
        // Ensure full URL for avatar - HARDCODED FIX
        $avatar = $course->instructor?->avatar;
        if ($avatar) {
            if (str_starts_with($avatar, 'http')) {
                $avatarUrl = $avatar;
            } else {
                $path = ltrim($avatar, '/');
                if (str_starts_with($path, 'storage/')) {
                    $avatarUrl = 'https://sanpyalearning.com/' . $path;
                } else {
                    $avatarUrl = 'https://sanpyalearning.com/storage/' . $path;
                }
            }
        } else {
            $instructorName = $course->instructor?->name ?? 'User';
            $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($instructorName) . '&background=0d9488&color=fff&size=200';
        }
        
        return [
            'id'                  => $course->id,
            'title'               => $course->title,
            'slug'                => $course->slug,
            'short_description'   => $course->short_description,
            'thumbnail_url'       => $thumbnailUrl,
            'price'               => $course->price,
            'discount_price'      => $course->discount_price,
            'current_price'       => $course->current_price,
            'discount_percentage' => $course->discount_percentage,
            'level'               => $course->level,
            'language'            => $course->language,
            'is_featured'         => $course->is_featured,
            'is_free'             => $course->isFree(),
            'average_rating'      => round($course->average_rating, 1),
            'total_students'      => $course->total_students,
            'total_lessons'       => $course->total_lessons,
            'category'            => [
                'id'   => $course->category?->id,
                'name' => $course->category?->name,
            ],
            'instructor' => [
                'id'         => $course->instructor?->id,
                'name'       => $course->instructor?->name,
                'avatar_url' => $avatarUrl,
            ],
        ];
    }

    private function formatEnrollment(Enrollment $enrollment): array
    {
        $course = $enrollment->course;
        return [
            ...$this->formatCourse($course, $enrollment->user_id),
            'enrollment_id'       => $enrollment->id,
            'progress_percentage' => $enrollment->progress_percentage,
            'payment_status'      => $enrollment->payment_status,
            'enrolled_at'         => $enrollment->enrolled_at?->format('Y-m-d'),
            'last_accessed_at'    => $enrollment->last_accessed_at?->diffForHumans(),
        ];
    }
}
