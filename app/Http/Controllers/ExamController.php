<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    // Student views available exams for a course
    public function index($courseId)
    {
        $course = \App\Models\Course::findOrFail($courseId);
        
        // Check if user is enrolled
        if (!$course->enrollments()->where('user_id', auth()->id())->where('payment_status', 'approved')->exists()) {
            return redirect()->route('courses.show', $course->slug)
                ->with('error', 'You must be enrolled in this course to view exams.');
        }

        $exams = $course->exams()->where('is_published', true)->get();

        return view('exams.index', compact('course', 'exams'));
    }

    // Student starts an exam
    public function start($examId)
    {
        $exam = Exam::with('questions')->findOrFail($examId);
        
        // Check enrollment
        if (!$exam->course->enrollments()->where('user_id', auth()->id())->where('payment_status', 'approved')->exists()) {
            return redirect()->back()->with('error', 'You must be enrolled to take this exam.');
        }

        // Check if user can attempt
        if (!$exam->canUserAttempt(auth()->id())) {
            return redirect()->back()->with('error', 'You have reached the maximum number of attempts for this exam.');
        }

        // Create new attempt
        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => auth()->id(),
            'started_at' => now(),
            'total_points' => $exam->total_points,
            'status' => 'in_progress',
        ]);

        return view('exams.take', compact('exam', 'attempt'));
    }

    // Student submits exam
    public function submit(Request $request, $attemptId)
    {
        $attempt = ExamAttempt::with('exam.questions')->findOrFail($attemptId);

        if ($attempt->user_id !== auth()->id()) {
            abort(403);
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
            return redirect()->back()->with('error', 'Error submitting exam: ' . $e->getMessage());
        }
    }

    // Student views exam result
    public function result($attemptId)
    {
        $attempt = ExamAttempt::with(['exam.questions', 'answers.question'])->findOrFail($attemptId);

        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }

        return view('exams.result', compact('attempt'));
    }

    // Student views their exam history
    public function myExams()
    {
        $attempts = ExamAttempt::with('exam.course')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('exams.my-exams', compact('attempts'));
    }
}
