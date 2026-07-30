<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Seed a complete, editable Coast & Cay development configuration.
     *
     * Contact details and external links remain safe placeholders. Replace them
     * with client-approved production values before launch.
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
     * Return public-site, ordering, SEO, and notification defaults.
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
                'key' => 'footer_description',
                'value' => 'A neighborhood Caribbean kitchen serving grilled jerk dishes, slow-cooked favorites, fresh seafood, and easygoing hospitality near the coast.',
                'group' => 'general',
            ],
            [
                'key' => 'opening_hours',
                'value' => <<<'TEXT'
Monday–Thursday: 11:30 AM–9:00 PM
Friday–Saturday: 11:30 AM–10:00 PM
Sunday: 11:30 AM–8:00 PM
TEXT,
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
                'value' => 'Downtown Santa Monica, CA 90401',
                'group' => 'contact',
            ],
            [
                'key' => 'map_link',
                'value' => 'https://www.google.com/maps/search/?api=1&query=Downtown+Santa+Monica%2C+CA+90401',
                'group' => 'contact',
            ],

            /*
             * Do not invent public social handles. Add verified restaurant
             * profiles through Filament when the client supplies them.
             */
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
                'value' => 'Coast & Cay | Caribbean Restaurant in Santa Monica',
                'group' => 'seo',
            ],
            [
                'key' => 'meta_description',
                'value' => 'Caribbean cooking in Santa Monica, with jerk from the grill, slow-cooked favorites, seafood, pickup, and local delivery.',
                'group' => 'seo',
            ],

            [
                'key' => 'notification_recipient_email',
                'value' => 'orders@coastandcay.test',
                'group' => 'mail',
            ],

            [
                'key' => 'accepting_online_orders',
                'value' => '1',
                'group' => 'ordering',
            ],
            [
                'key' => 'online_orders_closed_message',
                'value' => 'Online ordering is paused for the moment. You can still browse the menu, or call the restaurant for current availability.',
                'group' => 'ordering',
            ],
            [
                'key' => 'accepted_delivery_zip_codes',
                'value' => '90401, 90402, 90403, 90404, 90405, 90291, 90292, 90066, 90230',
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
                'value' => '1',
                'group' => 'ordering',
            ],
            [
                'key' => 'pickup_instructions',
                'value' => 'We will email you when the order is ready. Bring the order number to the pickup counter; payment is due at pickup when cash is selected.',
                'group' => 'ordering',
            ],
            [
                'key' => 'delivery_instructions',
                'value' => 'Local delivery is available to the listed ZIP codes with a $35.00 food-and-drink minimum and a $7.50 delivery fee. Enter a complete address and any gate or building instructions at checkout.',
                'group' => 'ordering',
            ],
        ];
    }
}
