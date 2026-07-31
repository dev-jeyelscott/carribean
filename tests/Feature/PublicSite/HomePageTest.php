<?php

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders published journal posts on the homepage', function (): void {
    BlogPost::query()->create([
        'title' => 'A Night by the Coast',
        'slug' => 'a-night-by-the-coast',
        'excerpt' => 'An evening of Caribbean flavor and coastal hospitality.',
        'body' => '<p>Journal article content.</p>',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('From the Journal')
        ->assertSee('A Night by the Coast');
});

it('hides the homepage journal preview without published posts', function (): void {
    BlogPost::query()->create([
        'title' => 'Unpublished Kitchen Notes',
        'slug' => 'unpublished-kitchen-notes',
        'excerpt' => 'Draft content.',
        'body' => '<p>Draft journal article content.</p>',
        'is_published' => false,
        'published_at' => null,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('From the Journal')
        ->assertDontSee('Unpublished Kitchen Notes');
});
