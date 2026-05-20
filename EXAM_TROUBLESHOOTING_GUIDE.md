# Exam System Troubleshooting Guide

## Why Students Can't See Exams

For an exam to be visible to students, **ALL** of these conditions must be met:

### 1. ✅ Exam Must Be Published
- Go to: `Admin Panel → Exams → Edit Exam`
- Check the **"Published"** checkbox at the bottom
- Click **"Update Settings"**
- You'll see a green "Published" badge at the top when it's published

### 2. ✅ Exam Must Have Questions
- Go to: `Admin Panel → Exams → Edit Exam`
- Click **"Add Question"** button
- Add at least one question
- Questions can be: Multiple Choice, True/False, or Essay

### 3. ✅ Student Must Complete 100% of Course
- Students must complete ALL lessons in the course
- Each lesson must be marked as "Done"
- Progress is shown in the course sidebar
- Only when progress reaches 100% will exams appear

---

## Step-by-Step: Creating an Exam

### Step 1: Create the Exam
1. Go to `Admin Panel → Exams`
2. Click **"Create New Exam"**
3. Fill in:
   - Course (select from dropdown)
   - Exam Title
   - Description (optional)
   - Duration (minutes) - leave empty for unlimited
   - Passing Score (default: 70%)
   - Max Attempts (default: 1)
4. Click **"Create Exam"**

### Step 2: Add Questions
1. You'll be redirected to the Edit Exam page
2. Click **"Add Question"** button
3. Select question type:
   - **Multiple Choice**: Add options and select correct answer
   - **True/False**: Select correct answer
   - **Essay**: Manual grading required
4. Enter question text and points
5. Click **"Add Question"**
6. Repeat to add more questions

### Step 3: Publish the Exam
1. Scroll down to "Exam Settings"
2. Check the **"Published"** checkbox
3. Click **"Update Settings"**
4. Verify the green "Published" badge appears at the top

---

## How Students Take Exams

### Student View:
1. Student enrolls in course (payment must be approved)
2. Student completes all lessons (100% progress)
3. Exam appears in the course sidebar with yellow background
4. Student clicks on exam to start
5. Student answers all questions
6. Student submits exam
7. Results shown immediately (if enabled)

### Exam Results:
- **Multiple Choice & True/False**: Auto-graded instantly
- **Essay Questions**: Require manual grading by admin/instructor
- Students see if they passed based on passing score
- If passed, they can get a certificate via Viber: +95 9695238273

---

## Common Issues & Solutions

### Issue: "I created an exam but students can't see it"
**Solution:**
1. Check if exam is published (Edit Exam page)
2. Check if exam has questions (must have at least 1)
3. Check if student completed 100% of course
4. Check if student's enrollment is approved (payment_status = 'completed')

### Issue: "Add Question button doesn't work"
**Solution:**
1. Make sure you're on the Edit Exam page (not Create page)
2. Clear browser cache and refresh
3. Check browser console for JavaScript errors
4. Verify modal appears when clicking "Add Question"

### Issue: "Student completed course but exam doesn't show"
**Solution:**
1. Verify progress is exactly 100% (check enrollment table)
2. Verify exam is published (is_published = 1)
3. Verify exam has questions (questions count > 0)
4. Check if exam belongs to the correct course

### Issue: "Exam shows but student can't start it"
**Solution:**
1. Check if student has remaining attempts
2. Check if previous attempt is still "in_progress"
3. Verify exam has questions
4. Check browser console for errors

---

## Database Check (For Developers)

### Check if exam is published:
```sql
SELECT id, title, is_published, course_id 
FROM exams 
WHERE id = YOUR_EXAM_ID;
```

### Check if exam has questions:
```sql
SELECT COUNT(*) as question_count 
FROM exam_questions 
WHERE exam_id = YOUR_EXAM_ID;
```

### Check student progress:
```sql
SELECT user_id, course_id, progress_percentage, payment_status 
FROM enrollments 
WHERE user_id = YOUR_USER_ID AND course_id = YOUR_COURSE_ID;
```

### Check exam attempts:
```sql
SELECT * FROM exam_attempts 
WHERE exam_id = YOUR_EXAM_ID AND user_id = YOUR_USER_ID;
```

---

## Visual Indicators

### Admin Exam Edit Page:
- **Green Badge**: "Published" - Students can see this exam
- **Yellow Badge**: "Not Published" - Students cannot see this exam
- **Yellow Alert**: "No questions added yet" - Add questions first
- **Blue Alert**: "Exam is not published" - Check the Published checkbox

### Student Course Page:
- **Yellow Section**: Exam available - Click to start
- **Blue Section**: Exam locked - Complete course first or exam not published
- **Green Badge**: "Get Certificate" - Student passed the exam

---

## Testing Checklist

Before expecting students to see an exam:

- [ ] Exam is created
- [ ] Exam has at least 1 question
- [ ] Exam is published (checkbox checked)
- [ ] Course has lessons
- [ ] Student is enrolled
- [ ] Student's enrollment is approved (payment_status = 'completed')
- [ ] Student completed 100% of lessons
- [ ] Browser cache cleared

---

## Contact

For certificate requests after passing exams:
**Viber**: +95 9695238273

For technical support:
**Email**: webdeveloperkkz@gmail.com
