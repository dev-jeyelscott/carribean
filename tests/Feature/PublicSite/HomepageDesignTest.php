<?php

/**
 * Verify that the current island-inspired homepage exposes its required
 * structural sections and approved customer-facing content.
 */
it('renders the approved island restaurant homepage sections', function (): void {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('data-home-motion', escape: false)
        ->assertSee('data-home-section-pager', escape: false)
        ->assertSee('data-public-hero', escape: false)
        ->assertSee('id="home"', escape: false)
        ->assertSee('id="featured"', escape: false)
        ->assertSee('id="story"', escape: false)
        ->assertSee('id="menu-explorer"', escape: false)
        ->assertSee('id="gallery-preview"', escape: false)
        ->assertSee('id="visit"', escape: false)
        ->assertSee(
            'aria-labelledby="featured-dishes-heading"',
            escape: false,
        )
        ->assertSee(
            'aria-labelledby="home-story-heading"',
            escape: false,
        )
        ->assertSeeText('Bold flavors. Warm hospitality.')
        ->assertSeeText('Taste the Caribbean.')
        ->assertSeeText('Feel the Islands.')
        ->assertSeeText("Chef's favorites")
        ->assertSeeText('A little taste of the islands.')
        ->assertSeeText('Made fresh')
        ->assertSeeText('Easy ordering')
        ->assertSeeText(
            'Rooted in the Caribbean. At home on the coast.',
        )
        ->assertSeeText('Good food.')
        ->assertSeeText('Good people.')
        ->assertSeeText('Good energy.')
        ->assertSeeText('Find your next favorite')
        ->assertSeeText('Bold, Honest Flavor')
        ->assertSeeText('A Warm Welcome')
        ->assertSeeText('Easy From First Click')
        ->assertSeeText('Food, warmth, and coastal evenings.')
        ->assertSeeText('Ready for good food and island vibes?');
});

/**
 * Verify that development placeholders do not introduce unsupported social
 * proof or restaurant-performance claims.
 */
it('does not publish unsupported restaurant claims', function (): void {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertDontSeeText('4.9')
        ->assertDontSeeText('Top Rated')
        ->assertDontSeeText('Loved by thousands');
});
