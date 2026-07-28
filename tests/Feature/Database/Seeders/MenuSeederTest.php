<?php

use App\Models\MenuItem;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('seeds a demo product image for every menu item', function (): void {
    Storage::fake('public');

    $this->seed(MenuSeeder::class);

    $menuItems = MenuItem::query()
        ->orderBy('id')
        ->get();

    expect($menuItems)
        ->not->toBeEmpty();

    expect($menuItems->count())
        ->toBeGreaterThan(6);

    foreach ($menuItems as $menuItem) {
        expect($menuItem->image_path)
            ->toBeString()
            ->not->toBeEmpty()
            ->toEndWith('.png');

        Storage::disk('public')->assertExists(
            $menuItem->image_path,
        );
    }

    expect(
        $menuItems
            ->pluck('image_path')
            ->unique()
            ->count(),
    )->toBe(6);
});

it('assigns demo images deterministically when reseeded', function (): void {
    Storage::fake('public');

    $this->seed(MenuSeeder::class);

    $initialImagePaths = MenuItem::query()
        ->orderBy('id')
        ->pluck('image_path', 'id')
        ->all();

    $this->seed(MenuSeeder::class);

    $reseededImagePaths = MenuItem::query()
        ->orderBy('id')
        ->pluck('image_path', 'id')
        ->all();

    expect($reseededImagePaths)
        ->toBe($initialImagePaths);
});
