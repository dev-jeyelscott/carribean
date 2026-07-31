<?php

use Illuminate\Support\Facades\File;

test('public entry registers the contact-only Alpine form component', function (): void {
    $appEntry = File::get(resource_path('js/app.js'));
    $contactFormScript = File::get(
        resource_path('js/forms/contact-form.js'),
    );
    $contactPage = File::get(
        resource_path('views/pages/contact.blade.php'),
    );
    $contactFormView = File::get(
        resource_path(
            'views/components/public/contact-form.blade.php',
        ),
    );

    expect($appEntry)
        ->toContain('import contactForm from "./forms/contact-form"')
        ->toContain('Alpine.data("contactForm", contactForm)')
        ->not->toContain('inquiryForm')
        ->not->toContain('forms/inquiry-form');

    expect($contactFormScript)
        ->toContain('export default function contactForm()')
        ->toContain('applyServerErrors')
        ->toContain('focusFirstError')
        ->not->toContain('fulfillmentType')
        ->not->toContain('delivery-address')
        ->not->toContain('order inquiry');

    expect($contactPage)
        ->toContain('<x-public.contact-form')
        ->not->toContain('x-data="inquiryForm"');

    expect($contactFormView)
        ->toContain('x-data="contactForm"')
        ->toContain('@submit.prevent="submit"')
        ->not->toContain('x-data="inquiryForm"');
});
