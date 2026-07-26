<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create immutable item and selected-option snapshots.
     */
    public function up(): void
    {
        Schema::create(
            'order_items',
            function (Blueprint $table): void {
                $table->id();

                $table
                    ->foreignId('order_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table
                    ->foreignId('menu_item_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->string('name');
                $table->text('description')->nullable();

                $table->unsignedBigInteger(
                    'base_unit_price_cents',
                );

                $table
                    ->unsignedSmallInteger('quantity')
                    ->default(1);

                $table
                    ->json('selected_options')
                    ->nullable();

                $table
                    ->unsignedBigInteger('option_total_cents')
                    ->default(0);

                $table->unsignedBigInteger('unit_price_cents');
                $table->unsignedBigInteger('line_total_cents');

                $table->timestamps();

                $table->index([
                    'order_id',
                    'menu_item_id',
                ]);
            },
        );
    }

    /**
     * Remove order-item snapshots during rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
