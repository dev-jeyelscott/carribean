<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\Page;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class PhaseTenContentSeeder extends Seeder
{
    /**
     * Seed published journal, FAQ, and legal-page development content.
     */
    public function run(): void
    {
        $this->seedPublishedBlogPost();
        $this->seedFaqs();
        $this->seedLegalPages();
    }

    /**
     * Publish one grounded restaurant article so the journal is available after
     * seeding and its navigation state can be tested.
     */
    private function seedPublishedBlogPost(): void
    {
        BlogPost::query()->updateOrCreate(
            [
                'slug' => 'welcome-to-coast-and-cay',
            ],
            [
                'title' => 'What We Mean by a Caribbean Table',
                'excerpt' => 'A good meal does not have to be complicated: something from the grill, something from the pot, a few sides, and enough room to pass everything around.',
                'body' => <<<'HTML'
<p>When we talk about a Caribbean table, we are not describing one dish or one island. We mean the kind of meal that invites another plate, another spoonful of rice, and one more person to pull up a chair.</p>

<h2>Start with what takes time</h2>

<p>Some of the food on our menu begins well before service. Jerk seasoning needs time on the chicken. Oxtail and goat need a slow cook. Sorrel needs to steep with ginger and spice. Those steps are not decoration; they are the reason the finished dish tastes settled rather than rushed.</p>

<h2>Let the sides do their job</h2>

<p>Rice and peas, plantains, callaloo, festival bread, and slaw are not afterthoughts. They cool the heat, catch the gravy, add texture, and turn a main dish into a complete meal.</p>

<h2>Order for the table</h2>

<p>A practical first order is one or two small plates, a grilled dish, a curry or braise, and a few sides to share. Add sorrel or limeade, then decide whether there is still room for rum cake.</p>

<p>That is the experience we are building at Coast &amp; Cay: careful cooking, an easy room, and food that makes sense when it is passed around.</p>
HTML,
                'is_published' => true,
                'published_at' => CarbonImmutable::create(
                    2026,
                    7,
                    24,
                    10,
                    0,
                    0,
                    'America/Los_Angeles',
                ),
                'meta_title' => 'What We Mean by a Caribbean Table | Coast & Cay',
                'meta_description' => 'How Coast & Cay approaches Caribbean cooking, shared plates, slow-cooked dishes, sides, and relaxed meals in Santa Monica.',
            ],
        );
    }

    /**
     * Create practical frequently asked questions for the live ordering and
     * restaurant workflows.
     */
    private function seedFaqs(): void
    {
        $faqs = [
            [
                'question' => 'Can I order without creating an account?',
                'answer' => '<p>Yes. Choose guest checkout, enter your contact details, and place the order normally. The confirmation email includes a secure link for checking the order status.</p>',
                'sort_order' => 10,
            ],
            [
                'question' => 'Do you offer pickup and local delivery?',
                'answer' => '<p>Yes, when online ordering is open. Pickup is available from the restaurant, and local delivery is offered to the ZIP codes accepted during checkout.</p>',
                'sort_order' => 20,
            ],
            [
                'question' => 'Can I pay with cash?',
                'answer' => '<p>Yes. Cash at pickup and cash on delivery are available when those options appear at checkout. Online card payment is also available when the restaurant payment service is active.</p>',
                'sort_order' => 30,
            ],
            [
                'question' => 'How should I ask about allergens?',
                'answer' => '<p>Call the restaurant before ordering and explain the allergy clearly. The menu lists known allergens where practical, but the kitchen handles wheat, milk, egg, fish, shellfish, soy, sesame, peanuts, and tree nuts in shared preparation areas.</p>',
                'sort_order' => 40,
            ],
            [
                'question' => 'Which ZIP codes are eligible for delivery?',
                'answer' => '<p>Enter the delivery ZIP code in the cart or checkout. The website will confirm eligibility before the order is placed. Delivery boundaries may be adjusted when traffic, staffing, or service conditions require it.</p>',
                'sort_order' => 50,
            ],
            [
                'question' => 'Can I change an order after placing it?',
                'answer' => '<p>Call the restaurant as soon as possible and have the order number ready. Changes are not guaranteed once the kitchen has started preparing the order, but the team will confirm what is still possible.</p>',
                'sort_order' => 60,
            ],
            [
                'question' => 'How do I check my order status?',
                'answer' => '<p>Registered customers can open Order History in their account. Guest customers can use the secure order link in the confirmation email. Status updates are also sent by email for important steps such as confirmation, pickup readiness, and delivery.</p>',
                'sort_order' => 70,
            ],
            [
                'question' => 'Does a reservation request confirm my table?',
                'answer' => '<p>No. A reservation request lets the restaurant review your preferred date, time, and party size. The table is confirmed only after a team member contacts you or sends a confirmation.</p>',
                'sort_order' => 80,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::query()->updateOrCreate(
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
     * Publish usable baseline policies for development and acceptance testing.
     *
     * These pages are operational starting points and still require client and
     * legal review before a production launch.
     */
    private function seedLegalPages(): void
    {
        foreach ($this->legalPages() as $page) {
            Page::query()->updateOrCreate(
                [
                    'slug' => $page['slug'],
                ],
                [
                    ...$page,
                    'meta_title' => $page['title'].' | Coast & Cay',
                    'is_published' => true,
                ],
            );
        }
    }

    /**
     * Return plain-language policy pages for the active website features.
     *
     * @return list<array{
     *     slug: string,
     *     title: string,
     *     excerpt: string,
     *     content: string,
     *     meta_description: string
     * }>
     */
    private function legalPages(): array
    {
        return [
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'excerpt' => 'How Coast & Cay handles information submitted through the website, customer accounts, online orders, and contact forms.',
                'content' => <<<'HTML'
<p><strong>Last updated: July 24, 2026.</strong></p>

<p>Coast &amp; Cay collects the information needed to operate the restaurant website and provide the services you request. This may include your name, email address, telephone number, delivery address, order details, account information, reservation details, and messages sent through our forms.</p>

<h2>How we use information</h2>

<p>We use this information to process and deliver orders, contact you about an order or reservation request, provide customer support, maintain customer accounts, prevent misuse, improve website operations, and meet recordkeeping obligations.</p>

<h2>Payments</h2>

<p>Online card payments are processed through Stripe-hosted Checkout. Coast &amp; Cay does not store complete card numbers or card security codes on this website. Stripe handles payment information under its own privacy and security practices.</p>

<h2>Service providers</h2>

<p>Information may be shared with service providers that support payment processing, email delivery, website hosting, storage, security, and order fulfillment. We provide only the information reasonably needed for those services.</p>

<h2>Cookies and sessions</h2>

<p>The website uses essential cookies and session storage for account sign-in, security, shopping-cart contents, checkout, and preferences. Additional analytics or marketing tools should not be enabled without updating this policy and any required consent controls.</p>

<h2>Retention and security</h2>

<p>Records are retained for as long as reasonably needed for restaurant operations, support, legal obligations, dispute resolution, and fraud prevention. We use reasonable safeguards, but no internet service can promise absolute security.</p>

<h2>Your questions</h2>

<p>To ask about personal information associated with your account or an order, contact <a href="mailto:hello@coastandcay.test">hello@coastandcay.test</a>. We may need to verify your identity before acting on a request.</p>
HTML,
                'meta_description' => 'Read how Coast & Cay handles account, order, reservation, contact, delivery, and payment-related information.',
            ],
            [
                'slug' => 'terms-and-conditions',
                'title' => 'Terms and Conditions',
                'excerpt' => 'The basic terms for using the Coast & Cay website, creating an account, and placing pickup or delivery orders.',
                'content' => <<<'HTML'
<p><strong>Last updated: July 24, 2026.</strong></p>

<p>These terms apply when you use the Coast &amp; Cay website, create an account, submit a form, or place an online order. By using those services, you agree to provide accurate information and use the website only for lawful purposes.</p>

<h2>Menu and availability</h2>

<p>Menu items, ingredients, prices, options, delivery areas, fees, hours, and availability may change. The website displays the current information available to us, but an item can become unavailable before an order is confirmed.</p>

<h2>Orders</h2>

<p>An order submission is a request to purchase. Coast &amp; Cay may confirm, reject, or cancel an order because of availability, delivery limitations, payment problems, suspected misuse, or circumstances affecting restaurant operations. You will receive an order status or direct communication when action is required.</p>

<h2>Payment</h2>

<p>Online payments are processed by Stripe. Cash at pickup or cash on delivery is available only when shown during checkout. You are responsible for all charges displayed in the final order total, including applicable tax and delivery fees.</p>

<h2>Allergies and dietary needs</h2>

<p>Contact the restaurant before ordering if you have an allergy or strict dietary requirement. Menu descriptions and labels are informational and do not guarantee that cross-contact can be prevented in a shared kitchen.</p>

<h2>Accounts and secure links</h2>

<p>Keep account credentials and guest order links private. You are responsible for activity performed through your account unless you promptly report unauthorized access.</p>

<h2>Website content</h2>

<p>Restaurant names, branding, photographs, menu text, and website content may not be copied or used commercially without permission, except where the law allows it.</p>

<h2>Changes</h2>

<p>We may update these terms as the website or restaurant services change. The date at the top identifies the current version.</p>
HTML,
                'meta_description' => 'Review the terms for using the Coast & Cay website and placing pickup or local delivery orders.',
            ],
            [
                'slug' => 'refund-and-cancellation-policy',
                'title' => 'Refund and Cancellation Policy',
                'excerpt' => 'What to do when an order needs to be changed, cancelled, corrected, or refunded.',
                'content' => <<<'HTML'
<p><strong>Last updated: July 24, 2026.</strong></p>

<h2>Changes and cancellations</h2>

<p>Call the restaurant as soon as possible and provide the order number. We will try to help before preparation begins, but a change or cancellation is not guaranteed after the kitchen has started the order.</p>

<h2>Restaurant cancellations</h2>

<p>Coast &amp; Cay may reject or cancel an order when an item is unavailable, a delivery address cannot be served, payment cannot be completed, or restaurant operations prevent fulfillment. Any captured online payment for a cancelled order will be refunded to the original payment method.</p>

<h2>Incorrect or missing items</h2>

<p>Contact the restaurant promptly after pickup or delivery. Keep the order and packaging available while the team reviews the issue. Depending on the circumstances, the restaurant may replace the item, issue store credit, or approve a full or partial refund.</p>

<h2>Prepared food</h2>

<p>Prepared food is generally not refundable because of a change of mind, personal taste, an unreported allergy, an incorrect address supplied at checkout, or failure to collect an order during the stated pickup period.</p>

<h2>Refund timing</h2>

<p>Approved card refunds are returned through the original payment provider. Bank processing times vary and are outside the restaurant's control. Cash-order resolutions are handled directly by restaurant staff.</p>
HTML,
                'meta_description' => 'Read the Coast & Cay policy for order changes, cancellations, missing items, and approved refunds.',
            ],
            [
                'slug' => 'delivery-and-pickup-policy',
                'title' => 'Delivery and Pickup Policy',
                'excerpt' => 'How pickup, local delivery, ZIP-code eligibility, timing, fees, and handoff work.',
                'content' => <<<'HTML'
<p><strong>Last updated: July 24, 2026.</strong></p>

<h2>Pickup</h2>

<p>Pickup orders are collected from the Coast &amp; Cay pickup counter. Wait for the ready-for-pickup email or status update before arriving, and bring the order number. Cash-at-pickup orders must be paid before the order is handed over.</p>

<h2>Local delivery</h2>

<p>Delivery is available only to ZIP codes accepted by the website at checkout. A minimum food-and-drink subtotal and delivery fee may apply. Current amounts are shown before the order is placed.</p>

<h2>Timing</h2>

<p>Orders are prepared for the earliest practical pickup or delivery. Times shown or communicated are estimates, not guarantees. Weather, traffic, order volume, building access, and item preparation can affect timing.</p>

<h2>Address and access details</h2>

<p>Customers are responsible for entering a complete and accurate address, telephone number, apartment or unit, gate code, and delivery instructions. Drivers must be able to reach the delivery point safely and legally.</p>

<h2>Delivery handoff</h2>

<p>Someone must be available to receive the order unless a contact-free handoff has been agreed. If the customer cannot be reached or the address cannot be accessed, the order may be returned to the restaurant and additional delivery attempts are not guaranteed.</p>

<h2>Order condition</h2>

<p>Report a significant delivery problem promptly. Food quality can be affected when delivery is delayed by an incorrect address, unavailable recipient, restricted building access, or instructions provided after dispatch.</p>
HTML,
                'meta_description' => 'Review Coast & Cay pickup instructions, local delivery eligibility, fees, timing, and handoff requirements.',
            ],
        ];
    }
}
