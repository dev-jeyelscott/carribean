<?php

/**
 * Confirm that the redesigned homepage exposes every required public section.
 */
it('renders the full-screen homepage experience', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('data-home-section-pager', false)
        ->assertSee('id="home"', false)
        ->assertSee('id="featured"', false)
        ->assertSee('id="story"', false)
        ->assertSee('id="menu-explorer"', false)
        ->assertSee('id="gallery-preview"', false)
        ->assertSee('id="visit"', false)
        ->assertSeeText('Taste the Caribbean.')
        ->assertSeeText('Featured Dishes')
        ->assertSeeText('Rooted in Tradition.')
        ->assertSeeText('Find your next favorite');
});
