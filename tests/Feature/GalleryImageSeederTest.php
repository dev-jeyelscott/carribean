<?php

use App\Models\GalleryImage;
use Database\Seeders\GalleryImageSeeder;
use Illuminate\Support\Facades\Storage;

test('gallery image seeder stores upload-like images idempotently', function (): void {
    Storage::fake('public');

    $this->seed(GalleryImageSeeder::class);

    $firstSeededPaths = GalleryImage::query()
        ->orderBy('id')
        ->pluck('image_path')
        ->all();

    expect($firstSeededPaths)
        ->toHaveCount(6)
        ->each->toMatch(
            '/^gallery\/[a-f0-9]{64}\.(?:jpg|png|webp)$/',
        );

    expect(array_unique($firstSeededPaths))->toHaveCount(6);

    Storage::disk('public')->assertExists($firstSeededPaths);

    $firstStoredFiles = Storage::disk('public')
        ->allFiles('gallery');

    $this->seed(GalleryImageSeeder::class);

    $secondSeededPaths = GalleryImage::query()
        ->orderBy('id')
        ->pluck('image_path')
        ->all();

    expect($secondSeededPaths)->toBe($firstSeededPaths);

    expect(
        Storage::disk('public')->allFiles('gallery'),
    )->toBe($firstStoredFiles);

    expect(GalleryImage::query()->count())->toBe(6);
});
