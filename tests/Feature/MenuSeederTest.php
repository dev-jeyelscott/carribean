<?php

use App\Models\MenuItem;
use Database\Seeders\MenuSeeder;
use Illuminate\Support\Facades\Storage;

test('menu seeder stores upload-like images idempotently', function (): void {
    Storage::fake('public');

    $this->seed(MenuSeeder::class);

    $firstSeededPaths = MenuItem::query()
        ->whereNotNull('image_path')
        ->orderBy('id')
        ->pluck('image_path')
        ->all();

    $firstSeededItemCount = MenuItem::query()->count();

    expect($firstSeededPaths)
        ->each
        ->toMatch('/^menu-items\/[a-f0-9]{64}\.(?:jpg|png|webp)$/');

    Storage::disk('public')->assertExists($firstSeededPaths);

    $firstStoredFiles = Storage::disk('public')
        ->allFiles('menu-items');

    $this->seed(MenuSeeder::class);

    $secondSeededPaths = MenuItem::query()
        ->whereNotNull('image_path')
        ->orderBy('id')
        ->pluck('image_path')
        ->all();

    expect($secondSeededPaths)->toBe($firstSeededPaths);
    expect(Storage::disk('public')->allFiles('menu-items'))
        ->toBe($firstStoredFiles);
    expect(MenuItem::query()->count())
        ->toBe($firstSeededItemCount);
});
