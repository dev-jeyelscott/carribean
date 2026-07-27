<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Seed development placeholder content for public CMS pages.
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
                'meta_description' => 'Discover Caribbean food, warm hospitality, reservations, and relaxed California dining at Coast & Cay.',
                'is_published' => true,
            ],
            [
                'slug' => 'about',
                'title' => 'Caribbean Roots, California Rhythm',
                'excerpt' => 'Coast & Cay is being created as a warm neighborhood restaurant where Caribbean flavors and easy California hospitality meet.',
                'content' => <<<'HTML'
<p>Coast & Cay began with a simple idea: create a restaurant where the food feels vibrant, the welcome feels genuine, and guests always have a reason to stay a little longer.</p>

<p>Our development identity draws inspiration from the warmth, generosity, and bold flavors associated with Caribbean hospitality, paired with the relaxed pace of the California coast.</p>

<h2>A restaurant made for gathering</h2>

<p>The final restaurant story, heritage, menu, and photography will be supplied by the client and their consultant. Until then, Coast & Cay remains an editable development identity designed to be replaced without changing the website architecture.</p>
HTML,
                'meta_title' => 'About Coast & Cay',
                'meta_description' => 'Learn about the Coast & Cay restaurant concept, Caribbean inspiration, and relaxed California hospitality.',
                'is_published' => true,
            ],
            [
                'slug' => 'menu',
                'title' => 'Island Favorites, Made to Gather Around',
                'excerpt' => 'Explore colorful starters, generous mains, sweet finishes, and drinks made for slow afternoons and lively evenings.',
                'content' => 'Food made with warmth, color, and a generous sense of hospitality.',
                'meta_title' => 'Menu | Coast & Cay',
                'meta_description' => 'Explore the current Coast & Cay menu of starters, mains, desserts, and drinks.',
                'is_published' => true,
            ],
            [
                'slug' => 'reservation-request',
                'title' => 'Save a Place at the Table',
                'excerpt' => 'Share your preferred date, time, and party size, and our team will personally confirm availability.',
                'content' => 'Every reservation remains a request until the restaurant team reviews and confirms it directly with you.',
                'meta_title' => 'Reservation Request | Coast & Cay',
                'meta_description' => 'Request a table at Coast & Cay and receive confirmation from the restaurant team.',
                'is_published' => true,
            ],
            [
                'slug' => 'order-inquiry',
                'title' => 'Online Ordering Is Coming Soon',
                'excerpt' => 'The current inquiry form remains available during development while transactional ordering is being built.',
                'content' => 'Browse the menu today. Shopping cart, checkout, pickup, delivery, and payment functionality will be introduced in the upcoming commerce phases.',
                'meta_title' => 'Order Inquiry | Coast & Cay',
                'meta_description' => 'Contact Coast & Cay regarding current pickup or delivery availability.',
                'is_published' => true,
            ],
            [
                'slug' => 'gallery',
                'title' => 'Food, Color, and Easy Evenings',
                'excerpt' => 'A look at the dishes, rooms, and warm details shaping the Coast & Cay experience.',
                'content' => 'Final restaurant photography will replace the current development imagery once approved assets are supplied.',
                'meta_title' => 'Gallery | Coast & Cay',
                'meta_description' => 'Explore the food, hospitality, and restaurant atmosphere of Coast & Cay.',
                'is_published' => true,
            ],
            [
                'slug' => 'contact',
                'title' => 'Come Say Hello',
                'excerpt' => 'Questions about the menu, a future visit, or the restaurant? Our team will be pleased to help.',
                'content' => 'Reach the restaurant by phone, email, location map, or the contact form.',
                'meta_title' => 'Contact Coast & Cay',
                'meta_description' => 'Contact Coast & Cay for menu questions, directions, reservations, and restaurant information.',
                'is_published' => true,
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(
                ['slug' => $page['slug']],
                $page,
            );
        }
    }
}
