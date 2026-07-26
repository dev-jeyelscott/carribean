@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'image' => null,
    'imageUrl' => null,
    'imageAlt' => '',
    'primaryLabel' => null,
    'primaryUrl' => null,
    'secondaryLabel' => null,
    'secondaryUrl' => null,
    'address' => null,
    'openingHours' => null,
    'fulfillmentLabel' => 'Pickup and local delivery available',
    'mapUrl' => null,
])

<section
    data-public-hero
    class="relative bg-primary-deep text-white">
    <div
        class="relative isolate flex min-h-[42rem] items-center
            overflow-hidden sm:min-h-[45rem] lg:min-h-[47rem]">
        <div data-gsap="hero-image" class="absolute inset-0 -z-30">
            <x-public.responsive-image
                :image="$image"
                :fallback-url="$imageUrl"
                :alt="$imageAlt"
                variant="hero"
                sizes="100vw"
                width="1920"
                height="1280"
                loading="eager"
                fetchpriority="high"
                img-class="h-full w-full object-cover object-center" />
        </div>

        <div
            class="absolute inset-0 -z-20
                bg-[linear-gradient(90deg,rgba(7,45,37,0.96)_0%,rgba(7,45,37,0.87)_32%,rgba(7,45,37,0.46)_64%,rgba(7,45,37,0.18)_100%)]">
        </div>

        <div
            class="absolute inset-0 -z-10
                bg-[linear-gradient(180deg,rgba(4,28,23,0.42)_0%,transparent_35%,rgba(4,28,23,0.48)_100%)]">
        </div>

        <div class="public-container pb-20 pt-32 sm:pt-36 lg:pt-40">
            <div data-gsap="hero-content" class="max-w-[43rem]">
                @if ($eyebrow)
                    <p
                        data-gsap-reveal
                        class="text-[0.68rem] font-semibold uppercase
                            tracking-[0.28em] text-white/80 sm:text-xs">
                        {{ $eyebrow }}
                    </p>
                @endif

                <h1
                    data-gsap-reveal
                    class="mt-6 font-display text-5xl leading-[1.02]
                        text-white sm:text-6xl lg:text-7xl">
                    {{ $title }}
                </h1>

                @if ($description)
                    <p
                        data-gsap-reveal
                        class="mt-7 max-w-xl text-base leading-8
                            text-white/78">
                        {{ $description }}
                    </p>
                @endif

                @if ($primaryLabel || $secondaryLabel)
                    <div
                        data-gsap-reveal
                        class="mt-9 flex flex-col gap-3 sm:flex-row">
                        @if ($primaryLabel && $primaryUrl)
                            <a
                                href="{{ $primaryUrl }}"
                                class="public-button-primary">
                                {{ $primaryLabel }}

                                <span class="ml-2" aria-hidden="true">
                                    &rarr;
                                </span>
                            </a>
                        @endif

                        @if ($secondaryLabel && $secondaryUrl)
                            <a
                                href="{{ $secondaryUrl }}"
                                class="public-button-secondary text-white">
                                {{ $secondaryLabel }}

                                <span class="ml-2" aria-hidden="true">
                                    &rarr;
                                </span>
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div
        class="border-t border-white/10 bg-primary-deep/95
            backdrop-blur">
        <dl
            class="public-container grid divide-y divide-white/10
                sm:grid-cols-3 sm:divide-x sm:divide-y-0">
            <div class="flex min-h-20 items-center gap-4 py-5 sm:px-5 first:pl-0">
                <svg
                    class="size-5 shrink-0 text-white"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    aria-hidden="true">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 21s7-5.25 7-12a7 7 0 1 0-14 0c0 6.75 7 12 7 12Z" />

                    <circle cx="12" cy="9" r="2.25" />
                </svg>

                <div class="min-w-0">
                    <dt class="sr-only">Location</dt>

                    <dd class="text-xs font-medium leading-5 text-white/82">
                        @if ($mapUrl)
                            <a
                                href="{{ $mapUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="transition hover:text-sun">
                                {{ $address ?: 'California location coming soon' }}
                            </a>
                        @else
                            {{ $address ?: 'California location coming soon' }}
                        @endif
                    </dd>
                </div>
            </div>

            <div class="flex min-h-20 items-center gap-4 py-5 sm:px-5">
                <svg
                    class="size-5 shrink-0 text-white"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    aria-hidden="true">
                    <circle cx="12" cy="12" r="8.25" />

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 7.5V12l3 1.75" />
                </svg>

                <div class="min-w-0">
                    <dt class="sr-only">Opening hours</dt>

                    <dd class="text-xs font-medium leading-5 text-white/82">
                        {{ $openingHours
                            ? str($openingHours)->squish()
                            : 'Opening hours coming soon' }}
                    </dd>
                </div>
            </div>

            <div class="flex min-h-20 items-center gap-4 py-5 sm:px-5">
                <svg
                    class="size-5 shrink-0 text-white"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    aria-hidden="true">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M5 8.5h14l-1 12H6l-1-12Z" />

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M8.5 9V6.75a3.5 3.5 0 0 1 7 0V9" />
                </svg>

                <div class="min-w-0">
                    <dt class="sr-only">Fulfillment</dt>

                    <dd class="text-xs font-medium leading-5 text-white/82">
                        {{ $fulfillmentLabel }}
                    </dd>
                </div>
            </div>
        </dl>
    </div>
</section>
