<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Record coupon consumption only for successfully placed orders.
     */
    public function up(): void
    {
        Schema::create(
            'coupon_usages',
            function (Blueprint $table): void {
                $table->id();

                $table
                    ->foreignId('coupon_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table
                    ->foreignId('order_id')
                    ->unique()
                    ->constrained()
                    ->cascadeOnDelete();

                $table
                    ->foreignId('user_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->string('code', 64);
                $table->unsignedBigInteger('discount_cents');
                $table->timestamp('used_at');

                $table->timestamps();

                $table->index([
                    'coupon_id',
                    'used_at',
                ]);
            },
        );
    }

    /**
     * Remove coupon usage records during rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
    }
};
