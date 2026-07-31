<?php

use App\Models\SiteSetting;
use App\Rules\ValidSiteSettingValue;
use Illuminate\Support\Facades\Validator;

it('applies field-specific validation to public contact link settings', function (
    string $key,
    string $value,
    bool $expectedToPass,
): void {
    $validator = Validator::make(
        ['value' => $value],
        ['value' => [new ValidSiteSettingValue($key)]],
    );

    expect($validator->passes())->toBe($expectedToPass);
})->with([
    'valid email' => [
        'email',
        'hello@example.test',
        true,
    ],
    'invalid email' => [
        'email',
        'not-an-email',
        false,
    ],
    'valid phone' => [
        'phone',
        '+63 (917) 123-4567',
        true,
    ],
    'unsafe phone characters' => [
        'phone',
        '+63 917<script>',
        false,
    ],
    'valid HTTPS map URL' => [
        'map_link',
        'https://maps.example.test/restaurant',
        true,
    ],
    'valid HTTP social URL' => [
        'facebook_url',
        'http://facebook.example.test/restaurant',
        true,
    ],
    'unsupported URL scheme' => [
        'instagram_url',
        'javascript:alert(1)',
        false,
    ],
    'malformed URL' => [
        'tiktok_url',
        'not-a-url',
        false,
    ],
]);

it('renders valid public contact links with a normalized phone target', function (): void {
    foreach ([
        'phone' => '+63 (917) 123-4567',
        'email' => 'hello@example.test',
        'map_link' => 'https://maps.example.test/restaurant',
        'facebook_url' => 'https://facebook.example.test/restaurant',
        'instagram_url' => 'https://instagram.example.test/restaurant',
        'tiktok_url' => 'https://tiktok.example.test/@restaurant',
    ] as $key => $value) {
        SiteSetting::query()->create([
            'key' => $key,
            'value' => $value,
            'group' => 'contact',
        ]);
    }

    $this->get(route('contact.create'))
        ->assertOk()
        ->assertSee('+63 (917) 123-4567')
        ->assertSee(
            'href="tel:+639171234567"',
            false,
        )
        ->assertDontSee(
            'href="tel:+63 (917) 123-4567"',
            false,
        )
        ->assertSee(
            'href="mailto:hello@example.test"',
            false,
        )
        ->assertSee(
            'href="https://maps.example.test/restaurant"',
            false,
        )
        ->assertSee(
            'href="https://facebook.example.test/restaurant"',
            false,
        )
        ->assertSee(
            'href="https://instagram.example.test/restaurant"',
            false,
        )
        ->assertSee(
            'href="https://tiktok.example.test/@restaurant"',
            false,
        );
});

it('does not expose malformed public contact values stored in the database', function (): void {
    $invalidSettings = [
        'phone' => '+63 917<script>',
        'email' => 'not-an-email',
        'map_link' => 'javascript:alert(1)',
        'facebook_url' => 'not-a-url',
        'instagram_url' => 'ftp://example.test/restaurant',
        'tiktok_url' => 'data:text/html,unsafe',
    ];

    foreach ($invalidSettings as $key => $value) {
        SiteSetting::query()->create([
            'key' => $key,
            'value' => $value,
            'group' => 'contact',
        ]);
    }

    SiteSetting::forgetCachedValues();

    $publicContacts = SiteSetting::publicContactMap();

    /*
     * Invalid stored values must be removed at the shared public-settings
     * boundary before they reach any public Blade template.
     */
    foreach (array_keys($invalidSettings) as $key) {
        expect(
            array_key_exists($key, $publicContacts),
        )->toBeFalse();
    }

    /*
     * Safe development fallbacks may still produce valid telephone or email
     * links. The regression contract is that none of the malformed stored
     * values or unsupported schemes reach the response.
     */
    $this->get(route('contact.create'))
        ->assertOk()
        ->assertDontSee(
            '+63 917&lt;script&gt;',
            false,
        )
        ->assertDontSee(
            'not-an-email',
            false,
        )
        ->assertDontSee(
            'javascript:alert(1)',
            false,
        )
        ->assertDontSee(
            'not-a-url',
            false,
        )
        ->assertDontSee(
            'ftp://example.test/restaurant',
            false,
        )
        ->assertDontSee(
            'data:text/html,unsafe',
            false,
        );
});
