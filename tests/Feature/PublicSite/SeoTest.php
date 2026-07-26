<?php

use App\Models\BlogPost;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('adds canonical and non-production robots metadata', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(
            '<link',
            false,
        )
        ->assertSee(
            'rel="canonical"',
            false,
        )
        ->assertSee(
            'content="noindex, nofollow"',
            false,
        )
        ->assertSee(
            'application/ld+json',
            false,
        );
});

it('returns an XML sitemap containing public content', function (): void {
    $page = Page::create([
        'slug' => 'privacy-policy',
        'title' => 'Privacy Policy',
        'content' => '<p>Approved policy.</p>',
        'is_published' => true,
    ]);

    $post = BlogPost::create([
        'title' => 'Sitemap Story',
        'slug' => 'sitemap-story',
        'body' => '<p>Public story.</p>',
        'is_published' => true,
    ]);

    $this->get(route('sitemap'))
        ->assertOk()
        ->assertHeader(
            'Content-Type',
            'application/xml; charset=UTF-8',
        )
        ->assertSee(
            route('privacy-policy'),
            false,
        )
        ->assertSee(
            route('blog.show', $post),
            false,
        );
});

it('blocks crawlers outside production', function (): void {
    $this->get(route('robots'))
        ->assertOk()
        ->assertHeader(
            'Content-Type',
            'text/plain; charset=UTF-8',
        )
        ->assertSee(
            'Disallow: /',
            false,
        );
});
