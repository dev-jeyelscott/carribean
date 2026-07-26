<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Store Stripe webhook deliveries and their processing state.
     */
    public function up(): void
    {
        Schema::create(
            'payment_webhook_events',
            function (Blueprint $table): void {
                $table->id();

                $table
                    ->string('provider', 32)
                    ->default('stripe');

                $table->string(
                    'provider_event_id',
                    191,
                );

                $table->string(
                    'event_type',
                    191,
                );

                $table->json('payload');

                $table
                    ->timestamp('received_at')
                    ->useCurrent();

                $table
                    ->timestamp('processed_at')
                    ->nullable();

                $table
                    ->timestamp('failed_at')
                    ->nullable();

                $table
                    ->text('failure_message')
                    ->nullable();

                $table->timestamps();

                $table->unique([
                    'provider',
                    'provider_event_id',
                ]);

                $table->index([
                    'provider',
                    'event_type',
                ]);

                $table->index('processed_at');
            },
        );
    }

    /**
     * Remove stored webhook deliveries during rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'payment_webhook_events',
        );
    }
};
