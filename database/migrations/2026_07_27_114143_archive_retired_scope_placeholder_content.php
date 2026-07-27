<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Archive exact legacy placeholders without deleting historical records.
     */
    public function up(): void
    {
        DB::table('pages')
            ->where('slug', 'contact')
            ->where(
                'meta_description',
                'Contact Coast & Cay for menu questions, directions, reservations, and restaurant information.',
            )
            ->update([
                'meta_description' => 'Contact Coast & Cay for menu questions, online-order support, directions, and restaurant information.',
                'updated_at' => now(),
            ]);

        $legacyFaq = DB::table('faqs')
            ->where(
                'question',
                'Is a reservation request automatically confirmed?',
            )
            ->first();

        if ($legacyFaq !== null) {
            $replacementExists = DB::table('faqs')
                ->where(
                    'question',
                    'Can I order without creating an account?',
                )
                ->exists();

            if ($replacementExists) {
                DB::table('faqs')
                    ->where('id', $legacyFaq->id)
                    ->update([
                        'is_visible' => false,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('faqs')
                    ->where('id', $legacyFaq->id)
                    ->update([
                        'question' => 'Can I order without creating an account?',
                        'answer' => '<p>Yes. Guest checkout is available, and you will receive a secure link to view your order after checkout.</p>',
                        'updated_at' => now(),
                    ]);
            }
        }

        $legacyMenuItemSlugs = [
            'truffle-crusted-beef-tenderloin',
            'miso-glazed-chilean-sea-bass',
            'herb-roasted-rack-of-lamb',
            'butter-poached-lobster-thermidor',
            'black-garlic-wagyu-striploin',
            'valrhona-dark-chocolate-sphere',
            'madagascar-vanilla-mille-feuille',
            'pistachio-rose-entremet',
            'yuzu-white-chocolate-cheesecake',
            'caramelized-pear-and-almond-tart',
            'imperial-saffron-champagne-cocktail',
            'smoked-fig-bourbon-reserve',
            'white-peach-jasmine-elixir',
            'black-truffle-espresso-martini',
            'golden-yuzu-honey-sparkler',
        ];

        DB::table('menu_items')
            ->whereIn('slug', $legacyMenuItemSlugs)
            ->update([
                'is_visible' => false,
                'is_available' => false,
                'is_purchasable' => false,
                'updated_at' => now(),
            ]);

        foreach (
            [
                'appetizers' => 'Appetizers',
                'main-courses' => 'Main Courses',
                'beverages' => 'Beverages',
            ] as $slug => $name
        ) {
            DB::table('menu_categories')
                ->where('slug', $slug)
                ->where('name', $name)
                ->update([
                    'is_visible' => false,
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Preserve the archive decision to avoid re-exposing retired placeholders.
     */
    public function down(): void
    {
        // Intentionally irreversible: no records are deleted by this migration.
    }
};
