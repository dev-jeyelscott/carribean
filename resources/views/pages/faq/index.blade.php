@php
    $mainEntities = $faqs
        ->map(function (\App\Models\Faq $faq): array {
            $plainAnswer = preg_replace(
                '/\s+/',
                ' ',
                strip_tags($faq->answer),
            );

            return [
                '@type' => 'Question',
                'name' => $faq->question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => trim(
                        is_string($plainAnswer)
                            ? $plainAnswer
                            : '',
                    ),
                ],
            ];
        })
        ->values()
        ->all();

    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $mainEntities,
    ];
@endphp

<x-layouts.public
    title="Frequently Asked Questions"
    description="Answers about reservations, online ordering, pickup, delivery, payments, and dining at Coast & Cay."
    :canonical="route('faq')"
    :structured-data="$structuredData">
    <section
        class="relative isolate overflow-hidden bg-brand-palm-dark
            pb-20 pt-36 text-white lg:pb-24">
        <div
            class="absolute inset-0 -z-10
                bg-[radial-gradient(circle_at_78%_20%,rgba(242,199,107,0.28),transparent_28%),radial-gradient(circle_at_12%_75%,rgba(32,111,124,0.34),transparent_34%)]">
        </div>

        <div class="public-container">
            <p class="public-eyebrow text-brand-sun">
                Helpful Information
            </p>

            <h1
                class="mt-5 max-w-4xl font-display text-5xl
                    leading-[0.96] sm:text-7xl">
                Frequently asked questions.
            </h1>

            <p
                class="mt-7 max-w-2xl text-base leading-8
                    text-white/75 sm:text-lg">
                Find quick answers about visiting, reservations,
                ordering, pickup, and local delivery.
            </p>
        </div>
    </section>

    <section
        class="public-island-pattern bg-brand-cream
            py-20 lg:py-28">
        <div class="public-container">
            <div class="mx-auto max-w-3xl">
                @if ($faqs->isEmpty())
                    <div
                        class="rounded-island bg-white p-10
                            text-center shadow-island">
                        <h2
                            class="font-display text-3xl
                                text-brand-palm-dark">
                            Questions are being prepared.
                        </h2>

                        <p class="mt-5 leading-8 text-brand-muted">
                            Contact the restaurant directly and our team
                            will be pleased to help.
                        </p>

                        <a
                            href="{{ route('contact.create') }}"
                            class="public-button-primary mt-8">
                            Contact Us
                        </a>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($faqs as $faq)
                            <details
                                class="group rounded-[1.5rem]
                                    border border-brand-palm/10
                                    bg-white shadow-island">
                                <summary
                                    class="flex min-h-16 cursor-pointer
                                        list-none items-center
                                        justify-between gap-5 px-6 py-5
                                        font-semibold text-brand-palm-dark
                                        marker:hidden sm:px-8">
                                    <span>
                                        {{ $faq->question }}
                                    </span>

                                    <span
                                        class="text-2xl text-brand-coral
                                            transition duration-300
                                            group-open:rotate-45
                                            motion-reduce:transition-none"
                                        aria-hidden="true">
                                        +
                                    </span>
                                </summary>

                                <div
                                    class="border-t border-brand-palm/10
                                        px-6 py-6 text-base leading-8
                                        text-brand-muted sm:px-8
                                        [&_a]:font-semibold
                                        [&_a]:text-brand-coral-dark
                                        [&_a]:underline
                                        [&_ol]:list-decimal
                                        [&_ol]:pl-6
                                        [&_ul]:list-disc
                                        [&_ul]:pl-6">
                                    {!! str($faq->answer)->sanitizeHtml() !!}
                                </div>
                            </details>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
</x-layouts.public>
