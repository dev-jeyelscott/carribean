<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a non-destructive staff workflow and acknowledgement marker.
     */
    public function up(): void
    {
        Schema::table('contact_inquiries', function (Blueprint $table): void {
            $table->string('status', 40)
                ->default('new')
                ->after('message')
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
        Schema::table('contact_inquiries', function (Blueprint $table): void {
            $table->dropIndex(['status']);

            $table->dropColumn([
                'status',
                'customer_acknowledgement_sent_at',
            ]);
        });
    }
};
