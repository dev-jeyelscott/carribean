<?php

it('renders the complete public homepage layout', function (): void {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('id="main-content"', escape: false)
        ->assertSeeText(
            'Island Hospitality, Made for the California Coast',
        );
});
