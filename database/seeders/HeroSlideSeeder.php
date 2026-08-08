<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\HeroSlide;

class HeroSlideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (HeroSlide::count() > 0) {
            return;
        }

        HeroSlide::create([
            'title' => 'Learn Anytime, Anywhere',
            'subtitle' => 'Join thousands of learners and start building your skills today with our expert-led courses.',
            'image' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1920&q=80',
            'button_text' => 'Browse Courses',
            'button_link' => '/courses',
            'sort_order' => 0,
            'is_active' => true,
        ]);
    }
}
