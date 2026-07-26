<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Insert missing ordering settings without overwriting existing values.
     */
    public function up(): void
    {
        $timestamp = now();

        $settings = [
            [
                'key' => 'accepting_online_orders',
                'value' => '0',
                'group' => 'ordering',
            ],
            [
                'key' => 'online_orders_closed_message',
                'value' => 'Online ordering is temporarily unavailable. Please check back soon.',
                'group' => 'ordering',
            ],
            [
                'key' => 'accepted_delivery_zip_codes',
                'value' => '',
                'group' => 'ordering',
            ],
            [
                'key' => 'delivery_fee_cents',
                'value' => '0',
                'group' => 'ordering',
            ],
            [
                'key' => 'delivery_minimum_cents',
                'value' => '0',
                'group' => 'ordering',
            ],
            [
                'key' => 'tax_rate_basis_points',
                'value' => '0',
                'group' => 'ordering',
            ],
            [
                'key' => 'cash_at_pickup_enabled',
                'value' => '1',
                'group' => 'payments',
            ],
            [
                'key' => 'cash_on_delivery_enabled',
                'value' => '0',
                'group' => 'payments',
            ],
            [
                'key' => 'pickup_instructions',
                'value' => 'Pickup instructions will be provided after the restaurant confirms your order.',
                'group' => 'ordering',
            ],
            [
                'key' => 'delivery_instructions',
                'value' => 'Local delivery is available only within accepted ZIP codes.',
                'group' => 'ordering',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('site_settings')->insertOrIgnore([
                ...$setting,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }
    }

    /**
     * Preserve administrator configuration during rollback.
     *
     * Removing these records could delete edited production values, so this
     * data migration intentionally has a non-destructive rollback.
     */
    public function down(): void
    {
        //
    }
};
