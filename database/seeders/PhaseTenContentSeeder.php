<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\Page;
use Illuminate\Database\Seeder;

class PhaseTenContentSeeder extends Seeder
{
    /**
     * Seed editable blog, FAQ, and legal-page development content.
     */
    public function run(): void
    {
        $this->seedDraftBlogPost();
        $this->seedFaqs();
        $this->seedDraftLegalPages();
    }

    /**
     * Create one draft article without exposing it publicly.
     */
    private function seedDraftBlogPost(): void
    {
        BlogPost::updateOrCreate(
            [
                'slug' => 'welcome-to-coast-and-cay',
            ],
            [
                'title' => 'Welcome to Coast & Cay',
                'excerpt' => 'A first look at the food, warmth, and island-inspired hospitality shaping Coast & Cay.',
                'body' => <<<'HTML'
<p>Coast &amp; Cay is being created as a warm place for vibrant Caribbean food, relaxed hospitality, and memorable meals shared together.</p>

<h2>A story still being written</h2>

<p>This development article remains a draft until the client supplies and approves the final restaurant story.</p>
HTML,
                'is_published' => false,
                'published_at' => null,
                'meta_title' => 'Welcome to Coast & Cay',
                'meta_description' => 'Discover the restaurant story and Caribbean hospitality behind Coast & Cay.',
            ],
        );
    }

    /**
     * Create editable frequently asked questions for active website features.
     */
    private function seedFaqs(): void
    {
        $faqs = [
            [
                'question' => 'Can I order without creating an account?',
                'answer' => '<p>Yes. Guest checkout is available, and you will receive a secure link to view your order after checkout.</p>',
                'sort_order' => 10,
            ],
            [
                'question' => 'Do you offer pickup and local delivery?',
                'answer' => '<p>Pickup and local delivery may be available when online ordering is enabled. Delivery is limited to accepted ZIP codes shown during checkout.</p>',
                'sort_order' => 20,
            ],
            [
                'question' => 'Can I pay with cash?',
                'answer' => '<p>Cash at pickup or cash on delivery appears during checkout only when that payment method is enabled by the restaurant.</p>',
                'sort_order' => 30,
            ],
            [
                'question' => 'How should I ask about allergens?',
                'answer' => '<p>Please contact the restaurant before ordering when you have a food allergy or dietary concern. Menu labels should not replace direct confirmation from restaurant staff.</p>',
                'sort_order' => 40,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(
                [
                    'question' => $faq['question'],
                ],
                [
                    ...$faq,
                    'is_visible' => true,
                ],
            );
        }
    }

    /**
     * Create legal-page shells that require client approval before publication.
     */
    private function seedDraftLegalPages(): void
    {
        $pages = [
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'meta_description' => 'Read the Coast & Cay privacy policy.',
            ],
            [
                'slug' => 'terms-and-conditions',
                'title' => 'Terms and Conditions',
                'meta_description' => 'Read the Coast & Cay website and ordering terms.',
            ],
            [
                'slug' => 'refund-and-cancellation-policy',
                'title' => 'Refund and Cancellation Policy',
                'meta_description' => 'Review the Coast & Cay refund and cancellation policy.',
            ],
            [
                'slug' => 'delivery-and-pickup-policy',
                'title' => 'Delivery and Pickup Policy',
                'meta_description' => 'Review Coast & Cay pickup and local delivery information.',
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(
                [
                    'slug' => $page['slug'],
                ],
                [
                    ...$page,
                    'excerpt' => 'This policy is being prepared for final client review.',
                    'content' => '<p>Replace this draft with client-approved policy content before publishing the page.</p>',
                    'meta_title' => $page['title'].' | Coast & Cay',
                    'is_published' => false,
                ],
            );
        }
    }
}
