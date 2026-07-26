<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create item-specific groups such as Size, Side, or Spice Level.
     */
    public function up(): void
    {
        Schema::create(
            'menu_item_option_groups',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('menu_item_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string('name', 120);

                $table->boolean('is_required')
                    ->default(false);

                $table->unsignedTinyInteger('minimum_selections')
                    ->default(0);

                $table->unsignedTinyInteger('maximum_selections')
                    ->default(1);

                $table->unsignedInteger('sort_order')
                    ->default(0);

                $table->timestamps();

                $table->unique(
                    [
                        'menu_item_id',
                        'name',
                    ],
                    'menu_item_option_groups_item_name_unique',
                );

                $table->index(
                    [
                        'menu_item_id',
                        'sort_order',
                    ],
                    'menu_item_option_groups_order_index',
                );
            },
        );
    }

    /**
     * Drop the option-group table.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_item_option_groups');
    }
};
