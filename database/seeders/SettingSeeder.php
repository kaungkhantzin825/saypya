<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'contact_email' => 'sanpyaeducationcentre@gmail.com',
            'contact_phone' => '+95 9 69523 8273',
            'contact_address' => 'Yangon, Myanmar',
            'contact_facebook' => 'https://www.facebook.com/sanpyalearning2017',
            'contact_website' => 'https://sanpyalearning.com',
            'site_name' => 'SanPya Learning',
            'site_description' => 'အွန်လိုင်းသင်ကြားရေး ပလက်ဖောင်း',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
