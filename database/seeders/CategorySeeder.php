<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'ဝဘ်ဖွံ့ဖြိုးတိုးတက်မှု',
                'slug' => 'web-development',
                'description' => 'HTML, CSS, JavaScript နှင့် framework များ သင်ယူပါ။',
                'icon' => 'fas fa-code',
                'image' => 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=200&h=200&fit=crop',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'မိုဘိုင်းအက်ပ်ဖွံ့ဖြိုးတိုးတက်မှု',
                'slug' => 'mobile-development',
                'description' => 'iOS နှင့် Android အတွက် မိုဘိုင်းအက်ပ်များ တည်ဆောက်ပါ။',
                'icon' => 'fas fa-mobile-alt',
                'image' => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=200&h=200&fit=crop',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'ဒေတာသိပ္ပံ',
                'slug' => 'data-science',
                'description' => 'ဒေတာခွဲခြမ်းစိတ်ဖြာမှုနှင့် Machine Learning သင်ယူပါ။',
                'icon' => 'fas fa-chart-bar',
                'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=200&h=200&fit=crop',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'ဒီဇိုင်း',
                'slug' => 'design',
                'description' => 'UI/UX ဒီဇိုင်းနှင့် ဂရပ်ဖစ်ဒီဇိုင်း သင်ယူပါ။',
                'icon' => 'fas fa-palette',
                'image' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=200&h=200&fit=crop',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'စီးပွားရေး',
                'slug' => 'business',
                'description' => 'စီးပွားရေးနှင့် စီမံခန့်ခွဲမှု ကျွမ်းကျင်မှုများ သင်ယူပါ။',
                'icon' => 'fas fa-briefcase',
                'image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=200&h=200&fit=crop',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'ဒစ်ဂျစ်တယ်စျေးကွက်ရှာဖွေမှု',
                'slug' => 'digital-marketing',
                'description' => 'SEO နှင့် Social Media Marketing သင်ယူပါ။',
                'icon' => 'fas fa-bullhorn',
                'image' => 'https://images.unsplash.com/photo-1432888622747-4eb9a8efeb07?w=200&h=200&fit=crop',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'ဓာတ်ပုံပညာ',
                'slug' => 'photography',
                'description' => 'ဓာတ်ပုံရိုက်ခြင်းနှင့် တည်းဖြတ်ခြင်း သင်ယူပါ။',
                'icon' => 'fas fa-camera',
                'image' => 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=200&h=200&fit=crop',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'ဂီတ',
                'slug' => 'music',
                'description' => 'ဂီတသီအိုရီနှင့် တူရိယာတီးခြင်း သင်ယူပါ။',
                'icon' => 'fas fa-music',
                'image' => 'https://images.unsplash.com/photo-1511379938547-c1f69419868d?w=200&h=200&fit=crop',
                'sort_order' => 8,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
