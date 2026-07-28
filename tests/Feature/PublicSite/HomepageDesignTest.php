<?php

it('renders the island restaurant mockup sections', function (): void {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('data-home-motion', escape: false)
        ->assertSee('data-public-hero', escape: false)
        ->assertSee(
            'aria-label="Restaurant highlights"',
            escape: false,
        )
        ->assertSeeText('Bold flavors. Warm hospitality.')
        ->assertSeeText('Freshly Prepared')
        ->assertSeeText('Caribbean Inspired')
        ->assertSeeText('Pickup & Delivery')
        ->assertSeeText('Easy Online Ordering')
        ->assertSeeText("Chef's Favorites")
        ->assertSeeText('Featured Dishes')
        ->assertSeeText('Rooted in Tradition.')
        ->assertSeeText('Made for Today.')
        ->assertSeeText('Explore by Category')
        ->assertSeeText('Made for Good Company')
        ->assertSeeText(
            'Ready for Good Food and Island Vibes?',
        );
});

it('does not publish unsupported restaurant claims', function (): void {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertDontSeeText('4.9')
        ->assertDontSeeText('Top Rated')
        ->assertDontSeeText('Loved by thousands');
});
