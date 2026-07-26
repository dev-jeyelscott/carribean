<?php

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists only published blog posts', function (): void {
    $published = BlogPost::create([
        'title' => 'Published Island Story',
        'slug' => 'published-island-story',
        'excerpt' => 'A public story.',
        'body' => '<p>Published content.</p>',
        'is_published' => true,
    ]);

    $draft = BlogPost::create([
        'title' => 'Private Draft Story',
        'slug' => 'private-draft-story',
        'body' => '<p>Draft content.</p>',
        'is_published' => false,
    ]);

    $this->get(route('blog.index'))
        ->assertOk()
        ->assertSee($published->title)
        ->assertDontSee($draft->title);

    $this->get(route('blog.show', $published))
        ->assertOk()
        ->assertSee($published->title);

    $this->get(route('blog.show', $draft))
        ->assertNotFound();
});

it('shows journal navigation only when a post is published', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Journal');

    BlogPost::create([
        'title' => 'A Published Story',
        'slug' => 'a-published-story',
        'body' => '<p>Story content.</p>',
        'is_published' => true,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Journal')
        ->assertSee('A Published Story');
});
