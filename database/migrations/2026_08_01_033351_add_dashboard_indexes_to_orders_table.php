<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add indexes used by date-range and fulfillment dashboard queries.
     */
    public function up(): void
    {
        Schema::table(
            'orders',
            function (Blueprint $table): void {
                $table->index(
                    'placed_at',
                    'orders_dashboard_placed_at_index',
                );

                $table->index(
                    [
                        'fulfillment_method',
                        'placed_at',
                    ],
                    'orders_dashboard_fulfillment_placed_index',
                );
            },
        );
    }

    /**
     * Remove only the dashboard-specific indexes.
     */
    public function down(): void
    {
        Schema::table(
            'orders',
            function (Blueprint $table): void {
                $table->dropIndex(
                    'orders_dashboard_placed_at_index',
                );

                $table->dropIndex(
                    'orders_dashboard_fulfillment_placed_index',
                );
            },
        );
    }
};
