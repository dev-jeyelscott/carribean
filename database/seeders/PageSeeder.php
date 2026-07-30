<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Seed editable development placeholder content for public CMS pages.
     */
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'home',
                'title' => 'Island Hospitality, Made for the California Coast',
                'excerpt' => 'A warm gathering place for vibrant Caribbean dishes, thoughtful drinks, and relaxed hospitality.',
                'content' => 'Coast & Cay brings Caribbean warmth to California through generous food, relaxed hospitality, and a dining room made for gathering.',
                'meta_title' => 'Coast & Cay | Caribbean Restaurant in California',
                'meta_description' => 'Discover Caribbean food, online ordering, pickup, local delivery, and relaxed California dining at Coast & Cay.',
                'is_published' => true,
            ],
            [
                'slug' => 'about',
                'title' => 'About Coast & Cay',
                'excerpt' => 'We are more than a restaurant. We are a meeting of cultures, a celebration of flavor, and a place where everyone feels at home.',
                'content' => <<<'HTML'
<p>Coast & Cay began with a simple idea: share the soulful, vibrant flavors of the Caribbean with the laid-back ease of California hospitality.</p>

<p>Our chefs blend time-honored recipes with the best local ingredients to create dishes that feel both familiar and new.</p>
HTML,
                'sections' => [
                    'hero' => [
                        'title' => 'About Coast & Cay.',
                        'accent' => 'Rooted in the Caribbean. Inspired by California.',
                    ],
                    'story' => [
                        'eyebrow' => 'Our Story',
                        'title' => 'From island roots to the California coast.',
                        'description' => 'Coast & Cay began with a simple idea: share the soulful, vibrant flavors of the Caribbean with the laid-back ease of California hospitality. Our chefs blend time-honored recipes with the best local ingredients to create dishes that feel both familiar and new.',
                        'quote' => 'Caribbean warmth. California ease.',
                    ],
                    'values_eyebrow' => 'What Defines Us',
                    'values_title' => 'Our values. In everything we do.',
                    'values' => [
                        [
                            'number' => '01',
                            'title' => 'Bold Flavor',
                            'description' => 'Vibrant, soulful flavors that celebrate the Caribbean spirit.',
                        ],
                        [
                            'number' => '02',
                            'title' => 'Genuine Hospitality',
                            'description' => 'Warm welcomes, attentive service, and care in every detail.',
                        ],
                        [
                            'number' => '03',
                            'title' => 'California Ease',
                            'description' => 'A relaxed, modern atmosphere where good food and good times flow.',
                        ],
                        [
                            'number' => '04',
                            'title' => 'Caribbean Roots',
                            'description' => 'Traditions, ingredients, and stories that continue to inspire us.',
                        ],
                    ],
                    'heritage' => [
                        'eyebrow' => 'Our Heritage',
                        'title' => 'Honoring where we come from. Creating what is next.',
                        'description' => 'Our inspiration comes from sun-soaked islands, family kitchens, and the farmers and producers who share our values.',
                        'items' => [
                            [
                                'title' => 'Inspired by Islands',
                                'description' => 'Caribbean traditions and time-honored recipes.',
                            ],
                            [
                                'title' => 'Thoughtful Ingredients',
                                'description' => 'Seasonal, sustainable, and locally sourced where possible.',
                            ],
                            [
                                'title' => 'Made with Care',
                                'description' => 'Prepared daily by people who love what they do.',
                            ],
                        ],
                    ],
                    'experience' => [
                        'eyebrow' => 'The Coast & Cay Experience',
                        'title' => 'Why guests keep coming back.',
                        'description' => 'It is more than the food. It is how we make you feel. Every visit is designed to be effortless, memorable, and filled with good energy.',
                        'items' => [
                            [
                                'text' => 'Warm welcomes from the moment you arrive.',
                            ],
                            [
                                'text' => 'Dishes that surprise and satisfy, every time.',
                            ],
                            [
                                'text' => 'A space that feels elevated and easygoing.',
                            ],
                            [
                                'text' => 'Moments worth savoring and sharing.',
                            ],
                        ],
                    ],
                    'closing' => [
                        'eyebrow' => 'You Are Invited',
                        'title' => 'Good food. Good people. Great memories.',
                        'description' => 'Come for the flavor. Stay for the feeling.',
                    ],
                ],
                'meta_title' => 'About Coast & Cay',
                'meta_description' => 'Learn about the Coast & Cay restaurant story, Caribbean inspiration, values, ingredients, and relaxed California hospitality.',
                'is_published' => true,
            ],
            [
                'slug' => 'menu',
                'title' => 'Island Favorites, Made to Gather Around',
                'excerpt' => 'Explore colorful starters, generous mains, sweet finishes, and drinks made for slow afternoons and lively evenings.',
                'content' => 'Food made with warmth, color, and a generous sense of hospitality.',
                'meta_title' => 'Menu | Coast & Cay',
                'meta_description' => 'Explore the current Coast & Cay menu of starters, mains, desserts, drinks, and online-ordering options.',
                'is_published' => true,
            ],
            [
                'slug' => 'gallery',
                'title' => 'Food, Color, and Easy Evenings',
                'excerpt' => 'A look at the dishes, rooms, and warm details shaping the Coast & Cay experience.',
                'content' => 'Final restaurant photography will replace the current development imagery once approved assets are supplied.',
                'meta_title' => 'Gallery | Coast & Cay',
                'meta_description' => 'Explore the food, hospitality, and relaxed restaurant atmosphere of Coast & Cay.',
                'is_published' => true,
            ],
            [
                'slug' => 'contact',
                'title' => 'Come Say Hello',
                'excerpt' => 'Questions about the menu, an online order, directions, or the restaurant? Our team will be pleased to help.',
                'content' => 'Reach the restaurant by phone, email, location map, or the contact form.',
                'meta_title' => 'Contact Coast & Cay',
                'meta_description' => 'Contact Coast & Cay for menu questions, online-order support, directions, and restaurant information.',
                'is_published' => true,
            ],
        ];

        foreach ($pages as $page) {
            Page::query()->updateOrCreate(
                [
                    'slug' => $page['slug'],
                ],
                $page,
            );
        }
    }
}
