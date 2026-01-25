# Exam/Quiz System Requirements

## Feature Overview
An exam and quiz system that allows admins and instructors to create assessments for courses, and students to take exams and view their results. The system supports multiple question types including multiple choice questions (MCQ) with automatic grading and essay/writing questions with manual grading.

## User Stories

### As an Admin
1. I want to create exams for any course so that I can assess student learning
2. I want to assign exams to specific courses or students so that I can control who takes which exam
3. I want to view all exam results across the platform so that I can monitor overall performance
4. I want to manually grade essay questions so that I can provide detailed feedback
5. I want to set exam parameters (time limit, passing score, attempts allowed) so that I can control exam conditions
6. I want to publish or unpublish exams so that I can control when students can access them

### As an Instructor
1. I want to create exams for my courses so that I can assess my students
2. I want to add different question types (MCQ, essay, true/false) so that I can test various skills
3. I want to set correct answers for MCQ questions so that they can be auto-graded
4. I want to manually grade essay questions from my students so that I can provide personalized feedback
5. I want to view exam results for my courses so that I can track student progress
6. I want to edit or delete my exams so that I can keep content up to date

### As a Student
1. I want to see available exams for my enrolled courses so that I know what assessments I need to complete
2. I want to take exams with a clear interface so that I can focus on answering questions
3. I want to see a timer during timed exams so that I can manage my time
4. I want to view my exam results after completion so that I can see my performance
5. I want to see which questions I got right or wrong (if enabled) so that I can learn from mistakes
6. I want to see my exam history so that I can track my progress

## Acceptance Criteria

### 1. Exam Creation
- [ ] 1.1 Admins can create exams for any course
- [ ] 1.2 Instructors can create exams only for their own courses
- [ ] 1.3 Exam form includes: title, description, course selection, time limit (optional), passing score, max attempts, show results option
- [ ] 1.4 Exams can be saved as draft or published
- [ ] 1.5 Published exams are visible to enrolled students

### 2. Question Management
- [ ] 2.1 Support for Multiple Choice Questions (MCQ) with 2-6 options
- [ ] 2.2 Support for Essay/Writing questions with text area
- [ ] 2.3 Support for True/False questions
- [ ] 2.4 Each question has: question text, question type, points/marks, correct answer (for MCQ/True-False)
- [ ] 2.5 Questions can be reordered within an exam
- [ ] 2.6 Questions can be edited or deleted before exam is taken
- [ ] 2.7 Minimum 1 question required per exam

### 3. Taking Exams
- [ ] 3.1 Students can only access exams for courses they are enrolled in
- [ ] 3.2 Students can only take published exams
- [ ] 3.3 Timer displays for timed exams and auto-submits when time expires
- [ ] 3.4 Students can navigate between questions
- [ ] 3.5 Students can save progress and resume later (if allowed)
- [ ] 3.6 Students must confirm before submitting exam
- [ ] 3.7 Students cannot retake exam if max attempts reached

### 4. Automatic Grading
- [ ] 4.1 MCQ questions are automatically graded upon submission
- [ ] 4.2 True/False questions are automatically graded upon submission
- [ ] 4.3 Total score is calculated for auto-graded questions
- [ ] 4.4 Pass/Fail status is determined based on passing score

### 5. Manual Grading
- [ ] 5.1 Essay questions show as "Pending Review" until graded
- [ ] 5.2 Instructors can view all essay answers for their courses
- [ ] 5.3 Admins can view all essay answers across platform
- [ ] 5.4 Graders can assign points and provide feedback for each essay question
- [ ] 5.5 Final score is updated after all manual grading is complete
- [ ] 5.6 Students are notified when manual grading is complete

### 6. Results Display
- [ ] 6.1 Students can view their score immediately after submission (for auto-graded questions)
- [ ] 6.2 Students can see "Pending Review" status for essay questions
- [ ] 6.3 If "show correct answers" is enabled, students can see which questions they got right/wrong
- [ ] 6.4 Students can see their exam history with dates and scores
- [ ] 6.5 Instructors can view all student results for their exams
- [ ] 6.6 Admins can view all exam results across the platform

### 7. Exam Settings
- [ ] 7.1 Time limit can be set (in minutes) or left unlimited
- [ ] 7.2 Passing score can be set as percentage (e.g., 70%)
- [ ] 7.3 Maximum attempts can be set (1-10 or unlimited)
- [ ] 7.4 "Show results immediately" option can be toggled
- [ ] 7.5 "Show correct answers" option can be toggled
- [ ] 7.6 "Allow resume" option can be toggled

### 8. Security & Validation
- [ ] 8.1 Students cannot view exam questions without starting the exam
- [ ] 8.2 Students cannot access other students' exam results
- [ ] 8.3 Instructors cannot access exams from other instructors' courses
- [ ] 8.4 All exam submissions are timestamped
- [ ] 8.5 Exam attempts are tracked and enforced

## Technical Requirements

### Database Tables Needed
1. **exams** - Store exam metadata
2. **exam_questions** - Store questions for each exam
3. **exam_question_options** - Store options for MCQ questions
4. **exam_attempts** - Store student exam attempts
5. **exam_answers** - Store student answers for each question

### Key Relationships
- Exam belongs to Course
- Exam has many Questions
- Question has many Options (for MCQ)
- Student has many Exam Attempts
- Exam Attempt has many Answers

### Permissions
- Admin: Full access to all exams
- Instructor: Access to exams for their courses only
- Student: Access to take exams for enrolled courses, view own results

## Out of Scope (Future Enhancements)
- Question bank/library for reusing questions
- Randomized question order
- Question pools (random selection from pool)
- File upload questions
- Matching questions
- Fill-in-the-blank questions
- Exam analytics and statistics
- Plagiarism detection
- Proctoring features
- Certificate generation upon passing

## Success Metrics
- Admins and instructors can successfully create exams with multiple question types
- Students can take exams without technical issues
- MCQ questions are automatically graded correctly
- Instructors can manually grade essay questions efficiently
- Students can view their results and learn from feedback
