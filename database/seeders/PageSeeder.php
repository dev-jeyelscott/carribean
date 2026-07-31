<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Seed natural, editable copy for the primary public pages.
     */
    public function run(): void
    {
        foreach ($this->pages() as $page) {
            Page::query()->updateOrCreate(
                [
                    'slug' => $page['slug'],
                ],
                $page,
            );
        }
    }

    /**
     * Return the public page records used by the restaurant website.
     *
     * @return list<array<string, mixed>>
     */
    private function pages(): array
    {
        return [
            [
                'slug' => 'home',
                'title' => 'Caribbean Cooking for Long Lunches and Easy Evenings',
                'excerpt' => 'Jerk from the grill, slow-cooked stews, fresh seafood, and island drinks served with a relaxed California welcome.',
                'content' => 'Coast & Cay is a neighborhood Caribbean restaurant in Santa Monica. The menu is built for sharing: crisp fritters, smoky jerk chicken, coconut curries, braised oxtail, bright vegetable dishes, and something sweet before you leave.',
                'meta_title' => 'Coast & Cay | Caribbean Restaurant in Santa Monica',
                'meta_description' => 'Explore the Coast & Cay menu, order for pickup or local delivery, and plan a relaxed Caribbean meal in Santa Monica.',
                'is_published' => true,
            ],
            [
                'slug' => 'about',
                'title' => 'A Caribbean Kitchen on the California Coast',
                'excerpt' => 'Our cooking draws from the food people gather around across the Caribbean, with room for California produce and the pace of a neighborhood restaurant.',
                'content' => <<<'HTML'
<p>Coast &amp; Cay brings together the food we look forward to sharing: jerk cooked over high heat, stews given time, seafood sharpened with citrus and pepper, and sides that make a plate feel complete.</p>

<p>We are not trying to compress the Caribbean into one style of cooking. The menu moves across familiar dishes and ingredients with respect for where they come from, while staying practical for the season and the California coast.</p>

<p>The dining room is meant to feel polished without becoming formal. Come for a quick lunch, bring a group for dinner, or take a full meal home.</p>
HTML,
                'sections' => [
                    'hero' => [
                        'title' => 'Caribbean cooking, close to the coast.',
                        'accent' => 'Food with history. Hospitality without fuss.',
                    ],
                    'story' => [
                        'eyebrow' => 'The Kitchen',
                        'title' => 'Built around the dishes people ask for again.',
                        'description' => 'Our menu starts with recognizable Caribbean favorites and pays attention to the details that make them worth returning for: well-seasoned marinades, patient braises, fresh herbs, proper heat, and sides prepared as carefully as the main dish.',
                        'quote' => 'A generous table does not need a special occasion.',
                    ],
                    'values_eyebrow' => 'How We Work',
                    'values_title' => 'Simple standards, followed every day.',
                    'values' => [
                        [
                            'number' => '01',
                            'title' => 'Cook with patience',
                            'description' => 'Marinades get time, stews are not rushed, and sauces are built in the kitchen rather than poured from a bottle.',
                        ],
                        [
                            'number' => '02',
                            'title' => 'Season with purpose',
                            'description' => 'Heat should carry flavor, not cover it. Guests can choose a comfortable spice level on selected dishes.',
                        ],
                        [
                            'number' => '03',
                            'title' => 'Serve a full plate',
                            'description' => 'Mains, sides, sauces, and garnishes are considered together so every plate feels complete.',
                        ],
                        [
                            'number' => '04',
                            'title' => 'Make room for people',
                            'description' => 'The service is attentive, the room is easygoing, and the table is set up for sharing.',
                        ],
                    ],
                    'heritage' => [
                        'eyebrow' => 'What Shapes the Menu',
                        'title' => 'Island staples, California produce, and a working restaurant kitchen.',
                        'description' => 'The menu draws on Caribbean techniques and pantry ingredients while making sensible use of what is fresh and available locally.',
                        'items' => [
                            [
                                'title' => 'From the grill',
                                'description' => 'Jerk seasoning, smoke, citrus, and properly rested meats.',
                            ],
                            [
                                'title' => 'From the pot',
                                'description' => 'Coconut curries, brown stews, braised oxtail, beans, and rice.',
                            ],
                            [
                                'title' => 'From the market',
                                'description' => 'Seasonal vegetables, fresh herbs, fruit, and seafood selected for the current menu.',
                            ],
                        ],
                    ],
                    'experience' => [
                        'eyebrow' => 'At the Restaurant',
                        'title' => 'Come as you are. Order what sounds good.',
                        'description' => 'Lunch can be quick, dinner can run long, and a few small plates can easily become a full table.',
                        'items' => [
                            [
                                'text' => 'Staff who can explain the menu without reciting it.',
                            ],
                            [
                                'text' => 'Spice-level and side choices on selected dishes.',
                            ],
                            [
                                'text' => 'Pickup and local delivery for nights at home.',
                            ],
                            [
                                'text' => 'A dining room that works for dates, families, and groups.',
                            ],
                        ],
                    ],
                    'closing' => [
                        'eyebrow' => 'Join Us',
                        'title' => 'Start with something for the table.',
                        'description' => 'We will take it from there.',
                    ],
                ],
                'meta_title' => 'About Coast & Cay | Caribbean Restaurant in Santa Monica',
                'meta_description' => 'Read about the cooking, ingredients, and easygoing hospitality behind Coast & Cay in Santa Monica.',
                'is_published' => true,
            ],
            [
                'slug' => 'menu',
                'title' => 'Start with Doubles. Stay for Oxtail.',
                'excerpt' => 'Small plates, grilled jerk dishes, slow braises, seafood, plant-based mains, familiar sides, and house drinks.',
                'content' => 'Availability may change during service. Ask the team about current ingredients and allergy accommodations before placing an order.',
                'meta_title' => 'Menu | Coast & Cay',
                'meta_description' => 'Browse Caribbean small plates, jerk chicken, oxtail, seafood, plant-based dishes, desserts, and drinks at Coast & Cay.',
                'is_published' => true,
            ],
            [
                'slug' => 'gallery',
                'title' => 'Around the Table',
                'excerpt' => 'A closer look at the dishes, drinks, and details that make up an evening at Coast & Cay.',
                'content' => 'The gallery is updated as new restaurant photography becomes available.',
                'meta_title' => 'Gallery | Coast & Cay',
                'meta_description' => 'See dishes, drinks, and restaurant moments from Coast & Cay in Santa Monica.',
                'is_published' => true,
            ],
            [
                'slug' => 'contact',
                'title' => 'Questions, Orders, and Directions',
                'excerpt' => 'Send a note about the menu, an existing online order, accessibility, directions, or anything you need before visiting.',
                'content' => 'For time-sensitive order questions, call the restaurant and have your order number ready. General messages are answered during business hours.',
                'meta_title' => 'Contact Coast & Cay',
                'meta_description' => 'Contact Coast & Cay for online-order support, menu questions, accessibility, directions, and restaurant information.',
                'is_published' => true,
            ],
        ];
    }
}
