<?php

it('renders the approved island restaurant homepage sections', function (): void {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('data-home-motion', escape: false)
        ->assertSee('data-home-section-pager', escape: false)
        ->assertSee('data-public-hero', escape: false)
        ->assertSee(
            'aria-labelledby="featured-dishes-heading"',
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
        ->assertSeeText('Find your next favorite')
        ->assertSeeText('Food, warmth, and coastal evenings.')
        ->assertSeeText('Ready for good food and island vibes?');
});

it('does not publish unsupported restaurant claims', function (): void {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertDontSeeText('4.9')
        ->assertDontSeeText('Top Rated')
        ->assertDontSeeText('Loved by thousands');
});
