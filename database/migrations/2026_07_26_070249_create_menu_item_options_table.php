<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create selectable options belonging to a menu-item option group.
     */
    public function up(): void
    {
        Schema::create(
            'menu_item_options',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('menu_item_option_group_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string('name', 120);

                $table->unsignedInteger('additional_price_cents')
                    ->default(0);

                $table->boolean('is_available')
                    ->default(true);

                $table->unsignedInteger('sort_order')
                    ->default(0);

                $table->timestamps();

                $table->unique(
                    [
                        'menu_item_option_group_id',
                        'name',
                    ],
                    'menu_item_options_group_name_unique',
                );

                $table->index(
                    [
                        'menu_item_option_group_id',
                        'is_available',
                        'sort_order',
                    ],
                    'menu_item_options_public_order_index',
                );
            },
        );
    }

    /**
     * Drop the item-options table.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_item_options');
    }
};
