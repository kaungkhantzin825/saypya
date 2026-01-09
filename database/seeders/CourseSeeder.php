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
        $categories = Category::all()->keyBy('name');
        $instructors = User::where('role', 'lecturer')->get();

        $courses = [
            [
                'title' => 'Complete Web Development Bootcamp',
                'description' => 'Learn full-stack web development from scratch. This comprehensive course covers HTML, CSS, JavaScript, React, Node.js, and MongoDB.',
                'short_description' => 'Master full-stack web development with hands-on projects',
                'thumbnail' => 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=400&h=300&fit=crop',
                'price' => 99.99,
                'discount_price' => 79.99,
                'level' => 'beginner',
                'status' => 'published',
                'requirements' => ['Basic computer skills', 'No programming experience required'],
                'what_you_learn' => ['HTML5 and CSS3', 'JavaScript ES6+', 'React.js', 'Node.js', 'MongoDB'],
                'language' => 'English',
                'duration_hours' => 40,
                'is_featured' => true,
                'category_id' => $categories['ဝဘ်ဖွံ့ဖြိုးတိုးတက်မှု']->id ?? 1,
                'instructor_id' => $instructors[0]->id,
            ],
            [
                'title' => 'React Native Mobile App Development',
                'description' => 'Build cross-platform mobile applications using React Native for iOS and Android.',
                'short_description' => 'Create mobile apps for iOS and Android',
                'thumbnail' => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=400&h=300&fit=crop',
                'price' => 89.99,
                'level' => 'intermediate',
                'status' => 'published',
                'requirements' => ['Basic JavaScript knowledge', 'Familiarity with React'],
                'what_you_learn' => ['React Native fundamentals', 'Navigation', 'Redux', 'Native features'],
                'language' => 'English',
                'duration_hours' => 30,
                'is_featured' => true,
                'category_id' => $categories['မိုဘိုင်းအက်ပ်ဖွံ့ဖြိုးတိုးတက်မှု']->id ?? 2,
                'instructor_id' => $instructors[3]->id ?? $instructors[0]->id,
            ],
            [
                'title' => 'Python for Data Science',
                'description' => 'Master data science using Python. Learn data analysis, visualization, and machine learning.',
                'short_description' => 'Complete data science course using Python',
                'thumbnail' => 'https://images.unsplash.com/photo-1526379095098-d400fd0bf935?w=400&h=300&fit=crop',
                'price' => 129.99,
                'discount_price' => 99.99,
                'level' => 'intermediate',
                'status' => 'published',
                'requirements' => ['Basic Python knowledge', 'High school mathematics'],
                'what_you_learn' => ['Pandas', 'Matplotlib', 'Scikit-learn', 'Statistical analysis'],
                'language' => 'English',
                'duration_hours' => 50,
                'is_featured' => true,
                'category_id' => $categories['ဒေတာသိပ္ပံ']->id ?? 3,
                'instructor_id' => $instructors[1]->id ?? $instructors[0]->id,
            ],
            [
                'title' => 'UI/UX Design Masterclass',
                'description' => 'Learn user interface and user experience design principles with practical projects.',
                'short_description' => 'Master UI/UX design with practical projects',
                'thumbnail' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=400&h=300&fit=crop',
                'price' => 79.99,
                'level' => 'beginner',
                'status' => 'published',
                'requirements' => ['No design experience required'],
                'what_you_learn' => ['Design principles', 'User research', 'Wireframing', 'Prototyping'],
                'language' => 'English',
                'duration_hours' => 35,
                'is_featured' => true,
                'category_id' => $categories['ဒီဇိုင်း']->id ?? 4,
                'instructor_id' => $instructors[2]->id ?? $instructors[0]->id,
            ],
            [
                'title' => 'Digital Marketing Strategy',
                'description' => 'Learn comprehensive digital marketing including SEO, social media, and paid advertising.',
                'short_description' => 'Complete digital marketing course',
                'thumbnail' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=400&h=300&fit=crop',
                'price' => 69.99,
                'level' => 'beginner',
                'status' => 'published',
                'requirements' => ['Basic computer skills'],
                'what_you_learn' => ['SEO', 'Social media marketing', 'Google Ads', 'Analytics'],
                'language' => 'English',
                'duration_hours' => 25,
                'is_featured' => false,
                'category_id' => $categories['စီးပွားရေး']->id ?? 5,
                'instructor_id' => $instructors[4]->id ?? $instructors[0]->id,
            ],
            [
                'title' => 'JavaScript Fundamentals',
                'description' => 'Master JavaScript from basics to advanced concepts. Perfect for beginners.',
                'short_description' => 'Learn JavaScript programming from scratch',
                'thumbnail' => 'https://images.unsplash.com/photo-1579468118864-1b9ea3c0db4a?w=400&h=300&fit=crop',
                'price' => 0,
                'level' => 'beginner',
                'status' => 'published',
                'requirements' => ['Basic computer skills'],
                'what_you_learn' => ['JavaScript syntax', 'Functions', 'DOM manipulation', 'Events'],
                'language' => 'English',
                'duration_hours' => 20,
                'is_featured' => true,
                'category_id' => $categories['ဝဘ်ဖွံ့ဖြိုးတိုးတက်မှု']->id ?? 1,
                'instructor_id' => $instructors[0]->id,
            ],
            [
                'title' => 'Machine Learning with Python',
                'description' => 'Learn machine learning algorithms and build AI models using Python.',
                'short_description' => 'Build AI models with Python',
                'thumbnail' => 'https://images.unsplash.com/photo-1555949963-aa79dcee981c?w=400&h=300&fit=crop',
                'price' => 149.99,
                'discount_price' => 119.99,
                'level' => 'advanced',
                'status' => 'published',
                'requirements' => ['Python programming', 'Basic statistics'],
                'what_you_learn' => ['ML algorithms', 'Neural networks', 'TensorFlow', 'Model deployment'],
                'language' => 'English',
                'duration_hours' => 60,
                'is_featured' => true,
                'category_id' => $categories['ဒေတာသိပ္ပံ']->id ?? 3,
                'instructor_id' => $instructors[1]->id ?? $instructors[0]->id,
            ],
            [
                'title' => 'Flutter App Development',
                'description' => 'Build beautiful cross-platform mobile apps with Flutter and Dart.',
                'short_description' => 'Create apps with Flutter framework',
                'thumbnail' => 'https://images.unsplash.com/photo-1551650975-87deedd944c3?w=400&h=300&fit=crop',
                'price' => 94.99,
                'level' => 'intermediate',
                'status' => 'published',
                'requirements' => ['Basic programming knowledge'],
                'what_you_learn' => ['Dart language', 'Flutter widgets', 'State management', 'Firebase'],
                'language' => 'English',
                'duration_hours' => 35,
                'is_featured' => false,
                'category_id' => $categories['မိုဘိုင်းအက်ပ်ဖွံ့ဖြိုးတိုးတက်မှု']->id ?? 2,
                'instructor_id' => $instructors[3]->id ?? $instructors[0]->id,
            ],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}
