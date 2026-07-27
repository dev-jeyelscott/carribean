<?php

use Illuminate\Support\Facades\File;

test('development seeders use the Coast and Cay scope and brand voice', function (): void {
    $pageSeeder = File::get(
        database_path('seeders/PageSeeder.php'),
    );

    $gallerySeeder = File::get(
        database_path('seeders/GalleryImageSeeder.php'),
    );

    $databaseSeeder = File::get(
        database_path('seeders/DatabaseSeeder.php'),
    );

    expect($pageSeeder)
        ->toContain('Coast & Cay')
        ->toContain('online-order support')
        ->not->toContain('Le Jardin')
        ->not->toContain('Reservation Request')
        ->not->toContain('Order Inquiry');

    expect($gallerySeeder)
        ->toContain('Coastal Dining Room')
        ->toContain('Island-Inspired Signature Plate')
        ->not->toContain('fine-dining');

    expect($databaseSeeder)
        ->toContain('PageSeeder::class')
        ->toContain('ContactInquirySeeder::class')
        ->not->toContain('PremiumPublicContentSeeder::class');

    expect(
        preg_match(
            '/^\s*InquirySeeder::class,\s*$/m',
            $databaseSeeder,
        ),
    )->toBe(0);
});

test('active public views do not expose retired workflow copy', function (): void {
    foreach (
        [
            'home.blade.php',
            'menu.blade.php',
            'gallery.blade.php',
            'contact.blade.php',
        ] as $filename
    ) {
        $source = File::get(
            resource_path('views/pages/'.$filename),
        );

        expect($source)
            ->not->toContain('Le Jardin')
            ->not->toContain('Reservation Request')
            ->not->toContain('Order Inquiry')
            ->not->toContain('Banquet Hall')
            ->not->toContain('private-event')
            ->not->toContain('private dining');
    }
});
