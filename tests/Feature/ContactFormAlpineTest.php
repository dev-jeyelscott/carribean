<?php

use Illuminate\Support\Facades\File;

test('public entry registers the contact-only Alpine form component', function (): void {
    $appEntry = File::get(resource_path('js/app.js'));
    $contactForm = File::get(resource_path('js/forms/contact-form.js'));
    $contactView = File::get(resource_path('views/pages/contact.blade.php'));

    expect($appEntry)
        ->toContain('import contactForm from "./forms/contact-form"')
        ->toContain('Alpine.data("contactForm", contactForm)')
        ->not->toContain('inquiryForm')
        ->not->toContain('forms/inquiry-form');

    expect($contactForm)
        ->toContain('export default function contactForm()')
        ->toContain('applyServerErrors')
        ->toContain('focusFirstError')
        ->not->toContain('fulfillmentType')
        ->not->toContain('delivery-address')
        ->not->toContain('order inquiry');

    expect($contactView)
        ->toContain('x-data="contactForm"')
        ->not->toContain('x-data="inquiryForm"');
});
