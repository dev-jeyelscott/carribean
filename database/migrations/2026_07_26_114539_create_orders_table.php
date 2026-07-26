<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create immutable restaurant order records and total snapshots.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();

            $table
                ->char('public_id', 26)
                ->unique();

            $table
                ->string('order_number', 32)
                ->nullable()
                ->unique();

            $table
                ->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 30);

            $table->string('status', 32);
            $table->string('payment_status', 32);
            $table->string('fulfillment_method', 32);
            $table->string('payment_method', 32);

            $table
                ->char('currency', 3)
                ->default('USD');

            $table
                ->unsignedBigInteger('subtotal_cents')
                ->default(0);

            $table
                ->unsignedBigInteger('discount_cents')
                ->default(0);

            $table
                ->unsignedBigInteger('tax_cents')
                ->default(0);

            $table
                ->unsignedBigInteger('delivery_cents')
                ->default(0);

            $table
                ->unsignedBigInteger('grand_total_cents')
                ->default(0);

            $table
                ->unsignedSmallInteger('tax_rate_basis_points')
                ->default(0);

            $table
                ->string('coupon_code', 64)
                ->nullable();

            $table
                ->json('coupon_snapshot')
                ->nullable();

            $table
                ->text('customer_note')
                ->nullable();

            $table
                ->text('internal_note')
                ->nullable();

            $table
                ->string('checkout_idempotency_token', 64)
                ->unique();

            $table->timestamp('placed_at');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index([
                'status',
                'placed_at',
            ]);

            $table->index([
                'payment_status',
                'placed_at',
            ]);

            $table->index([
                'user_id',
                'placed_at',
            ]);
        });
    }

    /**
     * Remove orders during an intentional rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
