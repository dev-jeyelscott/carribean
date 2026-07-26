<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's complete development baseline.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            SiteSettingSeeder::class,
            MenuSeeder::class,
            GalleryImageSeeder::class,
            PremiumPublicContentSeeder::class,
            InquirySeeder::class,
            PhaseTenContentSeeder::class,
        ]);
    }
}
