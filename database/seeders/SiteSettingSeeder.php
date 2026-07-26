<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Seed editable development placeholders for the public restaurant site.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'restaurant_name',
                'value' => 'Coast & Cay',
                'group' => 'general',
            ],
            [
                'key' => 'tagline',
                'value' => 'Caribbean warmth, California ease.',
                'group' => 'general',
            ],
            [
                'key' => 'opening_hours',
                'value' => 'Opening hours coming soon.',
                'group' => 'general',
            ],

            [
                'key' => 'phone',
                'value' => '+1 (555) 555-0142',
                'group' => 'contact',
            ],
            [
                'key' => 'email',
                'value' => 'hello@coastandcay.test',
                'group' => 'contact',
            ],
            [
                'key' => 'address',
                'value' => 'California location details coming soon.',
                'group' => 'contact',
            ],
            [
                'key' => 'map_link',
                'value' => 'https://maps.google.com',
                'group' => 'contact',
            ],

            [
                'key' => 'facebook_url',
                'value' => 'https://facebook.com',
                'group' => 'social',
            ],
            [
                'key' => 'instagram_url',
                'value' => 'https://instagram.com',
                'group' => 'social',
            ],

            [
                'key' => 'meta_title',
                'value' => 'Coast & Cay',
                'group' => 'seo',
            ],
            [
                'key' => 'meta_description',
                'value' => 'Caribbean food, warm hospitality, and California ease at Coast & Cay.',
                'group' => 'seo',
            ],
            [
                'key' => 'notification_recipient_email',
                'value' => 'restaurant@coastandcay.test',
                'group' => 'mail',
            ],
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting,
            );
        }
    }
}
