<?php

/**
 * Confirm that the redesigned homepage exposes every required public section
 * and its current primary editorial headings.
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
        ->assertSeeText('A little taste of the islands.')
        ->assertSeeText(
            'Rooted in the Caribbean. At home on the coast.',
        )
        ->assertSeeText('Find your next favorite');
});
