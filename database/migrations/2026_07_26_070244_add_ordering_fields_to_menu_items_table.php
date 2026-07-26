<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the non-destructive catalogue fields required for online ordering.
     */
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table): void {
            $table->unsignedInteger('price_cents')
                ->nullable();

            $table->string('image_alt_text', 255)
                ->nullable();

            $table->boolean('is_featured')
                ->default(false);

            $table->boolean('is_available')
                ->default(true);

            $table->boolean('is_purchasable')
                ->default(true);

            $table->json('dietary_labels')
                ->nullable();

            $table->text('allergen_information')
                ->nullable();

            $table->index(
                [
                    'is_visible',
                    'is_available',
                    'is_purchasable',
                ],
                'menu_items_ordering_state_index',
            );

            $table->index(
                [
                    'is_visible',
                    'is_featured',
                    'sort_order',
                ],
                'menu_items_featured_index',
            );
        });
    }

    /**
     * Remove only the fields added by this migration.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table): void {
            $table->dropIndex('menu_items_ordering_state_index');
            $table->dropIndex('menu_items_featured_index');

            $table->dropColumn([
                'price_cents',
                'image_alt_text',
                'is_featured',
                'is_available',
                'is_purchasable',
                'dietary_labels',
                'allergen_information',
            ]);
        });
    }
};
