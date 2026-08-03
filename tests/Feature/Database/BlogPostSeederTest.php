<?php

use App\Models\BlogPost;
use Database\Seeders\BlogPostSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds ten published blog posts', function (): void {
    $this->seed(BlogPostSeeder::class);

    expect(BlogPost::query()->count())
        ->toBe(10)
        ->and(BlogPost::query()->published()->count())
        ->toBe(10)
        ->and(BlogPost::query()->whereNotNull('published_at')->count())
        ->toBe(10);
});

it('can run repeatedly without creating duplicate blog posts', function (): void {
    $this->seed(BlogPostSeeder::class);
    $this->seed(BlogPostSeeder::class);

    expect(BlogPost::query()->count())
        ->toBe(10)
        ->and(BlogPost::query()->distinct()->count('slug'))
        ->toBe(10);
});
