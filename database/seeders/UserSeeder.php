<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@learnhub.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
            'bio' => 'System administrator with full access to manage the platform.',
            'is_active' => true,
        ]);

        // Create sample lecturers
        $lecturers = [
            [
                'name' => 'Dr. Sarah Johnson',
                'email' => 'sarah@learnhub.com',
                'bio' => 'Full-stack developer with 10+ years of experience in web development and software architecture.',
                'country' => 'United States',
            ],
            [
                'name' => 'Prof. Michael Chen',
                'email' => 'michael@learnhub.com',
                'bio' => 'Data scientist and machine learning expert with PhD in Computer Science.',
                'country' => 'Canada',
            ],
            [
                'name' => 'Emily Rodriguez',
                'email' => 'emily@learnhub.com',
                'bio' => 'UX/UI designer and digital marketing specialist with expertise in modern design trends.',
                'country' => 'Spain',
            ],
            [
                'name' => 'David Kim',
                'email' => 'david@learnhub.com',
                'bio' => 'Mobile app developer and entrepreneur with successful apps in the App Store.',
                'country' => 'South Korea',
            ],
            [
                'name' => 'Lisa Thompson',
                'email' => 'lisa@learnhub.com',
                'bio' => 'Business consultant and project management expert with MBA from Harvard.',
                'country' => 'United Kingdom',
            ],
        ];

        foreach ($lecturers as $lecturer) {
            User::create([
                'name' => $lecturer['name'],
                'email' => $lecturer['email'],
                'password' => Hash::make('password'),
                'role' => 'lecturer',
                'email_verified_at' => now(),
                'bio' => $lecturer['bio'],
                'country' => $lecturer['country'],
                'is_active' => true,
            ]);
        }

        // Create sample students
        $students = [
            'John Doe',
            'Jane Smith',
            'Alex Wilson',
            'Maria Garcia',
            'James Brown',
            'Anna Davis',
            'Robert Miller',
            'Jennifer Taylor',
            'William Anderson',
            'Elizabeth Moore',
        ];

        foreach ($students as $index => $name) {
            User::create([
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)) . '@example.com',
                'password' => Hash::make('password'),
                'role' => 'student',
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
        }
    }
}