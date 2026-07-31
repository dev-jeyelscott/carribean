<?php

use App\Models\SiteSetting;
use Database\Seeders\SiteSettingSeeder;

/**
 * Verify the development seeder creates a complete California restaurant and
 * ordering baseline without duplicating setting records.
 */
it('seeds coherent california restaurant and ordering settings', function (): void {
    $this->seed(SiteSettingSeeder::class);
    $this->seed(SiteSettingSeeder::class);

    expect(
        SiteSetting::query()
            ->where('key', 'restaurant_name')
            ->count(),
    )
        ->toBe(1)
        ->and(SiteSetting::value('restaurant_name'))
        ->toBe('Coast & Cay')
        ->and(SiteSetting::value('address'))
        ->toBe('Downtown Santa Monica, CA 90401')
        ->and(SiteSetting::acceptedDeliveryZipCodes())
        ->toBe([
            '90401',
            '90402',
            '90403',
            '90404',
            '90405',
            '90291',
            '90292',
            '90066',
            '90230',
        ])
        ->and(SiteSetting::acceptsDeliveryZip('90401'))
        ->toBeTrue()
        ->and(SiteSetting::acceptsDeliveryZip('90292'))
        ->toBeTrue()
        ->and(SiteSetting::acceptsDeliveryZip('99999'))
        ->toBeFalse()
        ->and(SiteSetting::deliveryFeeCents())
        ->toBe(750)
        ->and(SiteSetting::deliveryMinimumCents())
        ->toBe(3_500)
        ->and(SiteSetting::taxRateBasisPoints())
        ->toBe(1_075)
        ->and(SiteSetting::acceptingOnlineOrders())
        ->toBeTrue()
        ->and(SiteSetting::cashAtPickupEnabled())
        ->toBeTrue()
        ->and(SiteSetting::cashOnDeliveryEnabled())
        ->toBeTrue();
});
