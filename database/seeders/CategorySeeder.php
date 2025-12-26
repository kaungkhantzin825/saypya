<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'ဝဘ်ဖွံ့ဖြိုးတိုးတက်မှု',
                'description' => 'HTML, CSS, JavaScript နှင့် လူကြိုက်များသော framework များအပါအဝင် ခေတ်မီဝဘ်ဖွံ့ဖြိုးတိုးတက်မှုနည်းပညာများကို သင်ယူပါ။',
                'icon' => 'fas fa-code',
                'sort_order' => 1,
            ],
            [
                'name' => 'မိုဘိုင်းအက်ပ်ဖွံ့ဖြိုးတိုးတက်မှု',
                'description' => 'Native နှင့် cross-platform နည်းပညာများကို အသုံးပြု၍ iOS နှင့် Android အတွက် မိုဘိုင်းအက်ပ်များ တည်ဆောက်ပါ။',
                'icon' => 'fas fa-mobile-alt',
                'sort_order' => 2,
            ],
            [
                'name' => 'ဒေတာသိပ္ပံ',
                'description' => 'ဒေတာခွဲခြမ်းစိတ်ဖြာမှု၊ စက်သင်ယူမှုနှင့် ဉာဏ်ရည်တုနည်းပညာများကို ကျွမ်းကျင်အောင်လုပ်ပါ။',
                'icon' => 'fas fa-chart-bar',
                'sort_order' => 3,
            ],
            [
                'name' => 'ဒီဇိုင်း',
                'description' => 'ဂရပ်ဖစ်ဒီဇိုင်း၊ UI/UX ဒီဇိုင်းနှင့် ဒစ်ဂျစ်တယ်အနုပညာဖန်တီးမှုကို သင်ယူပါ။',
                'icon' => 'fas fa-palette',
                'sort_order' => 4,
            ],
            [
                'name' => 'စီးပွားရေး',
                'description' => 'စီမံခန့်ခွဲမှု၊ စျေးကွက်ရှာဖွေမှုနှင့် လုပ်ငန်းစတင်မှုအပါအဝင် စီးပွားရေးကျွမ်းကျင်မှုများကို ဖွံ့ဖြိုးတိုးတက်စေပါ။',
                'icon' => 'fas fa-briefcase',
                'sort_order' => 5,
            ],
            [
                'name' => 'ဒစ်ဂျစ်တယ်စျေးကွက်ရှာဖွေမှု',
                'description' => 'အွန်လိုင်းစျေးကွက်ရှာဖွေမှုဗျူဟာများ၊ SEO၊ လူမှုကွန်ယက်စျေးကွက်ရှာဖွေမှုနှင့် အကြောင်းအရာစျေးကွက်ရှာဖွေမှုကို ကျွမ်းကျင်အောင်လုပ်ပါ။',
                'icon' => 'fas fa-bullhorn',
                'sort_order' => 6,
            ],
            [
                'name' => 'ဓာတ်ပုံပညာ',
                'description' => 'ဓာတ်ပုံရိုက်ခြင်းနည်းပညာများ၊ ဓာတ်ပုံတည်းဖြတ်ခြင်းနှင့် အမြင်အာရုံဇာတ်လမ်းပြောခြင်းကို သင်ယူပါ။',
                'icon' => 'fas fa-camera',
                'sort_order' => 7,
            ],
            [
                'name' => 'ဂီတ',
                'description' => 'ဂီတသီအိုရီ၊ တူရိယာတီးခြင်းနှင့် ဂီတထုတ်လုပ်ခြင်းကို လေ့လာပါ။',
                'icon' => 'fas fa-music',
                'sort_order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}