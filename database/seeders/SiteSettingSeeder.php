<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteSetting;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // About Page Statistics
            [
                'key' => 'about_students_count',
                'value' => '1000',
                'type' => 'number',
                'group' => 'about_stats',
                'label' => 'Students Count',
                'description' => 'Number of students to display on About page',
            ],
            [
                'key' => 'about_courses_count',
                'value' => '50',
                'type' => 'number',
                'group' => 'about_stats',
                'label' => 'Courses Count',
                'description' => 'Number of courses to display on About page',
            ],
            [
                'key' => 'about_instructors_count',
                'value' => '20',
                'type' => 'number',
                'group' => 'about_stats',
                'label' => 'Instructors Count',
                'description' => 'Number of instructors to display on About page',
            ],
            [
                'key' => 'about_partners_count',
                'value' => '5',
                'type' => 'number',
                'group' => 'about_stats',
                'label' => 'Partners Count',
                'description' => 'Number of partners to display on About page',
            ],
            // General Settings
            [
                'key' => 'site_name',
                'value' => 'Sanpya Online Academy',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Site Name',
                'description' => 'The name of your website',
            ],
            [
                'key' => 'site_description',
                'value' => 'Learn Anytime, Anywhere',
                'type' => 'textarea',
                'group' => 'general',
                'label' => 'Site Description',
                'description' => 'Short description of your website',
            ],
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
