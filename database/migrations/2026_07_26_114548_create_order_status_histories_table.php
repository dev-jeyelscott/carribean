<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create append-only order status history records.
     */
    public function up(): void
    {
        Schema::create(
            'order_status_histories',
            function (Blueprint $table): void {
                $table->id();

                $table
                    ->foreignId('order_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table
                    ->foreignId('changed_by_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table
                    ->string('previous_status', 32)
                    ->nullable();

                $table->string('new_status', 32);

                $table->text('public_note')->nullable();
                $table->text('internal_note')->nullable();

                $table->timestamps();

                $table->index([
                    'order_id',
                    'created_at',
                ]);
            },
        );
    }

    /**
     * Remove order-status history during rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
    }
};
