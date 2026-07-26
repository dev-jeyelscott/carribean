<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create payment records for cash and Stripe transactions.
     */
    public function up(): void
    {
        Schema::create(
            'payments',
            function (Blueprint $table): void {
                $table->id();

                $table
                    ->foreignId('order_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string('provider', 32);

                $table
                    ->string('provider_payment_id', 191)
                    ->nullable()
                    ->unique();

                $table
                    ->string(
                        'provider_checkout_session_id',
                        191,
                    )
                    ->nullable()
                    ->unique();

                $table->string('payment_method', 32);
                $table->string('status', 32);

                $table->unsignedBigInteger('amount_cents');

                $table
                    ->char('currency', 3)
                    ->default('USD');

                $table->timestamp('paid_at')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->timestamp('refunded_at')->nullable();

                $table->text('failure_message')->nullable();

                $table->timestamps();

                $table->index([
                    'order_id',
                    'status',
                ]);
            },
        );
    }

    /**
     * Remove payment records during rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
