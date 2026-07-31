<?php

use App\Models\GalleryImage;
use Database\Seeders\GalleryImageSeeder;
use Illuminate\Support\Facades\Storage;

test('gallery image seeder stores upload-like images idempotently', function (): void {
    Storage::fake('public');

    $expectedImageCount = 6;

    /*
     * Run the seeder once and capture its database and storage state.
     */
    $this->seed(GalleryImageSeeder::class);

    $firstSeededPaths = GalleryImage::query()
        ->orderBy('id')
        ->pluck('image_path')
        ->all();

    expect($firstSeededPaths)
        ->toHaveCount($expectedImageCount)
        ->each
        ->toMatch(
            '/^gallery\/[a-f0-9]{64}\.(?:jpg|png|webp)$/',
        );

    /*
     * Every bundled image should produce one unique content-addressed source
     * path in public storage.
     */
    expect(
        array_values(array_unique($firstSeededPaths)),
    )->toHaveCount($expectedImageCount);

    Storage::disk('public')->assertExists(
        $firstSeededPaths,
    );

    $firstStoredFiles = Storage::disk('public')
        ->allFiles('gallery');

    sort($firstStoredFiles);

    /*
     * Running the seeder again must reuse the same records and stored files.
     */
    $this->seed(GalleryImageSeeder::class);

    $secondSeededPaths = GalleryImage::query()
        ->orderBy('id')
        ->pluck('image_path')
        ->all();

    $secondStoredFiles = Storage::disk('public')
        ->allFiles('gallery');

    sort($secondStoredFiles);

    expect($secondSeededPaths)
        ->toBe($firstSeededPaths)
        ->and($secondStoredFiles)
        ->toBe($firstStoredFiles)
        ->and(GalleryImage::query()->count())
        ->toBe($expectedImageCount);
});
