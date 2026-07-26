<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create immutable order address snapshots.
     */
    public function up(): void
    {
        Schema::create(
            'order_addresses',
            function (Blueprint $table): void {
                $table->id();

                $table
                    ->foreignId('order_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string('type', 32);
                $table->string('recipient_name');
                $table->string('street_address');
                $table->string('apartment_or_unit')->nullable();
                $table->string('city');
                $table->char('state', 2);
                $table->string('postal_code', 10);
                $table->string('phone', 30);
                $table->text('delivery_instructions')->nullable();

                $table->timestamps();

                $table->unique([
                    'order_id',
                    'type',
                ]);
            },
        );
    }

    /**
     * Remove order-address snapshots during rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_addresses');
    }
};
