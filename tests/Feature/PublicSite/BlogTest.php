<?php

use App\Livewire\PublicSite\BlogIndex;
use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

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

it('loads more published posts without pagination links', function (): void {
    foreach (range(1, 10) as $position) {
        BlogPost::create([
            'title' => "Published Story {$position}",
            'slug' => "published-story-{$position}",
            'excerpt' => "Journal excerpt {$position}.",
            'body' => "<p>Journal content {$position}.</p>",
            'is_published' => true,
            'published_at' => now()->subDays($position),
        ]);
    }

    Livewire::test(BlogIndex::class)
        ->assertSet('visiblePosts', 7)
        ->assertSee('Published Story 1')
        ->assertSee('Published Story 7')
        ->assertDontSee('Published Story 8')
        ->assertSee('More stories below')
        ->assertDontSee('pagination')
        ->call('loadMore')
        ->assertSet('visiblePosts', 10)
        ->assertSee('Published Story 8')
        ->assertSee('Published Story 9')
        ->assertSee('Published Story 10')
        ->assertDontSee('More stories below');
});

it('does not include drafts when loading additional posts', function (): void {
    foreach (range(1, 8) as $position) {
        BlogPost::create([
            'title' => "Visible Story {$position}",
            'slug' => "visible-story-{$position}",
            'body' => "<p>Visible content {$position}.</p>",
            'is_published' => true,
            'published_at' => now()->subDays($position),
        ]);
    }

    BlogPost::create([
        'title' => 'Unpublished Internal Draft',
        'slug' => 'unpublished-internal-draft',
        'body' => '<p>This must remain private.</p>',
        'is_published' => false,
    ]);

    Livewire::test(BlogIndex::class)
        ->call('loadMore')
        ->assertSee('Visible Story 8')
        ->assertDontSee('Unpublished Internal Draft');
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
