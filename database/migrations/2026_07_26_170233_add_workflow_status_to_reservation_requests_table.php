<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the agreed manual reservation-request lifecycle.
     */
    public function up(): void
    {
        Schema::table('reservation_requests', function (Blueprint $table): void {
            $table->string('status', 40)
                ->default('pending')
                ->after('special_requests')
                ->index();

            $table->timestamp('customer_acknowledgement_sent_at')
                ->nullable()
                ->after('notification_sent_at');
        });
    }

    /**
     * Remove only the Phase 10 workflow fields.
     */
    public function down(): void
    {
        Schema::table('reservation_requests', function (Blueprint $table): void {
            $table->dropIndex(['status']);

            $table->dropColumn([
                'status',
                'customer_acknowledgement_sent_at',
            ]);
        });
    }
};
