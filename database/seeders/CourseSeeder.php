<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $webDevCategory = Category::where('name', 'ဝဘ်ဖွံ့ဖြိုးတိုးတက်မှု')->first();
        $mobileCategory = Category::where('name', 'မိုဘိုင်းအက်ပ်ဖွံ့ဖြိုးတိုးတက်မှု')->first();
        $dataScienceCategory = Category::where('name', 'ဒေတာသိပ္ပံ')->first();
        $designCategory = Category::where('name', 'ဒီဇိုင်း')->first();
        $businessCategory = Category::where('name', 'စီးပွားရေး')->first();

        $instructors = User::where('role', 'lecturer')->get();

        $courses = [
            [
                'title' => 'Complete Web Development Bootcamp',
                'description' => 'Learn full-stack web development from scratch. This comprehensive course covers HTML, CSS, JavaScript, React, Node.js, and MongoDB. Build real-world projects and deploy them to production.',
                'short_description' => 'Master full-stack web development with hands-on projects',
                'thumbnail' => 'courses/web-development.jpg',
                'price' => 99.99,
                'discount_price' => 79.99,
                'level' => 'beginner',
                'status' => 'published',
                'requirements' => ['Basic computer skills', 'No programming experience required'],
                'what_you_learn' => [
                    'HTML5 and CSS3 fundamentals',
                    'JavaScript ES6+ features',
                    'React.js for frontend development',
                    'Node.js and Express.js for backend',
                    'MongoDB database integration',
                    'Deployment and hosting'
                ],
                'language' => 'English',
                'duration_hours' => 40,
                'is_featured' => true,
                'category_id' => $webDevCategory->id,
                'instructor_id' => $instructors[0]->id,
            ],
            [
                'title' => 'React Native Mobile App Development',
                'description' => 'Build cross-platform mobile applications using React Native. Learn to create iOS and Android apps with a single codebase.',
                'short_description' => 'Create mobile apps for iOS and Android with React Native',
                'thumbnail' => 'courses/react-native.jpg',
                'price' => 89.99,
                'level' => 'intermediate',
                'status' => 'published',
                'requirements' => ['Basic JavaScript knowledge', 'Familiarity with React'],
                'what_you_learn' => [
                    'React Native fundamentals',
                    'Navigation and routing',
                    'State management with Redux',
                    'Native device features',
                    'App store deployment'
                ],
                'language' => 'English',
                'duration_hours' => 30,
                'is_featured' => true,
                'category_id' => $mobileCategory->id,
                'instructor_id' => $instructors[3]->id,
            ],
            [
                'title' => 'Data Science with Python',
                'description' => 'Master data science using Python. Learn data analysis, visualization, machine learning, and statistical modeling.',
                'short_description' => 'Complete data science course using Python',
                'thumbnail' => 'courses/data-science.jpg',
                'price' => 129.99,
                'discount_price' => 99.99,
                'level' => 'intermediate',
                'status' => 'published',
                'requirements' => ['Basic Python knowledge', 'High school mathematics'],
                'what_you_learn' => [
                    'Data manipulation with Pandas',
                    'Data visualization with Matplotlib',
                    'Machine learning with Scikit-learn',
                    'Statistical analysis',
                    'Real-world projects'
                ],
                'language' => 'English',
                'duration_hours' => 50,
                'is_featured' => true,
                'category_id' => $dataScienceCategory->id,
                'instructor_id' => $instructors[1]->id,
            ],
            [
                'title' => 'UI/UX Design Masterclass',
                'description' => 'Learn user interface and user experience design principles. Master design tools and create stunning digital experiences.',
                'short_description' => 'Master UI/UX design with practical projects',
                'thumbnail' => 'courses/ui-ux-design.jpg',
                'price' => 79.99,
                'level' => 'beginner',
                'status' => 'published',
                'requirements' => ['No design experience required', 'Access to design software'],
                'what_you_learn' => [
                    'Design principles and theory',
                    'User research methods',
                    'Wireframing and prototyping',
                    'Visual design skills',
                    'Design tools mastery'
                ],
                'language' => 'English',
                'duration_hours' => 35,
                'is_featured' => false,
                'category_id' => $designCategory->id,
                'instructor_id' => $instructors[2]->id,
            ],
            [
                'title' => 'Digital Marketing Strategy',
                'description' => 'Learn comprehensive digital marketing strategies including SEO, social media marketing, content marketing, and paid advertising.',
                'short_description' => 'Complete digital marketing course for businesses',
                'thumbnail' => 'courses/digital-marketing.jpg',
                'price' => 69.99,
                'level' => 'beginner',
                'status' => 'published',
                'requirements' => ['Basic computer skills', 'Interest in marketing'],
                'what_you_learn' => [
                    'SEO optimization techniques',
                    'Social media marketing',
                    'Content marketing strategy',
                    'Google Ads and Facebook Ads',
                    'Analytics and reporting'
                ],
                'language' => 'English',
                'duration_hours' => 25,
                'is_featured' => false,
                'category_id' => $businessCategory->id,
                'instructor_id' => $instructors[4]->id,
            ],
            [
                'title' => 'JavaScript Fundamentals',
                'description' => 'Master JavaScript from basics to advanced concepts. Perfect for beginners who want to learn programming.',
                'short_description' => 'Learn JavaScript programming from scratch',
                'thumbnail' => 'courses/javascript.jpg',
                'price' => 0, // Free course
                'level' => 'beginner',
                'status' => 'published',
                'requirements' => ['Basic computer skills'],
                'what_you_learn' => [
                    'JavaScript syntax and basics',
                    'Functions and objects',
                    'DOM manipulation',
                    'Event handling',
                    'Asynchronous programming'
                ],
                'language' => 'English',
                'duration_hours' => 20,
                'is_featured' => true,
                'category_id' => $webDevCategory->id,
                'instructor_id' => $instructors[0]->id,
            ],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}