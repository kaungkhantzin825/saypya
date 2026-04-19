<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;
use App\Models\Course;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // Get some students and courses
        $students = User::where('role', 'student')->take(5)->get();
        $courses = Course::take(3)->get();

        if ($students->isEmpty() || $courses->isEmpty()) {
            $this->command->warn('No students or courses found. Please run UserSeeder and CourseSeeder first.');
            return;
        }

        $reviews = [
            [
                'rating' => 5,
                'comment' => 'Excellent course! Very well explained and easy to follow. The instructor is knowledgeable and engaging.',
                'is_approved' => true,
            ],
            [
                'rating' => 4,
                'comment' => 'Great content and good examples. Would recommend to anyone wanting to learn this topic.',
                'is_approved' => true,
            ],
            [
                'rating' => 5,
                'comment' => 'Best course I have taken so far. Clear explanations and practical exercises.',
                'is_approved' => true,
            ],
            [
                'rating' => 3,
                'comment' => 'Good course but could use more examples and practice exercises.',
                'is_approved' => false,
            ],
            [
                'rating' => 4,
                'comment' => 'Very informative and well-structured. The pace is perfect for beginners.',
                'is_approved' => true,
            ],
            [
                'rating' => 5,
                'comment' => 'Outstanding! This course exceeded my expectations. Highly recommended!',
                'is_approved' => true,
            ],
            [
                'rating' => 4,
                'comment' => 'Solid course with good content. The instructor explains concepts clearly.',
                'is_approved' => true,
            ],
            [
                'rating' => 2,
                'comment' => 'The course is okay but needs improvement in some areas.',
                'is_approved' => false,
            ],
        ];

        foreach ($students as $index => $student) {
            foreach ($courses as $courseIndex => $course) {
                // Create 1-2 reviews per student
                if ($index + $courseIndex < count($reviews)) {
                    $reviewData = $reviews[$index + $courseIndex];
                    
                    Review::create([
                        'user_id' => $student->id,
                        'course_id' => $course->id,
                        'rating' => $reviewData['rating'],
                        'comment' => $reviewData['comment'],
                        'is_approved' => $reviewData['is_approved'],
                    ]);
                }
            }
        }

        $this->command->info('Reviews seeded successfully!');
    }
}
