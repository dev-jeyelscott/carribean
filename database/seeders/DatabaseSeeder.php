<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the complete Coast & Cay development baseline.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            SiteSettingSeeder::class,
            PageSeeder::class,
            MenuSeeder::class,
            GalleryImageSeeder::class,
            ContactInquirySeeder::class,
            PhaseTenContentSeeder::class,
        ]);
    }
}
