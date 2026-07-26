<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the non-destructive coupon configuration table.
     */
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table): void {
            $table->id();

            $table
                ->string('code', 64)
                ->unique();

            $table->string('type', 32);

            $table
                ->unsignedBigInteger('fixed_discount_cents')
                ->nullable();

            $table
                ->unsignedSmallInteger('percentage_basis_points')
                ->nullable();

            $table
                ->unsignedBigInteger('minimum_subtotal_cents')
                ->default(0);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table
                ->unsignedInteger('usage_limit')
                ->nullable();

            $table
                ->unsignedInteger('times_used')
                ->default(0);

            $table
                ->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->index([
                'is_active',
                'starts_at',
                'expires_at',
            ]);
        });
    }

    /**
     * Remove the coupon table during an intentional rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
