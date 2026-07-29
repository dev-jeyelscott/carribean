<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Seed coherent Coast & Cay development settings for Santa Monica,
     * California.
     *
     * These values are safe development defaults. The restaurant's exact
     * street address, contact details, operating hours, delivery boundary,
     * and accountant-approved tax rate must be confirmed before production.
     */
    public function run(): void
    {
        foreach ($this->settings() as $setting) {
            SiteSetting::query()->updateOrCreate(
                [
                    'key' => $setting['key'],
                ],
                $setting,
            );
        }

        SiteSetting::forgetCachedValues();
    }

    /**
     * Return editable California-based development defaults grouped for the
     * public website, ordering flow, SEO, and customer notifications.
     *
     * @return list<array{
     *     key: string,
     *     value: string|null,
     *     group: string
     * }>
     */
    private function settings(): array
    {
        return [
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
                'value' => 'Mon–Thu 11:00 AM–9:00 PM; Fri–Sat 11:00 AM–10:00 PM; Sun 11:00 AM–9:00 PM',
                'group' => 'general',
            ],

            [
                'key' => 'phone',
                'value' => '+1 (310) 555-0142',
                'group' => 'contact',
            ],
            [
                'key' => 'email',
                'value' => 'hello@coastandcay.test',
                'group' => 'contact',
            ],
            [
                'key' => 'address',
                'value' => 'Santa Monica, CA 90401',
                'group' => 'contact',
            ],
            [
                'key' => 'map_link',
                'value' => 'https://www.google.com/maps/search/?api=1&query=Santa+Monica%2C+CA+90401',
                'group' => 'contact',
            ],

            [
                'key' => 'facebook_url',
                'value' => null,
                'group' => 'social',
            ],
            [
                'key' => 'instagram_url',
                'value' => null,
                'group' => 'social',
            ],
            [
                'key' => 'tiktok_url',
                'value' => null,
                'group' => 'social',
            ],

            [
                'key' => 'meta_title',
                'value' => 'Coast & Cay | Caribbean Restaurant in Santa Monica, California',
                'group' => 'seo',
            ],
            [
                'key' => 'meta_description',
                'value' => 'Upscale Caribbean dining in Santa Monica with warm hospitality, California coastal ease, pickup, and local delivery.',
                'group' => 'seo',
            ],

            [
                'key' => 'notification_recipient_email',
                'value' => 'restaurant@coastandcay.test',
                'group' => 'mail',
            ],

            [
                'key' => 'accepting_online_orders',
                'value' => '1',
                'group' => 'ordering',
            ],
            [
                'key' => 'online_orders_closed_message',
                'value' => 'Online ordering is temporarily paused. You may continue browsing the menu and keep existing items in your cart.',
                'group' => 'ordering',
            ],
            [
                'key' => 'accepted_delivery_zip_codes',
                'value' => '90401, 90402, 90403, 90404, 90405',
                'group' => 'ordering',
            ],
            [
                'key' => 'delivery_fee_cents',
                'value' => '750',
                'group' => 'ordering',
            ],
            [
                'key' => 'delivery_minimum_cents',
                'value' => '3500',
                'group' => 'ordering',
            ],
            [
                'key' => 'tax_rate_basis_points',
                'value' => '1075',
                'group' => 'ordering',
            ],
            [
                'key' => 'cash_at_pickup_enabled',
                'value' => '1',
                'group' => 'ordering',
            ],
            [
                'key' => 'cash_on_delivery_enabled',
                'value' => '0',
                'group' => 'ordering',
            ],
            [
                'key' => 'pickup_instructions',
                'value' => 'Collect your order from the Coast & Cay Santa Monica location after receiving the ready-for-pickup confirmation. Bring your order number.',
                'group' => 'ordering',
            ],
            [
                'key' => 'delivery_instructions',
                'value' => 'Local delivery is available within approved Santa Monica ZIP codes. A valid ZIP code and the configured minimum merchandise total are required.',
                'group' => 'ordering',
            ],
        ];
    }
}
