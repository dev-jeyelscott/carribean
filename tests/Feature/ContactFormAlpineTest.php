<?php

use Illuminate\Support\Facades\File;

test('public entry registers the contact-only Alpine form component', function (): void {
    $appEntry = File::get(
        resource_path('js/app.js'),
    );

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

    /*
     * Verify that the public JavaScript entry registers only the current
     * contact-form Alpine component.
     */
    expect($appEntry)
        ->toContain(
            'import contactForm from "./forms/contact-form"',
        )
        ->toContain(
            'Alpine.data("contactForm", contactForm)',
        )
        ->not->toContain('inquiryForm')
        ->not->toContain('forms/inquiry-form');

    /*
     * Verify that the Alpine component contains the expected submission and
     * validation behavior without legacy order-inquiry responsibilities.
     */
    expect($contactFormScript)
        ->toContain('export default function contactForm()')
        ->toContain('applyServerErrors')
        ->toContain('focusFirstError')
        ->not->toContain('fulfillmentType')
        ->not->toContain('delivery-address')
        ->not->toContain('order inquiry');

    /*
     * Verify that the Contact page composes the reusable form rather than
     * duplicating its Alpine state directly inside the page template.
     */
    expect($contactPage)
        ->toContain('<x-public.contact-form')
        ->not->toContain('x-data="inquiryForm"');

    /*
     * Verify that the reusable form owns the Alpine component binding and does
     * not expose retired inquiry workflows.
     */
    expect($contactFormView)
        ->toContain('x-data="contactForm"')
        ->toContain('@submit.prevent="submit"')
        ->not->toContain('x-data="inquiryForm"')
        ->not->toContain('Private event inquiry');
});
