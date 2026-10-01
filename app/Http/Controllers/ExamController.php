<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ExamController extends Controller
{
    // Student views available exams for a course
    public function index($courseId): Response|\Illuminate\Http\RedirectResponse
    {
        $course = \App\Models\Course::findOrFail($courseId);

        // Check if user is enrolled
        if (!$course->enrollments()->where('user_id', auth()->id())->where('payment_status', 'completed')->exists()) {
            return redirect()->route('courses.show', $course->slug)
                ->with('error', 'You must be enrolled in this course to view exams.');
        }

        $exams = $course->exams()
            ->where('is_published', true)
            ->withCount('questions')
            ->get();

        // Attach the learner's outcome per exam so the cards can show a badge:
        //   true  -> at least one graded attempt passed
        //   false -> attempts exist but none passed
        //   null  -> never attempted
        $attemptsByExam = ExamAttempt::where('user_id', auth()->id())
            ->whereIn('exam_id', $exams->pluck('id'))
            ->whereIn('status', ['submitted', 'graded'])
            ->orderByDesc('id')
            ->get()
            ->groupBy('exam_id');

        $exams->each(function (Exam $exam) use ($attemptsByExam) {
            $attempts = $attemptsByExam->get($exam->id);

            $exam->passed = $attempts
                ? $attempts->contains(fn (ExamAttempt $attempt) => $attempt->passed === true)
                : null;
        });

        $count = $exams->count();

        return Inertia::render('Exams/Index', [
            'course' => $course,
            'exams' => $exams,
            'title' => 'Exams',
            'description' => $count
                ? "{$count} published " . ($count === 1 ? 'exam' : 'exams') . " for {$course->title}."
                : "No published exams for {$course->title} yet.",
        ]);
    }

    // Student starts an exam
    public function start($examId): Response|\Illuminate\Http\RedirectResponse
    {
        $exam = Exam::with('questions')->findOrFail($examId);

        // Check enrollment
        if (!$exam->course->enrollments()->where('user_id', auth()->id())->where('payment_status', 'completed')->exists()) {
            return back()->with('error', 'You must be enrolled to take this exam.');
        }

        // Resume an unfinished in-progress attempt instead of creating a new one
        $existing = $exam->getInProgressAttempt(auth()->id());
        if ($existing) {
            return Inertia::render('Exams/Take', [
                'exam' => $exam,
                'attempt' => $existing,
            ]);
        }

        // Check if user can attempt (only counts submitted/graded)
        if (!$exam->canUserAttempt(auth()->id())) {
            return back()->with('error', 'You have reached the maximum number of attempts for this exam.');
        }

        // Create new attempt
        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => auth()->id(),
            'started_at' => now(),
            'total_points' => $exam->total_points,
            'status' => 'in_progress',
        ]);

        return Inertia::render('Exams/Take', [
            'exam' => $exam,
            'attempt' => $attempt,
        ]);
    }

    // Student submits exam
    public function submit(Request $request, $attemptId): \Illuminate\Http\RedirectResponse
    {
        $attempt = ExamAttempt::with('exam.questions')->findOrFail($attemptId);

        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }

        // Guard against double-submission (page refresh after submit)
        if ($attempt->status !== 'in_progress') {
            return redirect()->route('exams.result', $attempt->id)
                ->with('info', 'This exam has already been submitted.');
        }

        DB::beginTransaction();
        try {
            $totalScore = 0;
            $needsManualGrading = false;

            foreach ($attempt->exam->questions as $question) {
                $answer = $request->input('question_' . $question->id);

                $examAnswer = ExamAnswer::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'answer' => $answer ?? '',
                ]);

                // Auto-grade MCQ and True/False
                if (in_array($question->type, ['multiple_choice', 'true_false'])) {
                    $isCorrect = $question->isCorrect($answer);
                    $pointsEarned = $isCorrect ? $question->points : 0;

                    $examAnswer->update([
                        'is_correct' => $isCorrect,
                        'points_earned' => $pointsEarned,
                    ]);

                    $totalScore += $pointsEarned;
                } else {
                    // Essay questions need manual grading
                    $needsManualGrading = true;
                }
            }

            $percentage = ($attempt->total_points > 0) ? ($totalScore / $attempt->total_points) * 100 : 0;

            $attempt->update([
                'submitted_at' => now(),
                'score' => $totalScore,
                'passed' => $percentage >= $attempt->exam->passing_score,
                'status' => $needsManualGrading ? 'submitted' : 'graded',
            ]);

            DB::commit();

            return redirect()->route('exams.result', $attempt->id)
                ->with('success', 'Exam submitted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error submitting exam: ' . $e->getMessage());
        }
    }

    // Student views exam result
    public function result($attemptId): Response
    {
        $attempt = ExamAttempt::with(['exam.course', 'exam.questions', 'answers.question'])
            ->findOrFail($attemptId);

        // Allow if user owns the attempt OR user is admin/instructor
        if ($attempt->user_id !== auth()->id() && !in_array(auth()->user()->role, ['admin', 'instructor'])) {
            abort(403);
        }

        // Retake eligibility, surfaced on the exam payload for the result page.
        if ($attempt->exam) {
            $used = $attempt->exam->userAttempts($attempt->user_id);

            $attempt->exam->attempts_used = $used;
            $attempt->exam->attempts_remaining = max(0, $attempt->exam->max_attempts - $used);
        }

        return Inertia::render('Exams/Result', [
            'attempt' => $attempt,
            'title' => 'Exam result',
            'description' => $attempt->exam?->title ?? 'Exam attempt result',
        ]);
    }

    // Student views their exam history
    public function myExams(): Response
    {
        $attempts = ExamAttempt::with('exam.course')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return Inertia::render('Exams/MyExams', [
            'attempts' => $attempts,
            'title' => 'My exams',
            'description' => 'Every exam attempt you have started, with scores and status.',
        ]);
    }
}
