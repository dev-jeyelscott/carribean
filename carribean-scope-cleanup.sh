#!/usr/bin/env bash
set -euo pipefail

cd "${HOME}/projects/carribean"

git switch develop
git pull --ff-only origin develop

if grep -Eq '^APP_ENV=production$' .env 2>/dev/null; then
    echo "This cleanup script is for the local develop environment only."
    echo "Do not run db:seed against production."
    exit 1
fi

mkdir -p \
    database/seeders \
    resources/js/forms \
    tests/Feature/Admin \
    tests/Feature/Jobs

touch \
    database/seeders/ContactInquirySeeder.php \
    resources/js/forms/contact-form.js \
    tests/Feature/ContactFormAlpineTest.php \
    tests/Feature/HomePageDesignTest.php \
    tests/Feature/MenuPageDesignTest.php \
    tests/Feature/PublicContentTest.php \
    tests/Feature/Jobs/SendContactInquiryNotificationTest.php

cat > database/seeders/ContactInquirySeeder.php <<'PHP'
<?php

namespace Database\Seeders;

use App\Models\ContactInquiry;
use Illuminate\Database\Seeder;

class ContactInquirySeeder extends Seeder
{
    /**
     * Seed development-only contact messages that match the active contact flow.
     */
    public function run(): void
    {
        ContactInquiry::updateOrCreate(
            [
                'email' => 'sophia@example.com',
                'subject' => 'Online order question',
            ],
            [
                'customer_name' => 'Sophia Williams',
                'phone' => '+1 (555) 401-3001',
                'message' => 'Hello, I have a question about pickup availability for an online order.',
                'is_read' => false,
            ],
        );

        ContactInquiry::updateOrCreate(
            [
                'email' => 'liam@example.com',
                'subject' => 'Menu question',
            ],
            [
                'customer_name' => 'Liam Carter',
                'phone' => '+1 (555) 401-3002',
                'message' => 'Do you have vegetarian options available on the current menu?',
                'is_read' => true,
            ],
        );
    }
}
PHP

cat > database/seeders/DatabaseSeeder.php <<'PHP'
<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the complete Coast & Cay development baseline.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            SiteSettingSeeder::class,
            PageSeeder::class,
            MenuSeeder::class,
            GalleryImageSeeder::class,
            ContactInquirySeeder::class,
            PhaseTenContentSeeder::class,
        ]);
    }
}
PHP

cat > database/seeders/PageSeeder.php <<'PHP'
<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Seed editable development placeholder content for public CMS pages.
     */
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'home',
                'title' => 'Island Hospitality, Made for the California Coast',
                'excerpt' => 'A warm gathering place for vibrant Caribbean dishes, thoughtful drinks, and relaxed hospitality.',
                'content' => 'Coast & Cay brings Caribbean warmth to California through generous food, relaxed hospitality, and a dining room made for gathering.',
                'meta_title' => 'Coast & Cay | Caribbean Restaurant in California',
                'meta_description' => 'Discover Caribbean food, online ordering, pickup, local delivery, and relaxed California dining at Coast & Cay.',
                'is_published' => true,
            ],
            [
                'slug' => 'about',
                'title' => 'Caribbean Roots, California Rhythm',
                'excerpt' => 'Coast & Cay is being created as a warm neighborhood restaurant where Caribbean flavors and easy California hospitality meet.',
                'content' => <<<'HTML'
<p>Coast & Cay began with a simple idea: create a restaurant where the food feels vibrant, the welcome feels genuine, and guests always have a reason to stay a little longer.</p>

<p>Our development identity draws inspiration from the warmth, generosity, and bold flavors associated with Caribbean hospitality, paired with the relaxed pace of the California coast.</p>

<h2>A restaurant made for gathering</h2>

<p>The final restaurant story, heritage, menu, and photography will be supplied by the client and their consultant. Until then, Coast & Cay remains an editable development identity designed to be replaced without changing the website architecture.</p>
HTML,
                'meta_title' => 'About Coast & Cay',
                'meta_description' => 'Learn about the Coast & Cay restaurant concept, Caribbean inspiration, and relaxed California hospitality.',
                'is_published' => true,
            ],
            [
                'slug' => 'menu',
                'title' => 'Island Favorites, Made to Gather Around',
                'excerpt' => 'Explore colorful starters, generous mains, sweet finishes, and drinks made for slow afternoons and lively evenings.',
                'content' => 'Food made with warmth, color, and a generous sense of hospitality.',
                'meta_title' => 'Menu | Coast & Cay',
                'meta_description' => 'Explore the current Coast & Cay menu of starters, mains, desserts, drinks, and online-ordering options.',
                'is_published' => true,
            ],
            [
                'slug' => 'gallery',
                'title' => 'Food, Color, and Easy Evenings',
                'excerpt' => 'A look at the dishes, rooms, and warm details shaping the Coast & Cay experience.',
                'content' => 'Final restaurant photography will replace the current development imagery once approved assets are supplied.',
                'meta_title' => 'Gallery | Coast & Cay',
                'meta_description' => 'Explore the food, hospitality, and relaxed restaurant atmosphere of Coast & Cay.',
                'is_published' => true,
            ],
            [
                'slug' => 'contact',
                'title' => 'Come Say Hello',
                'excerpt' => 'Questions about the menu, an online order, directions, or the restaurant? Our team will be pleased to help.',
                'content' => 'Reach the restaurant by phone, email, location map, or the contact form.',
                'meta_title' => 'Contact Coast & Cay',
                'meta_description' => 'Contact Coast & Cay for menu questions, online-order support, directions, and restaurant information.',
                'is_published' => true,
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(
                ['slug' => $page['slug']],
                $page,
            );
        }
    }
}
PHP

cat > database/seeders/PhaseTenContentSeeder.php <<'PHP'
<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\Page;
use Illuminate\Database\Seeder;

class PhaseTenContentSeeder extends Seeder
{
    /**
     * Seed editable blog, FAQ, and legal-page development content.
     */
    public function run(): void
    {
        $this->seedDraftBlogPost();
        $this->seedFaqs();
        $this->seedDraftLegalPages();
    }

    /**
     * Create one draft article without exposing it publicly.
     */
    private function seedDraftBlogPost(): void
    {
        BlogPost::updateOrCreate(
            [
                'slug' => 'welcome-to-coast-and-cay',
            ],
            [
                'title' => 'Welcome to Coast & Cay',
                'excerpt' => 'A first look at the food, warmth, and island-inspired hospitality shaping Coast & Cay.',
                'body' => <<<'HTML'
<p>Coast &amp; Cay is being created as a warm place for vibrant Caribbean food, relaxed hospitality, and memorable meals shared together.</p>

<h2>A story still being written</h2>

<p>This development article remains a draft until the client supplies and approves the final restaurant story.</p>
HTML,
                'is_published' => false,
                'published_at' => null,
                'meta_title' => 'Welcome to Coast & Cay',
                'meta_description' => 'Discover the restaurant story and Caribbean hospitality behind Coast & Cay.',
            ],
        );
    }

    /**
     * Create editable frequently asked questions for active website features.
     */
    private function seedFaqs(): void
    {
        $faqs = [
            [
                'question' => 'Can I order without creating an account?',
                'answer' => '<p>Yes. Guest checkout is available, and you will receive a secure link to view your order after checkout.</p>',
                'sort_order' => 10,
            ],
            [
                'question' => 'Do you offer pickup and local delivery?',
                'answer' => '<p>Pickup and local delivery may be available when online ordering is enabled. Delivery is limited to accepted ZIP codes shown during checkout.</p>',
                'sort_order' => 20,
            ],
            [
                'question' => 'Can I pay with cash?',
                'answer' => '<p>Cash at pickup or cash on delivery appears during checkout only when that payment method is enabled by the restaurant.</p>',
                'sort_order' => 30,
            ],
            [
                'question' => 'How should I ask about allergens?',
                'answer' => '<p>Please contact the restaurant before ordering when you have a food allergy or dietary concern. Menu labels should not replace direct confirmation from restaurant staff.</p>',
                'sort_order' => 40,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(
                [
                    'question' => $faq['question'],
                ],
                [
                    ...$faq,
                    'is_visible' => true,
                ],
            );
        }
    }

    /**
     * Create legal-page shells that require client approval before publication.
     */
    private function seedDraftLegalPages(): void
    {
        $pages = [
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'meta_description' => 'Read the Coast & Cay privacy policy.',
            ],
            [
                'slug' => 'terms-and-conditions',
                'title' => 'Terms and Conditions',
                'meta_description' => 'Read the Coast & Cay website and ordering terms.',
            ],
            [
                'slug' => 'refund-and-cancellation-policy',
                'title' => 'Refund and Cancellation Policy',
                'meta_description' => 'Review the Coast & Cay refund and cancellation policy.',
            ],
            [
                'slug' => 'delivery-and-pickup-policy',
                'title' => 'Delivery and Pickup Policy',
                'meta_description' => 'Review Coast & Cay pickup and local delivery information.',
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(
                [
                    'slug' => $page['slug'],
                ],
                [
                    ...$page,
                    'excerpt' => 'This policy is being prepared for final client review.',
                    'content' => '<p>Replace this draft with client-approved policy content before publishing the page.</p>',
                    'meta_title' => $page['title'].' | Coast & Cay',
                    'is_published' => false,
                ],
            );
        }
    }
}
PHP

cat > database/seeders/GalleryImageSeeder.php <<'PHP'
<?php

namespace Database\Seeders;

use App\Models\GalleryImage;
use Illuminate\Database\Seeder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\File as HttpFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GalleryImageSeeder extends Seeder
{
    private const IMAGE_SOURCE_DIRECTORY = 'seeders/images/menu';

    private const IMAGE_STORAGE_DIRECTORY = 'gallery';

    private const MAX_IMAGE_SIZE_IN_BYTES = 2 * 1024 * 1024;

    /**
     * @var array<string, string>
     */
    private const IMAGE_EXTENSIONS_BY_MIME_TYPE = [
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
    ];

    /**
     * Seed reusable Coast & Cay gallery placeholders.
     */
    public function run(): void
    {
        $disk = Storage::disk('public');

        $disk->makeDirectory(self::IMAGE_STORAGE_DIRECTORY);

        $images = [
            [
                'title' => 'Coastal Dining Room',
                'alt_text' => 'Warm Coast & Cay dining room with relaxed island-inspired details',
                'image' => 'dining-room.webp',
                'category' => 'interior',
                'sort_order' => 1,
                'is_visible' => true,
            ],
            [
                'title' => 'Island-Inspired Signature Plate',
                'alt_text' => 'Colorful Caribbean-inspired dish prepared at Coast & Cay',
                'image' => 'Seared-Hokkaido-Scallops.webp',
                'category' => 'dish',
                'sort_order' => 2,
                'is_visible' => true,
            ],
            [
                'title' => 'Warm Coast & Cay Ambiance',
                'alt_text' => 'Warm evening ambiance inside Coast & Cay',
                'image' => 'warm-ambiance.webp',
                'category' => 'ambiance',
                'sort_order' => 3,
                'is_visible' => true,
            ],
        ];

        foreach ($images as $imageData) {
            $sourceFilename = $imageData['image'];

            unset($imageData['image']);

            $imagePath = $this->storeSeedImage(
                disk: $disk,
                sourceFilename: $sourceFilename,
            );

            GalleryImage::updateOrCreate(
                ['image_path' => $imagePath],
                [
                    ...$imageData,
                    'image_path' => $imagePath,
                ],
            );
        }
    }

    /**
     * Copy a trusted development image into public storage.
     */
    private function storeSeedImage(
        FilesystemAdapter $disk,
        string $sourceFilename,
    ): string {
        $sourcePath = database_path(
            self::IMAGE_SOURCE_DIRECTORY.'/'.$sourceFilename,
        );

        if (! File::isFile($sourcePath)) {
            throw new RuntimeException(
                "Gallery seed image does not exist: {$sourcePath}",
            );
        }

        if (! File::isReadable($sourcePath)) {
            throw new RuntimeException(
                "Gallery seed image is not readable: {$sourcePath}",
            );
        }

        $size = File::size($sourcePath);

        if ($size > self::MAX_IMAGE_SIZE_IN_BYTES) {
            throw new RuntimeException(
                sprintf(
                    'Gallery seed image exceeds the 2 MB upload limit (%d bytes): %s',
                    $size,
                    $sourcePath,
                ),
            );
        }

        $mimeType = File::mimeType($sourcePath);

        if (
            ! is_string($mimeType)
            || ! array_key_exists(
                $mimeType,
                self::IMAGE_EXTENSIONS_BY_MIME_TYPE,
            )
        ) {
            throw new RuntimeException(
                sprintf(
                    'Unsupported gallery seed image type "%s" for file: %s',
                    $mimeType ?: 'unknown',
                    $sourcePath,
                ),
            );
        }

        $contentHash = hash_file('sha256', $sourcePath);

        if (! is_string($contentHash)) {
            throw new RuntimeException(
                "Unable to hash gallery seed image: {$sourcePath}",
            );
        }

        $destinationFilename = $contentHash.'.'.self::IMAGE_EXTENSIONS_BY_MIME_TYPE[$mimeType];
        $destinationPath = self::IMAGE_STORAGE_DIRECTORY.'/'.$destinationFilename;

        if ($disk->exists($destinationPath)) {
            return $destinationPath;
        }

        $storedPath = $disk->putFileAs(
            self::IMAGE_STORAGE_DIRECTORY,
            new HttpFile($sourcePath),
            $destinationFilename,
            ['visibility' => 'public'],
        );

        if ($storedPath === false) {
            throw new RuntimeException(
                "Unable to store gallery seed image: {$destinationPath}",
            );
        }

        return $storedPath;
    }
}
PHP

cat > resources/js/forms/contact-form.js <<'JS'
const defaultSuccessTitle = "Message received";

function fieldErrorId(field) {
    return `${field.id || field.name}-error`;
}

function serverErrorMessage(payload) {
    if (typeof payload?.message === "string" && payload.message.trim() !== "") {
        return payload.message;
    }

    return "We could not send your message. Please review the form and try again.";
}

/**
 * Return the Alpine state used by the public contact form.
 */
export default function contactForm() {
    return {
        submitting: false,
        errors: {},
        successMessage: "",
        successTitle: defaultSuccessTitle,

        /**
         * Attach accessible validation behavior after Alpine initializes.
         */
        init() {
            const form = this.$root;

            this.successTitle =
                form.dataset.successTitle || defaultSuccessTitle;

            form.querySelectorAll("input, select, textarea").forEach((field) => {
                field.addEventListener("blur", () => {
                    this.validateField(field);
                });

                field.addEventListener("input", () => {
                    if (this.errors[field.name]) {
                        this.validateField(field);
                    }
                });

                field.addEventListener("change", () => {
                    if (this.errors[field.name]) {
                        this.validateField(field);
                    }
                });
            });
        },

        /**
         * Return a user-facing browser validation message for one field.
         */
        validationMessage(field) {
            if (field.validity.valueMissing) {
                return "This field is required.";
            }

            if (field.validity.typeMismatch) {
                return "Enter a valid value.";
            }

            if (field.validity.tooLong) {
                return `Use no more than ${field.maxLength} characters.`;
            }

            return field.validationMessage || "Check this field.";
        },

        /**
         * Validate one field and synchronize its accessible error state.
         */
        validateField(field) {
            if (!field.name || field.disabled) {
                return true;
            }

            if (field.checkValidity()) {
                this.clearFieldError(field);

                return true;
            }

            this.setFieldError(field, this.validationMessage(field));

            return false;
        },

        /**
         * Validate every active field before submitting the contact message.
         */
        validateForm() {
            let valid = true;

            this.$root
                .querySelectorAll("input, select, textarea")
                .forEach((field) => {
                    if (!this.validateField(field)) {
                        valid = false;
                    }
                });

            if (!valid) {
                this.focusFirstError();
            }

            return valid;
        },

        /**
         * Add one accessible field error to Alpine and the DOM.
         */
        setFieldError(field, message) {
            this.errors = {
                ...this.errors,
                [field.name]: message,
            };

            field.setAttribute("aria-invalid", "true");

            const errorId = fieldErrorId(field);
            const describedBy = new Set(
                (field.getAttribute("aria-describedby") || "")
                    .split(/\s+/)
                    .filter(Boolean),
            );

            describedBy.add(errorId);
            field.setAttribute(
                "aria-describedby",
                [...describedBy].join(" "),
            );

            let error = this.$root.querySelector(`#${CSS.escape(errorId)}`);

            if (!error) {
                error = document.createElement("p");
                error.id = errorId;
                error.className = "public-field-error";
                field.insertAdjacentElement("afterend", error);
            }

            error.textContent = message;
        },

        /**
         * Remove one field error and restore its accessible attributes.
         */
        clearFieldError(field) {
            const nextErrors = { ...this.errors };

            delete nextErrors[field.name];
            this.errors = nextErrors;

            field.removeAttribute("aria-invalid");

            const errorId = fieldErrorId(field);
            const describedBy = (field.getAttribute("aria-describedby") || "")
                .split(/\s+/)
                .filter((id) => id && id !== errorId);

            if (describedBy.length > 0) {
                field.setAttribute(
                    "aria-describedby",
                    describedBy.join(" "),
                );
            } else {
                field.removeAttribute("aria-describedby");
            }

            this.$root
                .querySelector(`#${CSS.escape(errorId)}`)
                ?.remove();
        },

        /**
         * Clear all client and server validation errors.
         */
        clearErrors() {
            this.errors = {};

            this.$root
                .querySelectorAll('[aria-invalid="true"]')
                .forEach((field) => {
                    field.removeAttribute("aria-invalid");
                });

            this.$root
                .querySelectorAll(".public-field-error")
                .forEach((error) => error.remove());
        },

        /**
         * Focus the first invalid field after validation fails.
         */
        focusFirstError() {
            this.$nextTick(() => {
                this.$root
                    .querySelector('[aria-invalid="true"]')
                    ?.focus();
            });
        },

        /**
         * Submit the contact form with JSON and preserve non-JavaScript fallback.
         */
        async submit() {
            this.successMessage = "";

            if (!this.validateForm()) {
                return;
            }

            this.submitting = true;

            try {
                const response = await fetch(this.$root.action, {
                    method: this.$root.method || "POST",
                    body: new FormData(this.$root),
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                });

                const payload = await response.json().catch(() => ({}));

                if (response.status === 422 && payload.errors) {
                    this.applyServerErrors(payload.errors);
                    this.focusFirstError();

                    return;
                }

                if (!response.ok) {
                    throw new Error(serverErrorMessage(payload));
                }

                this.clearErrors();
                this.$root.reset();
                this.successMessage =
                    payload.message || "Your message has been received.";
            } catch (error) {
                this.successMessage = "";
                this.errors = {
                    form:
                        error instanceof Error
                            ? error.message
                            : serverErrorMessage(),
                };
            } finally {
                this.submitting = false;
            }
        },

        /**
         * Apply Laravel validation errors to their corresponding form fields.
         */
        applyServerErrors(errors) {
            this.clearErrors();

            Object.entries(errors).forEach(([name, messages]) => {
                const field = this.$root.elements.namedItem(name);
                const message = Array.isArray(messages)
                    ? messages[0]
                    : messages;

                if (
                    field instanceof HTMLInputElement
                    || field instanceof HTMLSelectElement
                    || field instanceof HTMLTextAreaElement
                ) {
                    this.setFieldError(field, String(message));
                }
            });
        },
    };
}
JS

cat > resources/js/app.js <<'JS'
import Alpine from "alpinejs";
import contactForm from "./forms/contact-form";

window.Alpine = Alpine;

Alpine.data("contactForm", contactForm);

Alpine.start();

if (document.querySelector("[data-home-motion]")) {
    import("./public-animations")
        .then(({ initPublicAnimations }) => {
            initPublicAnimations();
        })
        .catch((error) => {
            console.error("Unable to initialize public animations.", error);
        });
}
JS

cat > resources/js/public-animations.js <<'JS'
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

const revealDistance = 28;

/**
 * Reveal child elements that opt into the shared public motion contract.
 */
function revealTimeline(target, options = {}) {
    const elements = target.querySelectorAll("[data-gsap-reveal]");

    if (!elements.length) {
        return;
    }

    gsap.set(elements, { autoAlpha: 0, y: revealDistance });

    gsap.timeline({
        scrollTrigger: {
            trigger: target,
            start: options.start ?? "top 82%",
            once: true,
        },
    }).to(elements, {
        autoAlpha: 1,
        duration: options.duration ?? 0.85,
        ease: "power3.out",
        stagger: options.stagger ?? 0.1,
        y: 0,
    });
}

/**
 * Reveal one managed image without blocking native rendering.
 */
function revealImage(target) {
    const image = target.querySelector("img");

    if (!image) {
        return;
    }

    gsap.set(target, { clipPath: "inset(0 0 100% 0)" });
    gsap.set(image, { scale: 1.04 });

    gsap.timeline({
        scrollTrigger: {
            trigger: target,
            start: "top 82%",
            once: true,
        },
    })
        .to(target, {
            clipPath: "inset(0 0 0% 0)",
            duration: 1,
            ease: "power4.out",
        })
        .to(
            image,
            {
                scale: 1,
                duration: 1.2,
                ease: "power3.out",
            },
            "<",
        );
}

/**
 * Add restrained image depth on scroll-capable devices.
 */
function addParallax(target, amount = 5) {
    const image = target.querySelector("img");

    if (!image) {
        return;
    }

    gsap.fromTo(
        image,
        { yPercent: -amount / 2 },
        {
            yPercent: amount / 2,
            ease: "none",
            scrollTrigger: {
                trigger: target,
                start: "top bottom",
                end: "bottom top",
                scrub: true,
            },
        },
    );
}

/**
 * Reveal decorative frames used by editorial sections.
 */
function revealFrame(target) {
    gsap.fromTo(
        target,
        { autoAlpha: 0, scale: 0.98 },
        {
            autoAlpha: 1,
            duration: 0.9,
            ease: "power3.out",
            scale: 1,
            scrollTrigger: {
                trigger: target.parentElement ?? target,
                start: "top 78%",
                end: "bottom top",
                once: true,
            },
        },
    );
}

/**
 * Animate the shared public hero and its content.
 */
function initializeHeroMotion(root, { desktop }) {
    const heroImage = root.querySelector('[data-gsap="hero-image"]');

    if (heroImage?.querySelector("img")) {
        gsap.fromTo(
            heroImage.querySelector("img"),
            { scale: 1.06 },
            {
                scale: 1,
                duration: 1.6,
                ease: "power4.out",
            },
        );

        addParallax(heroImage, desktop ? 3 : 1.5);
    }

    const heroContent = root.querySelector('[data-gsap="hero-content"]');

    if (!heroContent) {
        return;
    }

    const heroItems = heroContent.querySelectorAll("[data-gsap-reveal]");

    gsap.set(heroItems, { autoAlpha: 0, y: 24 });

    gsap.timeline({
        defaults: { ease: "power4.out" },
    }).to(heroItems, {
        autoAlpha: 1,
        duration: 1.1,
        stagger: 0.12,
        y: 0,
    });
}

/**
 * Restore final menu states when reduced motion is requested.
 */
function setMenuFinalStates(root) {
    gsap.set(
        root.querySelectorAll(
            '[data-menu-motion="hero-image"], [data-menu-motion="card-image"]',
        ),
        {
            clipPath: "inset(0 0 0% 0)",
            scale: 1,
        },
    );

    gsap.set(
        root.querySelectorAll(
            '[data-menu-motion="hero-item"], [data-menu-motion="full-heading"], [data-menu-motion="course-number"], [data-menu-motion="course-title"], [data-menu-motion="course-description"], [data-menu-motion="course-rule"], [data-menu-motion="card"], [data-menu-motion="card-copy"], [data-menu-motion="closing-item"], [data-menu-motion="closing-actions"]',
        ),
        {
            autoAlpha: 1,
            clipPath: "inset(0 0 0% 0)",
            scaleX: 1,
            x: 0,
            y: 0,
        },
    );
}

/**
 * Keep the menu category indicator aligned with the active course.
 */
function initializeMenuNavigation(root) {
    const categoryNav = root.querySelector(
        '[data-menu-motion="category-nav"]',
    );

    if (!categoryNav) {
        return () => {};
    }

    const links = [
        ...categoryNav.querySelectorAll("[data-menu-category-link]"),
    ];
    const courses = [
        ...root.querySelectorAll('[data-menu-motion="course"]'),
    ];
    const indicator = categoryNav.querySelector(
        "[data-menu-category-indicator]",
    );
    const cleanup = [];

    let activeLink =
        links.find(
            (link) => link.getAttribute("href") === window.location.hash,
        )
        ?? links.find(
            (link) => link.getAttribute("aria-current") === "true",
        )
        ?? links[0];

    const positionIndicator = () => {
        if (!indicator || !activeLink) {
            return;
        }

        gsap.to(indicator, {
            duration: 0.35,
            ease: "power3.out",
            width: activeLink.offsetWidth,
            x: activeLink.offsetLeft,
        });
    };

    const setActiveLink = (link) => {
        if (!link || link === activeLink) {
            return;
        }

        links.forEach((candidate) => {
            candidate.setAttribute(
                "aria-current",
                candidate === link ? "true" : "false",
            );
        });

        activeLink = link;
        positionIndicator();
    };

    links.forEach((link) => {
        const handleClick = () => setActiveLink(link);

        link.addEventListener("click", handleClick);
        cleanup.push(() => {
            link.removeEventListener("click", handleClick);
        });
    });

    courses.forEach((course, index) => {
        const trigger = ScrollTrigger.create({
            trigger: course,
            start: "top 42%",
            end: "bottom 42%",
            onEnter: () => setActiveLink(links[index]),
            onEnterBack: () => setActiveLink(links[index]),
        });

        cleanup.push(() => trigger.kill());
    });

    positionIndicator();
    ScrollTrigger.addEventListener("refresh", positionIndicator);
    cleanup.push(() => {
        ScrollTrigger.removeEventListener("refresh", positionIndicator);
    });

    return () => {
        cleanup.forEach((callback) => callback());
    };
}

/**
 * Initialize the menu page's restrained progressive motion.
 */
function initializeMenuMotion(root, { desktop, reducedMotion }) {
    if (reducedMotion) {
        setMenuFinalStates(root);

        return () => {};
    }

    initializeHeroMotion(root, { desktop });

    const navigationCleanup = initializeMenuNavigation(root);

    root.querySelectorAll('[data-menu-motion="course"]').forEach((course) => {
        const heading = course.querySelectorAll(
            '[data-menu-motion="course-number"], [data-menu-motion="course-title"], [data-menu-motion="course-description"]',
        );
        const rule = course.querySelector(
            '[data-menu-motion="course-rule"]',
        );
        const cards = course.querySelectorAll(
            '[data-menu-motion="card"]',
        );

        gsap.set(heading, { autoAlpha: 0, y: 20 });
        gsap.set(cards, { autoAlpha: 0, y: revealDistance });

        if (rule) {
            gsap.set(rule, {
                autoAlpha: 1,
                scaleX: 0,
                transformOrigin: "left center",
            });
        }

        const timeline = gsap.timeline({
            scrollTrigger: {
                trigger: course,
                start: "top 78%",
                once: true,
            },
        });

        timeline
            .to(heading, {
                autoAlpha: 1,
                duration: 0.7,
                ease: "power3.out",
                stagger: 0.08,
                y: 0,
            })
            .to(
                rule,
                {
                    scaleX: 1,
                    duration: 0.5,
                    ease: "power3.out",
                },
                "<0.1",
            )
            .to(
                cards,
                {
                    autoAlpha: 1,
                    duration: 0.75,
                    ease: "power3.out",
                    stagger: 0.08,
                    y: 0,
                },
                "<0.1",
            );
    });

    const closingCta = root.querySelector(
        '[data-menu-motion="closing-cta"]',
    );

    if (closingCta) {
        revealTimeline(closingCta);
    }

    return navigationCleanup;
}

/**
 * Initialize shared homepage, gallery, contact, and content-page motion.
 */
function initializeGeneralMotion(root, { desktop, reducedMotion }) {
    if (reducedMotion) {
        gsap.set(
            root.querySelectorAll(
                "[data-gsap-reveal], [data-gsap=card], [data-gsap=panel]",
            ),
            {
                autoAlpha: 1,
                x: 0,
                y: 0,
            },
        );

        return () => {};
    }

    initializeHeroMotion(root, { desktop });

    root.querySelectorAll('[data-gsap="section"]').forEach((section) => {
        revealTimeline(section);
    });

    root.querySelectorAll('[data-gsap="image"]').forEach((image) => {
        revealImage(image);
    });

    root.querySelectorAll('[data-gsap="parallax"]').forEach((image) => {
        addParallax(image, desktop ? 4 : 2);
    });

    root.querySelectorAll('[data-gsap="frame"]').forEach((frame) => {
        revealFrame(frame);
    });

    const menu = root.querySelector('[data-gsap="menu"]');

    if (menu) {
        const cards = menu.querySelectorAll('[data-gsap="card"]');

        gsap.set(cards, { autoAlpha: 0, y: revealDistance });

        gsap.timeline({
            scrollTrigger: {
                trigger: menu,
                start: "top 78%",
                once: true,
            },
        }).to(cards, {
            autoAlpha: 1,
            duration: 0.8,
            ease: "power3.out",
            stagger: 0.12,
            y: 0,
        });
    }

    root.querySelectorAll('[data-gsap="panel"]').forEach((panel, index) => {
        const offset = desktop
            ? { x: index % 2 === 0 ? -32 : 32, y: 0 }
            : { x: 0, y: revealDistance };

        gsap.fromTo(
            panel,
            {
                autoAlpha: 0,
                ...offset,
            },
            {
                autoAlpha: 1,
                duration: 0.9,
                ease: "power3.out",
                scrollTrigger: {
                    trigger: panel.parentElement ?? panel,
                    start: "top 78%",
                    once: true,
                },
                x: 0,
                y: 0,
            },
        );
    });

    return () => {};
}

/**
 * Initialize progressive public animations and return their cleanup callback.
 */
export function initPublicAnimations(
    root = document.querySelector("[data-home-motion]"),
) {
    if (!root) {
        return () => {};
    }

    const media = gsap.matchMedia();

    media.add(
        {
            reducedMotion: "(prefers-reduced-motion: reduce)",
            desktop: "(min-width: 1024px)",
        },
        (context) => {
            const {
                desktop = false,
                reducedMotion = false,
            } = context.conditions;

            if (root.dataset.publicMotion === "menu") {
                return initializeMenuMotion(root, {
                    desktop,
                    reducedMotion,
                });
            }

            return initializeGeneralMotion(root, {
                desktop,
                reducedMotion,
            });
        },
    );

    const cleanup = () => media.revert();

    if (import.meta.hot) {
        import.meta.hot.dispose(cleanup);
    }

    return cleanup;
}
JS

cat > resources/views/pages/contact.blade.php <<'BLADE'
<x-layouts.public
    :title="$page?->meta_title ?: 'Contact | Coast & Cay'"
    :description="$page?->meta_description ?: 'Contact Coast & Cay for menu questions, online-order support, directions, and restaurant information.'">
    <div data-home-motion>
        <section
            data-public-hero
            class="public-hero-viewport relative isolate flex items-center
                overflow-hidden bg-brand-palm-dark">
            @if ($heroImage?->image_url)
            <div data-gsap="hero-image" class="absolute inset-0 -z-30">
                <x-public.responsive-image
                    :image="$heroImage"
                    :alt="$heroImage->alt_text ?: $heroImage->title ?: 'Warm Coast and Cay dining room'"
                    variant="hero"
                    sizes="100vw"
                    width="1920"
                    height="1280"
                    loading="eager"
                    fetchpriority="high"
                    img-class="h-full w-full object-cover object-center" />
            </div>
            @else
            <div
                data-contact-hero-fallback
                class="absolute inset-0 -z-30
                    bg-[radial-gradient(circle_at_72%_28%,rgba(242,199,107,0.28),transparent_25%),linear-gradient(135deg,#206f7c,#0c342b_62%)]">
            </div>
            @endif

            <div
                class="absolute inset-0 -z-20
                    bg-[linear-gradient(to_bottom,rgba(12,52,43,0.38),rgba(12,52,43,0.62)_48%,rgba(12,52,43,0.98))]">
            </div>

            <div class="public-container pb-20 pt-36 sm:pb-24 sm:pt-44">
                <div data-gsap="hero-content" class="max-w-3xl">
                    <p
                        data-gsap-reveal
                        class="text-xs font-semibold uppercase
                            tracking-[0.3em] text-brand-sun">
                        Contact Coast & Cay
                    </p>

                    <h1
                        data-gsap-reveal
                        class="mt-6 font-display text-5xl leading-[0.96]
                            text-white sm:text-7xl">
                        {{ $page?->title ?: 'Come Say Hello' }}
                    </h1>

                    <p
                        data-gsap-reveal
                        class="mt-7 max-w-2xl text-base leading-8
                            text-white/78 sm:text-lg">
                        {{ $page?->excerpt ?: 'Questions about the menu, an online order, directions, or the restaurant? Our team will be pleased to help.' }}
                    </p>
                </div>
            </div>
        </section>

        <section
            id="contact-inquiry"
            data-gsap="section"
            class="public-island-pattern bg-brand-cream py-20 lg:py-28">
            <div
                class="public-container grid gap-10
                    lg:grid-cols-[0.72fr_1.28fr] lg:gap-14">
                <aside
                    data-gsap="panel"
                    class="rounded-island bg-brand-palm-dark p-7 text-white
                        shadow-island-dark sm:p-9">
                    <p class="public-eyebrow text-brand-sun">
                        Reach the Restaurant
                    </p>

                    <h2
                        class="mt-5 font-display text-4xl leading-tight">
                        We are here to help.
                    </h2>

                    <p class="mt-6 text-sm leading-7 text-white/68">
                        Send a message for menu questions, online-order support,
                        directions, accessibility information, or general
                        restaurant details.
                    </p>

                    <div class="mt-9 space-y-6 text-sm leading-7 text-white/72">
                        @if ($settings['phone'] ?? null)
                        <div>
                            <p class="text-xs font-semibold uppercase
                                tracking-[0.18em] text-brand-sun">
                                Phone
                            </p>
                            <p class="mt-1">{{ $settings['phone'] }}</p>
                        </div>
                        @endif

                        @if ($settings['email'] ?? null)
                        <div>
                            <p class="text-xs font-semibold uppercase
                                tracking-[0.18em] text-brand-sun">
                                Email
                            </p>
                            <a
                                href="mailto:{{ $settings['email'] }}"
                                class="mt-1 block break-words transition
                                    hover:text-brand-sun">
                                {{ $settings['email'] }}
                            </a>
                        </div>
                        @endif

                        @if ($settings['address'] ?? null)
                        <div>
                            <p class="text-xs font-semibold uppercase
                                tracking-[0.18em] text-brand-sun">
                                Address
                            </p>
                            <p class="mt-1">{{ $settings['address'] }}</p>
                        </div>
                        @endif

                        @if ($settings['opening_hours'] ?? null)
                        <div>
                            <p class="text-xs font-semibold uppercase
                                tracking-[0.18em] text-brand-sun">
                                Opening Hours
                            </p>
                            <p class="mt-1 whitespace-pre-line">
                                {{ $settings['opening_hours'] }}
                            </p>
                        </div>
                        @endif
                    </div>
                </aside>

                <div
                    data-gsap="panel"
                    class="rounded-island bg-white p-7 shadow-island sm:p-10">
                    <div>
                        <p class="public-eyebrow">Send a Message</p>

                        <h2
                            class="mt-4 font-display text-4xl
                                text-brand-forest">
                            How can we help?
                        </h2>

                        <p class="mt-4 max-w-2xl text-sm leading-7
                            text-brand-muted">
                            For help with an existing order, include the order
                            number in your message.
                        </p>
                    </div>

                    @if (session('success'))
                    <x-public.alert type="success" class="mt-7">
                        {{ session('success') }}
                    </x-public.alert>
                    @endif

                    <form
                        method="POST"
                        action="{{ route('contact-inquiries.store') }}"
                        x-data="contactForm"
                        @submit.prevent="submit"
                        data-success-title="Message received"
                        class="mt-8 space-y-6"
                        novalidate>
                        @csrf

                        <div
                            x-cloak
                            x-show="successMessage"
                            class="rounded-island border border-brand-palm/20
                                bg-brand-palm/5 p-4 text-sm text-brand-forest"
                            role="status"
                            aria-live="polite">
                            <strong x-text="successTitle"></strong>
                            <p class="mt-1" x-text="successMessage"></p>
                        </div>

                        <div
                            x-cloak
                            x-show="errors.form"
                            class="rounded-island border border-red-300
                                bg-red-50 p-4 text-sm text-red-800"
                            role="alert">
                            <span x-text="errors.form"></span>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="customer_name" class="public-label">
                                    Name
                                </label>
                                <input
                                    id="customer_name"
                                    name="customer_name"
                                    type="text"
                                    value="{{ old('customer_name') }}"
                                    class="public-input"
                                    maxlength="120"
                                    autocomplete="name"
                                    required>
                                @error('customer_name')
                                <p class="public-field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="public-label">
                                    Email
                                </label>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    value="{{ old('email') }}"
                                    class="public-input"
                                    maxlength="160"
                                    autocomplete="email"
                                    required>
                                @error('email')
                                <p class="public-field-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="phone" class="public-label">
                                    Phone <span class="font-normal">(optional)</span>
                                </label>
                                <input
                                    id="phone"
                                    name="phone"
                                    type="tel"
                                    value="{{ old('phone') }}"
                                    class="public-input"
                                    maxlength="40"
                                    autocomplete="tel"
                                    placeholder="+1 (555) 555-0142">
                                @error('phone')
                                <p class="public-field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="subject" class="public-label">
                                    Subject <span class="font-normal">(optional)</span>
                                </label>
                                <select
                                    id="subject"
                                    name="subject"
                                    class="public-input">
                                    <option value="">Choose a subject</option>
                                    @foreach ([
                                        'General restaurant question',
                                        'Menu question',
                                        'Online order support',
                                        'Directions or accessibility',
                                        'Website feedback',
                                    ] as $subject)
                                    <option
                                        value="{{ $subject }}"
                                        @selected(old('subject') === $subject)>
                                        {{ $subject }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('subject')
                                <p class="public-field-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="message" class="public-label">
                                Message
                            </label>
                            <textarea
                                id="message"
                                name="message"
                                rows="7"
                                class="public-input"
                                maxlength="5000"
                                required>{{ old('message') }}</textarea>
                            @error('message')
                            <p class="public-field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div
                            class="absolute left-[-10000px] top-auto size-px
                                overflow-hidden"
                            aria-hidden="true">
                            <label for="website">Website</label>
                            <input
                                id="website"
                                name="website"
                                type="text"
                                tabindex="-1"
                                autocomplete="off">
                        </div>

                        <button
                            type="submit"
                            class="public-button-primary w-full sm:w-auto"
                            :disabled="submitting">
                            <span x-show="! submitting">Send Message</span>
                            <span x-cloak x-show="submitting">Sending...</span>
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </div>
</x-layouts.public>
BLADE

cat > resources/views/pages/menu.blade.php <<'BLADE'
<x-layouts.public
    :title="$page?->meta_title ?: 'Menu'"
    :description="$page?->meta_description ?: 'Explore Caribbean-inspired dishes, shared plates, desserts, and drinks at Coast & Cay.'">
    @php
    $restaurantName = $settings['restaurant_name']
    ?? config('app.name');

    $menuItems = $categories->flatMap->menuItems;

    $heroItem = $menuItems->first(
    fn ($item) => filled($item->image_url),
    );
    @endphp

    <div data-home-motion data-public-motion="menu">
        <section
            data-public-hero
            data-menu-motion="hero"
            class="public-hero-viewport relative isolate flex items-center
                overflow-hidden bg-brand-palm-dark">
            @if ($heroItem?->image_url)
            <div
                data-gsap="hero-image"
                data-menu-motion="hero-image"
                class="absolute inset-0 -z-30 overflow-hidden">
                <x-public.responsive-image
                    :image="$heroItem"
                    alt=""
                    variant="hero"
                    sizes="100vw"
                    width="1920"
                    height="1280"
                    loading="eager"
                    fetchpriority="high"
                    img-class="absolute inset-0 h-full w-full object-cover" />
            </div>
            @else
            <div
                data-gsap="hero-image"
                data-menu-motion="hero-image"
                class="absolute inset-0 -z-30
                    bg-[radial-gradient(circle_at_72%_28%,rgba(242,199,107,0.28),transparent_25%),linear-gradient(135deg,#206f7c,#0c342b_62%)]">
            </div>
            @endif

            <div
                class="absolute inset-0 -z-20
                    bg-[linear-gradient(to_bottom,rgba(12,52,43,0.45),rgba(12,52,43,0.64)_45%,rgba(12,52,43,0.98))]">
            </div>

            <div
                class="absolute inset-0 -z-10 bg-gradient-to-r
                    from-brand-palm-dark/95 via-brand-palm-dark/55
                    to-brand-palm-dark/15">
            </div>

            <div
                class="public-container pb-16 pt-32
                    sm:pb-24 sm:pt-40 lg:pb-28 lg:pt-48">
                <div
                    data-gsap="hero-content"
                    data-menu-motion="hero-content"
                    class="max-w-4xl">
                    <p
                        data-gsap-reveal
                        data-menu-motion="hero-item"
                        class="text-xs font-semibold uppercase tracking-[0.34em]
                            text-brand-sun">
                        Our Menu
                    </p>

                    <h1
                        data-gsap-reveal
                        data-menu-motion="hero-item"
                        class="mt-6 max-w-4xl font-display text-5xl
                            leading-[0.95] text-white sm:text-7xl lg:text-8xl">
                        {{ $page?->title ?: 'Island Favorites, Made to Gather Around' }}
                    </h1>

                    <p
                        data-gsap-reveal
                        data-menu-motion="hero-item"
                        class="mt-7 max-w-2xl text-base leading-8
                            text-white/80 sm:text-lg">
                        {{ $page?->excerpt ?: 'Explore colorful starters, generous mains, sweet finishes, and drinks made for slow afternoons and lively evenings.' }}
                    </p>

                    <div
                        data-gsap-reveal
                        data-menu-motion="hero-item"
                        class="mt-10 flex flex-col gap-4 sm:flex-row">
                        <a
                            href="#menu-selections"
                            class="public-button-primary">
                            Explore the Menu
                        </a>
                    </div>
                </div>
            </div>
        </section>

        @if ($categories->isNotEmpty())
        <nav
            data-menu-motion="category-nav"
            class="border-y border-brand-palm/10 bg-brand-cream"
            aria-label="Menu categories">
            <div
                class="relative mx-auto flex max-w-7xl gap-7 overflow-x-auto
                    px-5 py-5 sm:px-6 lg:justify-center lg:px-10">
                @foreach ($categories as $category)
                <a
                    href="#category-{{ $category->slug }}"
                    data-menu-category-link
                    @if ($loop->first) aria-current="true" @endif
                    class="shrink-0 text-[0.68rem] font-semibold uppercase
                        tracking-[0.2em] text-brand-palm transition
                        hover:text-brand-coral-dark">
                    {{ $category->name }}
                </a>
                @endforeach

                <span
                    data-menu-category-indicator
                    class="pointer-events-none absolute bottom-0 left-0
                        h-0.5 w-0 bg-brand-coral"
                    aria-hidden="true"></span>
            </div>
        </nav>
        @endif

        <section
            id="menu-selections"
            class="public-island-pattern bg-brand-palm-dark py-24 lg:py-32">
            <div class="public-container">
                <x-public.section-heading
                    data-menu-motion="full-heading"
                    eyebrow="From Our Kitchen"
                    title="The {{ $restaurantName }} Menu"
                    :description="$page?->content ?: 'Food made with warmth, color, and a generous sense of hospitality.'" />

                <div class="mt-20 divide-y divide-white/15">
                    @forelse ($categories as $category)
                    <section
                        id="category-{{ $category->slug }}"
                        data-menu-motion="course"
                        class="scroll-mt-28 py-16 first:pt-0 last:pb-0
                            lg:py-24">
                        <div
                            class="grid gap-12 lg:grid-cols-[0.32fr_0.68fr]
                                lg:gap-16">
                            <div>
                                <p
                                    data-menu-motion="course-number"
                                    class="text-[0.68rem] font-semibold
                                        uppercase tracking-[0.28em]
                                        text-brand-sun">
                                    Selection
                                    {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </p>

                                <h2
                                    data-menu-motion="course-title"
                                    class="mt-4 font-display text-4xl
                                        leading-tight text-brand-cream
                                        sm:text-5xl">
                                    {{ $category->name }}
                                </h2>

                                @if ($category->description)
                                <p
                                    data-menu-motion="course-description"
                                    class="mt-5 max-w-md text-sm leading-7
                                        text-brand-cream/60">
                                    {{ $category->description }}
                                </p>
                                @endif

                                <div
                                    data-menu-motion="course-rule"
                                    class="mt-8 h-0.5 w-16 origin-left
                                        scale-x-0 bg-brand-coral"
                                    aria-hidden="true"></div>
                            </div>

                            <div
                                class="grid gap-x-8 gap-y-12 sm:grid-cols-2">
                                @forelse ($category->menuItems as $item)
                                <x-public.menu-card
                                    :item="$item"
                                    variant="luxury" />
                                @empty
                                <x-public.alert
                                    type="warning"
                                    class="sm:col-span-2">
                                    This selection is currently being prepared.
                                </x-public.alert>
                                @endforelse
                            </div>
                        </div>
                    </section>
                    @empty
                    <x-public.alert type="warning">
                        Our latest menu is currently being prepared.
                    </x-public.alert>
                    @endforelse
                </div>
            </div>
        </section>

        <section
            data-menu-motion="closing-cta"
            class="relative isolate overflow-hidden bg-brand-sand-soft py-24
                text-brand-forest lg:py-28">
            <div
                class="absolute inset-0 -z-10
                    bg-[radial-gradient(circle_at_85%_20%,rgba(230,110,80,0.18),transparent_28%)]">
            </div>

            <div
                class="public-container grid items-center gap-12
                    lg:grid-cols-[1fr_auto]">
                <div data-menu-motion="closing-copy">
                    <p
                        data-menu-motion="closing-item"
                        class="public-eyebrow">
                        Bring It Home
                    </p>

                    <h2
                        data-menu-motion="closing-item"
                        class="mt-5 max-w-3xl font-display text-4xl
                            leading-tight sm:text-5xl">
                        Ready for island flavor?
                    </h2>

                    <p
                        data-menu-motion="closing-item"
                        class="mt-6 max-w-2xl text-base leading-8
                            text-brand-muted">
                        Order online for pickup or local delivery, or contact
                        the restaurant team with a question about the menu.
                    </p>
                </div>

                <div
                    data-menu-motion="closing-actions"
                    class="flex flex-col gap-4 sm:flex-row lg:flex-col">
                    <a
                        href="{{ route('cart.index') }}"
                        class="public-button-primary">
                        Order Online
                    </a>

                    <a
                        href="{{ route('contact.create') }}"
                        class="public-button-secondary text-brand-palm">
                        Contact Us
                    </a>
                </div>
            </div>
        </section>
    </div>
</x-layouts.public>
BLADE

cat > resources/views/pages/gallery.blade.php <<'BLADE'
<x-layouts.public
    :title="$page?->meta_title ?: 'Gallery | Coast & Cay'"
    :description="$page?->meta_description ?: 'Explore Coast & Cay food, drinks, hospitality, and relaxed island-inspired details.'">
    @php
    $heroImage = $galleryImages->first();
    $supportingImages = $galleryImages->skip(1)->take(2)->values();
    $categories = $galleryImages
    ->pluck('category')
    ->filter()
    ->unique()
    ->values();
    @endphp

    <div data-home-motion data-gallery-motion>
        <section
            data-public-hero
            class="public-hero-viewport relative isolate flex items-center
                overflow-hidden bg-brand-palm-dark">
            @if ($heroImage?->image_url)
            <div data-gsap="hero-image" class="absolute inset-0 -z-30">
                <x-public.responsive-image
                    :image="$heroImage"
                    :alt="$heroImage->alt_text ?: $heroImage->title ?: 'Coast and Cay restaurant gallery'"
                    variant="hero"
                    sizes="100vw"
                    width="1920"
                    height="1280"
                    loading="eager"
                    fetchpriority="high"
                    img-class="h-full w-full object-cover object-center" />
            </div>
            @else
            <div
                class="absolute inset-0 -z-30
                    bg-[radial-gradient(circle_at_70%_28%,rgba(242,199,107,0.25),transparent_34%),linear-gradient(145deg,#206f7c,#0c342b_68%)]">
            </div>
            @endif

            <div
                class="absolute inset-0 -z-20
                    bg-[linear-gradient(to_bottom,rgba(12,52,43,0.38),rgba(12,52,43,0.28)_32%,rgba(12,52,43,0.94))]">
            </div>

            <div
                class="absolute inset-0 -z-10 bg-gradient-to-r
                    from-brand-palm-dark/88 via-brand-palm-dark/35
                    to-transparent">
            </div>

            <div
                class="public-container pb-12 pt-28
                    sm:pb-20 sm:pt-36 lg:pb-28 lg:pt-44">
                <div data-gsap="hero-content" class="max-w-4xl">
                    <p
                        data-gsap-reveal
                        class="text-xs font-semibold uppercase tracking-[0.38em]
                            text-brand-sun sm:text-sm">
                        The Gallery
                    </p>

                    <h1
                        data-gsap-reveal
                        class="mt-6 max-w-4xl font-display text-5xl
                            leading-[0.98] text-white sm:text-6xl lg:text-8xl">
                        {{ $page?->title ?: 'Food, Color, and Easy Evenings' }}
                    </h1>

                    <p
                        data-gsap-reveal
                        class="mt-7 max-w-2xl text-base leading-8
                            text-white/78 sm:text-lg">
                        {{ $page?->excerpt ?: 'A look at the dishes, rooms, and warm details shaping the Coast & Cay experience.' }}
                    </p>

                    <div
                        data-gsap-reveal
                        class="mt-10 flex flex-col gap-4 sm:flex-row">
                        <a
                            href="#gallery-collection"
                            class="public-button-primary">
                            View the Gallery
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section
            data-gsap="section"
            class="overflow-hidden bg-brand-cream py-24
                text-brand-forest lg:py-32">
            <div
                class="public-container grid items-center gap-16
                    lg:grid-cols-[0.9fr_1.1fr]">
                <div>
                    <x-public.section-heading
                        eyebrow="The Experience"
                        title="A taste of the island"
                        :description="$page?->content ?: 'From vibrant plates to a warm dining room, every image reflects generous Caribbean hospitality and California ease.'"
                        align="left"
                        theme="light" />

                    <div
                        data-gsap-reveal
                        class="mt-10 grid max-w-xl grid-cols-2 gap-8
                            border-t border-brand-palm/15 pt-8">
                        <div
                            data-gsap-counter
                            data-count-value="{{ $galleryImages->count() }}">
                            <p
                                data-gsap-count
                                class="font-display text-4xl text-brand-forest">
                                {{ $galleryImages->count() }}
                            </p>
                            <p
                                class="mt-2 text-xs font-semibold uppercase
                                    tracking-[0.2em] text-brand-coral-dark">
                                Moments on this page
                            </p>
                        </div>

                        <div
                            data-gsap-counter
                            data-count-value="{{ $categories->count() }}">
                            <p
                                data-gsap-count
                                class="font-display text-4xl text-brand-forest">
                                {{ $categories->count() }}
                            </p>
                            <p
                                class="mt-2 text-xs font-semibold uppercase
                                    tracking-[0.2em] text-brand-coral-dark">
                                Categories on this page
                            </p>
                        </div>
                    </div>
                </div>

                <div class="relative min-h-[27rem] sm:min-h-[34rem]">
                    <div
                        data-gsap="frame"
                        class="absolute left-0 top-0 h-[82%] w-[78%]
                            border border-brand-coral/45"
                        aria-hidden="true">
                    </div>

                    @if ($supportingImages->count() >= 2)
                    @foreach ($supportingImages as $image)
                    <figure
                        data-gsap="image"
                        @class([
                            'absolute overflow-hidden rounded-island bg-white shadow-island',
                            'left-5 top-5 h-[72%] w-[72%]' => $loop->first,
                            'bottom-0 right-0 h-[52%] w-[52%] border-8 border-brand-cream' => $loop->last,
                        ])>
                        @if ($image->image_url)
                        <x-public.responsive-image
                            :image="$image"
                            :alt="$image->alt_text ?: $image->title ?: 'Restaurant gallery image'"
                            variant="large"
                            sizes="(min-width: 1024px) 40vw, 75vw"
                            width="1200"
                            height="900"
                            img-class="h-full w-full object-cover" />
                        @endif
                    </figure>
                    @endforeach
                    @elseif ($heroImage?->image_url)
                    <figure
                        data-gsap="image"
                        class="absolute bottom-0 right-0 h-[88%] w-[88%]
                            overflow-hidden rounded-island bg-white
                            shadow-island">
                        <x-public.responsive-image
                            :image="$heroImage"
                            :alt="$heroImage->alt_text ?: $heroImage->title ?: 'Restaurant gallery image'"
                            variant="large"
                            sizes="(min-width: 1024px) 45vw, 88vw"
                            width="1200"
                            height="900"
                            img-class="h-full w-full object-cover" />
                    </figure>
                    @else
                    <div
                        class="absolute bottom-0 right-0 h-[88%] w-[88%]
                            rounded-island
                            bg-[radial-gradient(circle_at_65%_28%,rgba(242,199,107,0.3),transparent_34%),linear-gradient(145deg,#e8d6b8,#206f7c)]
                            shadow-island">
                    </div>
                    @endif
                </div>
            </div>
        </section>

        <section
            id="gallery-collection"
            data-gsap="gallery"
            class="scroll-mt-24 bg-brand-palm-dark py-24 lg:py-32">
            <div class="public-container">
                <x-public.section-heading
                    eyebrow="The Collection"
                    title="Food, hospitality, and coastal moments"
                    description="Explore colorful dishes, relaxed spaces, and the warm details that make Coast & Cay feel welcoming." />

                @if ($categories->isNotEmpty())
                <ul
                    class="mt-10 flex flex-wrap justify-center gap-3"
                    aria-label="Gallery categories">
                    @foreach ($categories as $category)
                    <li
                        data-gsap-reveal
                        class="rounded-full border border-white/15 px-4 py-2
                            text-[0.65rem] font-semibold uppercase
                            tracking-[0.22em] text-white/72">
                        {{ $category }}
                    </li>
                    @endforeach
                </ul>
                @endif

                @if ($galleryImages->isNotEmpty())
                <div
                    data-gsap="gallery-collection"
                    class="mt-14 grid gap-4 md:auto-rows-[18rem]
                        md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($galleryImages as $image)
                    @php
                    $patternIndex = $loop->index % 8;
                    $cardClass = match ($patternIndex) {
                    0 => 'md:col-span-2 md:row-span-2 lg:col-span-2',
                    3 => 'lg:row-span-2',
                    5 => 'md:col-span-2 lg:col-span-1',
                    6 => 'lg:col-span-2',
                    default => '',
                    };
                    @endphp

                    <x-public.gallery-card
                        :image="$image"
                        variant="editorial"
                        :class="$cardClass" />
                    @endforeach
                </div>

                @if ($galleryImages->hasPages())
                <div
                    data-gsap="section"
                    class="mt-14 border-t border-white/10 pt-10">
                    {{ $galleryImages->links() }}
                </div>
                @endif
                @else
                <x-public.alert type="warning" class="mt-14">
                    Our gallery is currently being curated. Please check back
                    soon for more Coast & Cay moments.
                </x-public.alert>
                @endif
            </div>
        </section>

        <section class="grid lg:grid-cols-2">
            <article
                data-gsap="panel"
                class="relative isolate flex min-h-[30rem] items-center
                    overflow-hidden bg-brand-ocean px-5 py-20 text-white
                    sm:px-10 lg:px-16">
                @if ($heroImage?->image_url)
                <div data-gsap="parallax" class="absolute inset-0 -z-20">
                    <x-public.responsive-image
                        :image="$heroImage"
                        alt=""
                        variant="large"
                        sizes="(min-width: 1024px) 50vw, 100vw"
                        width="1200"
                        height="900"
                        img-class="h-full w-full object-cover opacity-25" />
                </div>
                @endif

                <div class="absolute inset-0 -z-10 bg-brand-palm-dark/70"></div>

                <div data-gsap-reveal class="mx-auto max-w-lg text-center">
                    <p
                        class="text-xs font-semibold uppercase
                            tracking-[0.3em] text-brand-sun">
                        Island Hospitality
                    </p>
                    <h2
                        class="mt-5 font-display text-4xl leading-tight
                            sm:text-5xl">
                        Food made for sharing
                    </h2>
                    <p class="mt-6 text-base leading-8 text-white/72">
                        Coast & Cay brings vibrant food, thoughtful drinks,
                        and an easy welcome to every table.
                    </p>
                </div>
            </article>

            <article
                data-gsap="panel"
                class="relative isolate flex min-h-[30rem] items-center
                    overflow-hidden bg-brand-sand-soft px-5 py-20
                    text-brand-forest sm:px-10 lg:px-16">
                <div
                    class="absolute inset-0 -z-10
                        bg-[radial-gradient(circle_at_75%_22%,rgba(230,110,80,0.18),transparent_35%)]">
                </div>

                <div data-gsap-reveal class="mx-auto max-w-lg text-center">
                    <p
                        class="text-xs font-semibold uppercase
                            tracking-[0.3em] text-brand-coral-dark">
                        Need a Hand?
                    </p>
                    <h2
                        class="mt-5 font-display text-4xl leading-tight
                            sm:text-5xl">
                        Questions about the menu or an order?
                    </h2>
                    <p
                        class="mt-6 text-base leading-8 text-brand-muted">
                        Contact the restaurant team for menu questions,
                        online-order support, directions, or accessibility
                        information.
                    </p>
                    <a
                        href="{{ route('contact.create') }}"
                        class="public-button-primary mt-9">
                        Contact Us
                    </a>
                </div>
            </article>
        </section>
    </div>
</x-layouts.public>
BLADE

cat > resources/views/filament/widgets/recent-inquiries.blade.php <<'BLADE'
<x-filament-widgets::widget>
    <section
        class="admin-dashboard-panel"
        aria-labelledby="recent-inquiries-heading">
        <header class="admin-dashboard-panel__header">
            <div>
                <p class="admin-dashboard-panel__eyebrow">
                    Guest messages
                </p>

                <h2
                    id="recent-inquiries-heading"
                    class="admin-dashboard-panel__title">
                    Recent contact inquiries
                </h2>

                <p class="admin-dashboard-panel__description">
                    Review the latest customer messages and follow up manually.
                </p>
            </div>
        </header>

        <div
            wire:loading.flex
            class="admin-dashboard-state"
            role="status"
            aria-live="polite">
            <x-filament::icon
                icon="heroicon-o-arrow-path"
                class="admin-dashboard-state__icon
                    admin-dashboard-state__icon--spin" />

            <div>
                <strong>Loading contact inquiries</strong>
                <p>Preparing the latest customer messages.</p>
            </div>
        </div>

        <div wire:loading.remove>
            @if ($loadError)
            <div
                class="admin-dashboard-state admin-dashboard-state--error"
                role="status">
                <x-filament::icon
                    icon="heroicon-o-exclamation-triangle"
                    class="admin-dashboard-state__icon" />

                <div>
                    <strong>Contact inquiries are temporarily unavailable</strong>

                    <p>
                        The dashboard could not load recent contact inquiries.
                        The error has been recorded for investigation.
                    </p>
                </div>
            </div>
            @else
            <div class="admin-dashboard-inquiries">
                @forelse ($inquiries as $inquiry)
                <a
                    href="{{ $inquiry['url'] }}"
                    class="admin-dashboard-inquiry
                        {{ $inquiry['is_read'] ? '' : 'is-unread' }}"
                    aria-label="View {{ $inquiry['type'] }} from
                        {{ $inquiry['customer_name'] }}">
                    <div class="admin-dashboard-inquiry__content">
                        <div class="admin-dashboard-inquiry__heading">
                            <span class="admin-dashboard-inquiry__type">
                                {{ $inquiry['type'] }}
                            </span>

                            @unless ($inquiry['is_read'])
                            <span class="admin-dashboard-badge">
                                New
                            </span>
                            @endunless
                        </div>

                        <strong class="admin-dashboard-inquiry__name">
                            {{ $inquiry['customer_name'] }}
                        </strong>

                        <span class="admin-dashboard-inquiry__summary">
                            {{ $inquiry['summary'] }}
                        </span>
                    </div>

                    <div class="admin-dashboard-inquiry__meta">
                        <time
                            datetime="{{ $inquiry['created_at']->toIso8601String() }}">
                            {{ $inquiry['created_at']->diffForHumans() }}
                        </time>

                        <x-filament::icon
                            icon="heroicon-m-chevron-right"
                            class="admin-dashboard-inquiry__arrow" />
                    </div>
                </a>
                @empty
                <div class="admin-dashboard-state">
                    <x-filament::icon
                        icon="heroicon-o-inbox"
                        class="admin-dashboard-state__icon" />

                    <div>
                        <strong>No contact inquiries yet</strong>

                        <p>
                            New messages from the public contact form will
                            appear here.
                        </p>
                    </div>
                </div>
                @endforelse
            </div>
            @endif
        </div>
    </section>
</x-filament-widgets::widget>
BLADE

# Preserve the framework-maintained Laravel Boost guidance while replacing the
# obsolete project-specific AGENTS.md header.
tail -n +127 AGENTS.md > /tmp/carribean-agents-guidelines.md

cat > AGENTS.md <<'MD'
## Project Overview

Coast & Cay is a single-location Caribbean restaurant website and online-ordering application.

The public experience should feel warm, vibrant, hospitable, relaxed, and distinctly island-inspired without becoming visually noisy or culturally generic.

## Current Product Scope

Customers can:

- Learn about the restaurant.
- Browse menu categories and menu-item details.
- Configure item-specific options and add-ons.
- Use a server-side session cart.
- Complete guest or registered checkout.
- Choose pickup or ZIP-code-based local delivery.
- Pay through Stripe-hosted Checkout or an enabled cash method.
- View secure order status and order history.
- Read published blog posts, FAQs, and legal pages.
- Contact the restaurant through the public contact form.

Administrators use one Filament panel to manage:

- Menu categories and menu items.
- Item option groups and options.
- Coupons.
- Orders and customers.
- Blog posts, FAQs, pages, site settings, and gallery images.
- Contact inquiries.

## Retired Workflows

Do not restore, advertise, or imply these retired workflows:

- Reservation Request.
- Order Inquiry.
- Banquet Hall.
- Catering.
- Private-event or private-dining inquiry workflow.

Historical migrations may retain old schema history. Do not delete or rewrite migrations that may already have run. Current runtime routes, UI, seeders, tests, and documentation must not expose the retired workflows.

## Architecture Constraints

- Work only on `develop`.
- Use Laravel 13, PHP 8.4, Livewire 4, Flux UI, Filament 5, Alpine.js 3, Tailwind CSS 4, Vite 8, and GSAP.
- Use a Laravel monolith.
- Use MySQL, database sessions, and database queues.
- Use one Filament admin panel.
- Use one session cart per browser.
- Use one payment provider.
- Store money in integer cents.
- Recalculate all prices on the server.
- Snapshot order item, option, address, coupon, tax, and total data.
- Keep payment and order state changes transactional and idempotent.
- Prefer Laravel-native patterns and existing project components.
- Do not introduce Redis, microservices, a JavaScript SPA, a repository layer, a command bus, or an event bus without explicit approval.

## Explicitly Excluded

- Multiple restaurant locations.
- POS integration.
- Ingredient inventory and recipe costing.
- Scheduled ordering.
- Driver accounts and live driver tracking.
- Distance-based delivery fees.
- Kitchen display systems.
- Reservation or table-capacity systems.
- Catering or banquet workflows.
- Loyalty points, gift cards, wishlists, or product reviews.
- Newsletter platforms and social login.
- Saved customer address books.
- Multiple currencies or languages.
- Advanced coupon targeting.
- Refund management inside Filament.
- Native mobile applications.

## Implementation Expectations

- Keep the build simple, secure, production-ready, and maintainable.
- Inspect existing sibling files before creating or changing code.
- Use full server-side validation and authorization.
- Keep public content accessible and responsive.
- Respect reduced-motion preferences.
- Protect payment webhooks with signature verification and idempotency.
- Queue transactional mail after database commits.
- Add or update the minimum meaningful Pest coverage for every change.
- Run Pint, Larastan, Pest, and Vite build before committing.
- Do not create documentation files unless explicitly requested.
MD

cat /tmp/carribean-agents-guidelines.md >> AGENTS.md
rm -f /tmp/carribean-agents-guidelines.md

cat > docs/scope.md <<'MD'
# Locked Coast & Cay Scope

## Product

A single-location Caribbean restaurant website with online ordering.

Temporary development identity:

- Name: Coast & Cay
- Tagline: Caribbean warmth, California ease.
- Currency: USD

## Included

- Responsive public website
- Homepage
- About page
- Menu categories and item details
- Item-specific options and add-ons
- Session-based cart
- One coupon per order
- Guest checkout
- Customer registration and login
- Pickup
- ZIP-code-based local delivery
- Stripe-hosted Checkout
- Cash at pickup
- Cash on delivery
- Customer order history
- Secure guest order links
- Order status tracking
- Filament administration
- Menu, coupon, customer, and order management
- Contact inquiries
- Blog
- FAQ
- Legal pages
- Gallery
- Basic SEO
- Queued email notifications
- Automated tests
- GitHub Actions CI
- Production deployment documentation

## Retired and removed

The following legacy workflows must not be restored:

- Reservation Request
- Order Inquiry
- Banquet Hall
- Catering
- Private-event or private-dining inquiry workflow

Historical migrations may remain for audit and deployment safety. Runtime code,
routes, views, seeders, tests, navigation, sitemap entries, and documentation
must not expose these workflows.

## Architecture constraints

- One restaurant
- One currency
- One payment provider
- One Filament admin panel
- One session cart per browser
- One coupon per order
- One delivery-fee rule
- MySQL
- Database sessions
- Database queues
- No Redis requirement
- No JavaScript SPA
- No unnecessary repository layer
- No generic command bus or event bus
- No microservices

## Explicitly excluded

- Multiple restaurant locations
- POS integration
- Ingredient inventory
- Recipe costing
- Scheduled ordering
- Driver accounts
- Live delivery tracking
- Distance-based delivery fees
- Kitchen display system
- Reservation or table-capacity system
- Banquet or catering workflow
- Loyalty points
- Gift cards
- Wishlists
- Product reviews
- Newsletter platform
- Social login
- Saved address book
- Multiple currencies
- Multiple languages
- Advanced coupon targeting
- Refund management inside Filament
- Native mobile applications

## Pending client configuration

- Final restaurant name
- Logo
- Brand story
- Menu and prices
- Item options
- Address
- Delivery ZIP codes
- Delivery fee
- Delivery minimum
- Tax rate
- Operating hours
- Phone and email
- Social links
- Stripe credentials
- Legal content
- Final photographs
MD

cat > docs/roadmap.md <<'MD'
# Coast & Cay — Current Delivery Roadmap

## Current state

The application has moved beyond the original brochure and manual-inquiry
baseline. The active implementation now includes the restaurant catalogue,
session cart, checkout, payments, customer accounts, order tracking, CMS,
Filament administration, and CI foundations.

The retired Reservation Request, Order Inquiry, Banquet Hall, catering, and
private-event workflows must remain removed from runtime code and public copy.

## Remaining delivery priorities

### 1. Scope consistency

- Keep routes, navigation, sitemap, seeders, tests, and documentation aligned.
- Remove legacy Le Jardin and generic fine-dining placeholder copy.
- Keep Contact Inquiry as the only public inquiry workflow.
- Preserve historical migrations that may already have run.

### 2. Public experience

- Complete the Caribbean visual refresh across Home, Menu, About, Gallery,
  Contact, account, cart, checkout, and order pages.
- Keep motion progressive and reduced-motion safe.
- Replace development images and copy when approved client assets arrive.

### 3. Commerce validation

- Verify server-side price recalculation.
- Verify coupons, tax, delivery ZIP codes, and delivery minimums.
- Verify guest and registered checkout.
- Verify Stripe webhook signatures and idempotency.
- Verify cash-payment settings and order transitions.

### 4. Administration

- Verify menu, option, coupon, customer, order, CMS, gallery, and contact
  inquiry resources.
- Verify dashboard metrics and order actions.
- Keep one Filament panel and one administrator access model.

### 5. Content and SEO

- Replace development brand details with approved client content.
- Publish approved legal pages.
- Publish Blog navigation only when an article is public.
- Verify metadata, canonical URLs, sitemap, robots rules, and structured data.

### 6. Quality and launch

- Run Pint, Larastan, Pest, Vite build, and Playwright.
- Complete mobile, tablet, laptop, and desktop acceptance testing.
- Verify queue worker, scheduler, persistent storage, HTTPS, mail, Stripe
  webhook endpoint, and backups.
- Complete administrator handover and production smoke testing.

## Scope cut line

Do not add multiple locations, POS integration, inventory, scheduled ordering,
driver tracking, table reservations, banquet/catering workflows, loyalty,
gift cards, saved addresses, multiple currencies, or native applications
without a separately approved scope change.
MD

# Create a production-safe one-way migration that archives exact legacy
# placeholders without deleting historical records.
MIGRATION_FILE="$(
    find database/migrations -maxdepth 1 \
        -name '*_archive_retired_scope_placeholder_content.php' \
        | sort \
        | tail -n 1
)"

if [[ -z "${MIGRATION_FILE}" ]]; then
    ./vendor/bin/sail artisan make:migration \
        archive_retired_scope_placeholder_content \
        --no-interaction

    MIGRATION_FILE="$(
        find database/migrations -maxdepth 1 \
            -name '*_archive_retired_scope_placeholder_content.php' \
            | sort \
            | tail -n 1
    )"
fi

cat > "${MIGRATION_FILE}" <<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Archive exact legacy placeholders without deleting historical records.
     */
    public function up(): void
    {
        DB::table('pages')
            ->where('slug', 'contact')
            ->where(
                'meta_description',
                'Contact Coast & Cay for menu questions, directions, reservations, and restaurant information.',
            )
            ->update([
                'meta_description' => 'Contact Coast & Cay for menu questions, online-order support, directions, and restaurant information.',
                'updated_at' => now(),
            ]);

        $legacyFaq = DB::table('faqs')
            ->where(
                'question',
                'Is a reservation request automatically confirmed?',
            )
            ->first();

        if ($legacyFaq !== null) {
            $replacementExists = DB::table('faqs')
                ->where(
                    'question',
                    'Can I order without creating an account?',
                )
                ->exists();

            if ($replacementExists) {
                DB::table('faqs')
                    ->where('id', $legacyFaq->id)
                    ->update([
                        'is_visible' => false,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('faqs')
                    ->where('id', $legacyFaq->id)
                    ->update([
                        'question' => 'Can I order without creating an account?',
                        'answer' => '<p>Yes. Guest checkout is available, and you will receive a secure link to view your order after checkout.</p>',
                        'updated_at' => now(),
                    ]);
            }
        }

        $legacyMenuItemSlugs = [
            'truffle-crusted-beef-tenderloin',
            'miso-glazed-chilean-sea-bass',
            'herb-roasted-rack-of-lamb',
            'butter-poached-lobster-thermidor',
            'black-garlic-wagyu-striploin',
            'valrhona-dark-chocolate-sphere',
            'madagascar-vanilla-mille-feuille',
            'pistachio-rose-entremet',
            'yuzu-white-chocolate-cheesecake',
            'caramelized-pear-and-almond-tart',
            'imperial-saffron-champagne-cocktail',
            'smoked-fig-bourbon-reserve',
            'white-peach-jasmine-elixir',
            'black-truffle-espresso-martini',
            'golden-yuzu-honey-sparkler',
        ];

        DB::table('menu_items')
            ->whereIn('slug', $legacyMenuItemSlugs)
            ->update([
                'is_visible' => false,
                'is_available' => false,
                'is_purchasable' => false,
                'updated_at' => now(),
            ]);

        foreach (
            [
                'appetizers' => 'Appetizers',
                'main-courses' => 'Main Courses',
                'beverages' => 'Beverages',
            ] as $slug => $name
        ) {
            DB::table('menu_categories')
                ->where('slug', $slug)
                ->where('name', $name)
                ->update([
                    'is_visible' => false,
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Preserve the archive decision to avoid re-exposing retired placeholders.
     */
    public function down(): void
    {
        // Intentionally irreversible: no records are deleted by this migration.
    }
};
PHP

cat > tests/Feature/ContactFormAlpineTest.php <<'PHP'
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
PHP

cat > tests/Feature/PublicContentTest.php <<'PHP'
<?php

use Illuminate\Support\Facades\File;

test('development seeders use the Coast and Cay scope and brand voice', function (): void {
    $pageSeeder = File::get(database_path('seeders/PageSeeder.php'));
    $gallerySeeder = File::get(database_path('seeders/GalleryImageSeeder.php'));
    $databaseSeeder = File::get(database_path('seeders/DatabaseSeeder.php'));

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
        ->not->toContain('PremiumPublicContentSeeder::class')
        ->not->toContain('InquirySeeder::class');
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
        $source = File::get(resource_path('views/pages/'.$filename));

        expect($source)
            ->not->toContain('Le Jardin')
            ->not->toContain('Reservation Request')
            ->not->toContain('Order Inquiry')
            ->not->toContain('Banquet Hall')
            ->not->toContain('private-event')
            ->not->toContain('private dining');
    }
});
PHP

cat > tests/Feature/HomePageDesignTest.php <<'PHP'
<?php

use Illuminate\Support\Facades\File;

test('homepage renders the approved Caribbean restaurant sections', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertSeeText('Flavors worth sharing')
        ->assertSeeText('Caribbean warmth. California ease.')
        ->assertSeeText('Island favorites')
        ->assertSeeText('Dine your way')
        ->assertSeeText('A taste of the island')
        ->assertSeeText('We can’t wait to welcome you');
});

test('homepage exposes accessible navigation and progressive motion hooks', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('aria-controls="mobile-navigation"', false)
        ->assertSee(':aria-expanded="open.toString()"', false)
        ->assertSee('aria-label="Primary navigation"', false)
        ->assertSee('aria-label="Mobile navigation"', false)
        ->assertSee('data-home-motion', false)
        ->assertSee('data-gsap="hero-image"', false)
        ->assertSee('data-gsap="menu"', false)
        ->assertSee('data-gsap="panel"', false);
});

test('public motion no longer loads the retired home gallery carousel or delivery address helper', function (): void {
    $appEntry = File::get(resource_path('js/app.js'));
    $motionModule = File::get(resource_path('js/public-animations.js'));

    expect($appEntry)
        ->toContain('document.querySelector("[data-home-motion]")')
        ->toContain('import("./public-animations")');

    expect($motionModule)
        ->toContain('gsap.matchMedia()')
        ->toContain('reducedMotion')
        ->toContain('media.revert()')
        ->not->toContain('home-gallery-carousel')
        ->not->toContain('initHomeGalleryCarousel')
        ->not->toContain('animateDeliveryAddress');
});

test('homepage does not expose retired workflow labels', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertDontSeeText('Reservation Request')
        ->assertDontSeeText('Order Inquiry')
        ->assertDontSeeText('Banquet Hall')
        ->assertDontSeeText('Private Celebrations');
});
PHP

cat > tests/Feature/MenuPageDesignTest.php <<'PHP'
<?php

use App\Models\MenuCategory;
use App\Models\MenuItem;

test('menu page presents visible orderable items in the Coast and Cay layout', function (): void {
    $category = MenuCategory::query()->create([
        'name' => 'Island Favorites',
        'slug' => 'island-favorites',
        'description' => 'Caribbean dishes made for sharing.',
        'sort_order' => 10,
        'is_visible' => true,
    ]);

    MenuItem::query()->create([
        'menu_category_id' => $category->id,
        'name' => 'Island Jerk Chicken',
        'slug' => 'island-jerk-chicken',
        'description' => 'Flame-grilled jerk chicken with rice and peas.',
        'price_cents' => 2400,
        'sort_order' => 10,
        'is_visible' => true,
        'is_available' => true,
        'is_purchasable' => true,
    ]);

    $this->get(route('menu'))
        ->assertOk()
        ->assertSee('id="menu-selections"', false)
        ->assertSee('aria-label="Menu categories"', false)
        ->assertSee('href="#category-island-favorites"', false)
        ->assertSee('id="category-island-favorites"', false)
        ->assertSeeTextInOrder([
            'The Coast & Cay Menu',
            'Island Favorites',
            'Island Jerk Chicken',
            'Order Online',
        ])
        ->assertDontSeeText('Submit Order Inquiry')
        ->assertDontSeeText('Request a table');
});

test('menu page preserves progressive motion and native content', function (): void {
    $this->get(route('menu'))
        ->assertOk()
        ->assertSee('data-home-motion data-public-motion="menu"', false)
        ->assertSee('data-menu-motion="hero"', false)
        ->assertSee('data-menu-motion="category-nav"', false)
        ->assertSee('data-menu-category-link', false)
        ->assertSee('data-menu-motion="closing-cta"', false)
        ->assertSeeText('Order online for pickup or local delivery');
});
PHP

cat > tests/Feature/Jobs/SendContactInquiryNotificationTest.php <<'PHP'
<?php

use App\Jobs\SendContactInquiryNotification;
use App\Mail\ContactInquirySubmitted;
use App\Models\ContactInquiry;
use Illuminate\Support\Facades\Mail;

test('contact inquiry notification job sends the active contact mailable', function (): void {
    Mail::fake();

    $inquiry = ContactInquiry::query()->create([
        'customer_name' => 'Sophia Williams',
        'email' => 'sophia@example.com',
        'phone' => '+1 (555) 401-3001',
        'subject' => 'Online order question',
        'message' => 'Please help me with a pickup order question.',
        'is_read' => false,
    ]);

    (new SendContactInquiryNotification(
        contactInquiryId: $inquiry->id,
        recipient: 'restaurant@example.test',
    ))->handle();

    Mail::assertSent(
        ContactInquirySubmitted::class,
        fn (ContactInquirySubmitted $mail): bool => (
            $mail->hasTo('restaurant@example.test')
            && $mail->contactInquiry->is($inquiry)
        ),
    );

    expect($inquiry->fresh()?->notification_sent_at)->not->toBeNull();
});
PHP

cat > tests/Feature/DatabaseSeederTest.php <<'PHP'
<?php

namespace Tests\Feature;

use App\Models\ContactInquiry;
use App\Models\GalleryImage;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeders_create_current_scope_records_with_configured_admin_credentials(): void
    {
        $credentials = [
            'name' => 'Configured Admin',
            'email' => 'configured-admin@example.test',
            'password' => 'a-strong-test-password',
        ];

        config()->set('admin.seed_user', $credentials);

        $this->seed();

        $admin = User::query()
            ->where('email', $credentials['email'])
            ->firstOrFail();

        $this->assertSame($credentials['name'], $admin->name);
        $this->assertTrue(
            Hash::check($credentials['password'], $admin->password),
        );

        $this->assertDatabaseHas(SiteSetting::class, [
            'key' => 'restaurant_name',
            'value' => 'Coast & Cay',
        ]);

        $this->assertDatabaseHas(Page::class, [
            'slug' => 'home',
        ]);

        $this->assertGreaterThanOrEqual(
            4,
            MenuCategory::query()->count('*'),
        );
        $this->assertGreaterThanOrEqual(
            12,
            MenuItem::query()->count('*'),
        );
        $this->assertGreaterThanOrEqual(
            3,
            GalleryImage::query()->count('*'),
        );
        $this->assertGreaterThanOrEqual(
            2,
            ContactInquiry::query()->count('*'),
        );

        $this->assertDatabaseMissing(Page::class, [
            'title' => 'Welcome to Le Jardin',
        ]);
    }

    public function test_admin_user_seeder_rejects_a_missing_password(): void
    {
        config()->set('admin.seed_user', [
            'name' => 'Configured Admin',
            'email' => 'configured-admin@example.test',
            'password' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'ADMIN_USER_PASSWORD must be configured before seeding the admin user.',
        );

        $this->seed(AdminUserSeeder::class);
    }
}
PHP

cat > tests/Feature/Admin/DashboardTest.php <<'PHP'
<?php

use App\Filament\Resources\GalleryImages\GalleryImageResource;
use App\Filament\Resources\MenuItems\MenuItemResource;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\SiteSettings\SiteSettingResource;
use App\Filament\Widgets\ContentQuickActions;
use App\Filament\Widgets\InquiryOverview;
use App\Filament\Widgets\RecentInquiries;
use App\Models\ContactInquiry;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function (): void {
    config()->set('admin.seed_user.email', 'admin@example.test');

    Filament::setCurrentPanel(
        Filament::getPanel('admin'),
    );
});

it('protects dashboard data from users without panel access', function (): void {
    $user = User::factory()->create([
        'email' => 'staff@example.test',
    ]);

    $this->actingAs($user);

    $this->get('/admin')->assertForbidden();

    expect(InquiryOverview::canView())->toBeFalse()
        ->and(RecentInquiries::canView())->toBeFalse()
        ->and(ContentQuickActions::canView())->toBeFalse();
});

it('renders the branded dashboard for the configured admin', function (): void {
    $this->actingAs(dashboardTestAdmin());

    $this->get('/admin')
        ->assertOk()
        ->assertSee('Website overview');
});

it('shows accurate unread contact inquiry counts', function (): void {
    $this->actingAs(dashboardTestAdmin());

    foreach (range(1, 4) as $index) {
        dashboardTestContactInquiry([
            'email' => "guest-{$index}@example.test",
        ]);
    }

    Livewire::test(InquiryOverview::class)
        ->assertSee('Unread Contact Inquiries')
        ->assertSee('4')
        ->assertSee('Awaiting manual review');
});

it('shows recent contact inquiries without exposing unnecessary customer data', function (): void {
    $this->actingAs(dashboardTestAdmin());

    dashboardTestContactInquiry([
        'customer_name' => 'Maria Carter',
        'email' => 'private-customer@example.test',
        'phone' => '+1 (555) 401-3100',
        'subject' => 'Online order question',
        'message' => 'Sensitive order information should not appear on the dashboard.',
    ]);

    Livewire::test(RecentInquiries::class)
        ->assertSee('Recent contact inquiries')
        ->assertSee('Maria Carter')
        ->assertSee('Online order question')
        ->assertSee('New')
        ->assertDontSee('private-customer@example.test')
        ->assertDontSee('+1 (555) 401-3100')
        ->assertDontSee('Sensitive order information');
});

it('limits recent contact inquiries to the newest eight records', function (): void {
    $this->actingAs(dashboardTestAdmin());

    foreach (range(1, 9) as $index) {
        dashboardTestContactInquiry([
            'customer_name' => "Contact Guest {$index}",
            'email' => "contact-{$index}@example.test",
            'created_at' => now()->subMinutes(10 - $index),
            'updated_at' => now()->subMinutes(10 - $index),
        ]);
    }

    Livewire::test(RecentInquiries::class)
        ->assertSee('Contact Guest 9')
        ->assertSee('Contact Guest 2')
        ->assertDontSee('Contact Guest 1');
});

it('renders a contact-only empty inquiry state', function (): void {
    $this->actingAs(dashboardTestAdmin());

    Livewire::test(RecentInquiries::class)
        ->assertSee('No contact inquiries yet')
        ->assertSee('public contact form')
        ->assertDontSee('Reservation Requests')
        ->assertDontSee('Order Inquiries');
});

it('links quick actions to approved protected resources', function (): void {
    $this->actingAs(dashboardTestAdmin());

    Livewire::test(ContentQuickActions::class)
        ->assertSee('Add menu item')
        ->assertSee('Upload gallery image')
        ->assertSee('Manage page content')
        ->assertSee('Update site settings')
        ->assertSeeHtml(
            'href="'.MenuItemResource::getUrl('create').'"',
        )
        ->assertSeeHtml(
            'href="'.GalleryImageResource::getUrl('create').'"',
        )
        ->assertSeeHtml(
            'href="'.PageResource::getUrl('index').'"',
        )
        ->assertSeeHtml(
            'href="'.SiteSettingResource::getUrl('index').'"',
        );
});

it('loads recent contact inquiries within a fixed query budget', function (): void {
    $this->actingAs(dashboardTestAdmin());

    dashboardTestContactInquiry();

    DB::flushQueryLog();
    DB::enableQueryLog();

    Livewire::test(RecentInquiries::class)
        ->assertSee('Recent contact inquiries');

    $selectQueryCount = collect(DB::getQueryLog())
        ->filter(
            fn (array $query): bool => str_starts_with(
                strtolower(ltrim($query['query'])),
                'select',
            ),
        )
        ->count();

    DB::disableQueryLog();

    expect($selectQueryCount)->toBeLessThanOrEqual(4);
});

function dashboardTestAdmin(): User
{
    return User::factory()->create([
        'email' => 'admin@example.test',
    ]);
}

/**
 * @param  array<string, mixed>  $overrides
 */
function dashboardTestContactInquiry(
    array $overrides = [],
): ContactInquiry {
    return ContactInquiry::query()->create(array_merge([
        'customer_name' => 'Contact Guest',
        'email' => 'contact@example.test',
        'phone' => null,
        'subject' => 'General restaurant question',
        'message' => 'Please contact me about the restaurant.',
        'is_read' => false,
    ], $overrides));
}
PHP

cat > tests/Feature/ContactInquirySubmissionTest.php <<'PHP'
<?php

use App\Jobs\SendContactInquiryNotification;
use App\Models\ContactInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

$validPayload = static function (array $overrides = []): array {
    return array_merge([
        'customer_name' => 'Maria Carter',
        'email' => 'maria@example.com',
        'phone' => '+1 (555) 401-3200',
        'subject' => 'Menu question',
        'message' => 'Hello, I would like to ask about your vegetarian menu options.',
        'website' => '',
    ], $overrides);
};

$contactFormUrl = static fn (): string => route('contact.create').'#contact-inquiry';

test('invalid payload fails validation', function () use ($contactFormUrl): void {
    Queue::fake();

    $response = $this
        ->from(route('contact.create'))
        ->post(route('contact-inquiries.store'), [
            'subject' => 'Menu question',
        ]);

    $response
        ->assertRedirect($contactFormUrl())
        ->assertSessionHasErrors([
            'customer_name',
            'email',
            'message',
        ])
        ->assertSessionHasInput('subject', 'Menu question');

    $this->assertDatabaseCount('contact_inquiries', 0);

    Queue::assertNothingPushed();
});

test('valid payload stores database record', function () use ($contactFormUrl, $validPayload): void {
    Queue::fake();

    $response = $this
        ->from(route('contact.create'))
        ->post(route('contact-inquiries.store'), $validPayload());

    $response
        ->assertRedirect($contactFormUrl())
        ->assertSessionHasNoErrors();

    $this->assertDatabaseCount('contact_inquiries', 1);

    $this->assertDatabaseHas('contact_inquiries', [
        'customer_name' => 'Maria Carter',
        'email' => 'maria@example.com',
        'phone' => '+1 (555) 401-3200',
        'subject' => 'Menu question',
        'message' => 'Hello, I would like to ask about your vegetarian menu options.',
    ]);
});

test('valid JSON payload stores once and returns a safe success response', function () use ($validPayload): void {
    Queue::fake();

    $response = $this
        ->withHeader('Accept', 'application/json')
        ->postJson(
            route('contact-inquiries.store'),
            $validPayload(),
        );

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath(
            'message',
            fn (string $message): bool => str_contains(
                $message,
                'Contact Inquiry',
            ),
        );

    $this->assertDatabaseCount('contact_inquiries', 1);
});

test('invalid JSON payload returns field errors without storing', function (): void {
    Queue::fake();

    $response = $this
        ->withHeader('Accept', 'application/json')
        ->postJson(route('contact-inquiries.store'), [
            'email' => 'invalid',
        ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'customer_name',
            'email',
            'message',
        ]);

    $this->assertDatabaseCount('contact_inquiries', 0);
});

test('valid payload queues notification', function () use ($contactFormUrl, $validPayload): void {
    Queue::fake();

    config([
        'mail.inquiries_to' => 'restaurant@example.test',
    ]);

    $response = $this
        ->from(route('contact.create'))
        ->post(route('contact-inquiries.store'), $validPayload());

    $response
        ->assertRedirect($contactFormUrl())
        ->assertSessionHasNoErrors();

    $contactInquiry = ContactInquiry::query()->firstOrFail();

    Queue::assertPushed(
        SendContactInquiryNotification::class,
        fn (SendContactInquiryNotification $job): bool => (
            $job->contactInquiryId === $contactInquiry->id
            && $job->recipient === 'restaurant@example.test'
        ),
    );

    expect($contactInquiry->notification_sent_at)->toBeNull();
});

test('success message appears', function () use ($contactFormUrl, $validPayload): void {
    Queue::fake();

    $response = $this
        ->from(route('contact.create'))
        ->post(route('contact-inquiries.store'), $validPayload());

    $response
        ->assertRedirect($contactFormUrl())
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success');
});
PHP

cat > tests/Feature/InquiryNotificationFailureTest.php <<'PHP'
<?php

use App\Jobs\SendContactInquiryNotification;
use App\Models\ContactInquiry;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

test('contact inquiry notification failure leaves the stored inquiry available for retry', function (): void {
    $inquiry = ContactInquiry::query()->create([
        'customer_name' => 'Noah Carter',
        'email' => 'noah@example.com',
        'phone' => '+1 (555) 401-3300',
        'subject' => 'Online order question',
        'message' => 'Please help me understand the pickup instructions.',
        'is_read' => false,
    ]);

    Mail::shouldReceive('to')
        ->once()
        ->with('restaurant@example.test')
        ->andThrow(new RuntimeException('SMTP unavailable'));

    expect(fn () => (new SendContactInquiryNotification(
        contactInquiryId: $inquiry->id,
        recipient: 'restaurant@example.test',
    ))->handle())->toThrow(RuntimeException::class, 'SMTP unavailable');

    expect($inquiry->fresh())
        ->not->toBeNull()
        ->notification_sent_at
        ->toBeNull();
});
PHP

cat > tests/Feature/Admin/InquiryDetailPageTest.php <<'PHP'
<?php

use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Models\ContactInquiry;
use App\Models\User;
use Filament\Facades\Filament;

beforeEach(function (): void {
    config()->set('admin.seed_user.email', 'admin@example.test');

    Filament::setCurrentPanel(
        Filament::getPanel('admin'),
    );
});

it('allows the configured administrator to review a contact inquiry', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin@example.test',
    ]);

    $inquiry = ContactInquiry::query()->create([
        'customer_name' => 'Avery Carter',
        'email' => 'avery@example.test',
        'phone' => '+1 (555) 401-3400',
        'subject' => 'Menu question',
        'message' => 'Could you confirm which dishes are vegetarian?',
        'is_read' => false,
    ]);

    $this->actingAs($admin)
        ->get(ContactInquiryResource::getUrl('view', [
            'record' => $inquiry,
        ]))
        ->assertOk()
        ->assertSeeText('Avery Carter')
        ->assertSeeText('Menu question')
        ->assertSeeText('Could you confirm which dishes are vegetarian?');

    expect($inquiry->fresh()?->is_read)->toBeTrue();
});

it('blocks users without panel access from contact inquiry details', function (): void {
    $staff = User::factory()->create([
        'email' => 'staff@example.test',
    ]);

    $inquiry = ContactInquiry::query()->create([
        'customer_name' => 'Jordan Carter',
        'email' => 'jordan@example.test',
        'phone' => null,
        'subject' => 'Directions question',
        'message' => 'Could you share the easiest way to reach the restaurant?',
        'is_read' => false,
    ]);

    $this->actingAs($staff)
        ->get(ContactInquiryResource::getUrl('view', [
            'record' => $inquiry,
        ]))
        ->assertForbidden();
});
PHP

cat > tests/Feature/GalleryPageTest.php <<'PHP'
<?php

use App\Models\GalleryImage;
use App\Models\Page;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
});

function storeGalleryPageTestImage(string $filename): string
{
    $path = UploadedFile::fake()
        ->image($filename, 2400, 1600)
        ->storeAs('gallery', $filename, 'public');

    expect($path)->toBeString();

    return $path;
}

test('gallery page presents visible Coast and Cay images without retired copy', function (): void {
    GalleryImage::query()->create([
        'title' => 'Coastal Dining Room',
        'alt_text' => 'Warm island-inspired dining room',
        'image_path' => storeGalleryPageTestImage('coastal-dining-room.jpg'),
        'category' => 'Ambiance',
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    GalleryImage::query()->create([
        'title' => 'Island Jerk Chicken',
        'alt_text' => 'Island jerk chicken with rice and peas',
        'image_path' => storeGalleryPageTestImage('island-jerk-chicken.jpg'),
        'category' => 'Cuisine',
        'sort_order' => 2,
        'is_visible' => true,
    ]);

    $this->get(route('gallery'))
        ->assertOk()
        ->assertSee('id="gallery-collection"', false)
        ->assertSee('data-home-motion', false)
        ->assertSee('data-gallery-motion', false)
        ->assertSee('data-gsap="hero-image"', false)
        ->assertSeeText('A taste of the island')
        ->assertSeeText('Food, hospitality, and coastal moments')
        ->assertSeeText('Coastal Dining Room')
        ->assertSeeText('Island Jerk Chicken')
        ->assertDontSeeText('Le Jardin')
        ->assertDontSeeText('Private Celebrations')
        ->assertDontSeeText('private-event');
});

test('gallery page bounds visible images with simple pagination', function (): void {
    foreach (range(1, 14) as $index) {
        GalleryImage::query()->create([
            'title' => sprintf('Coast and Cay Moment %02d', $index),
            'alt_text' => "Coast and Cay moment {$index}",
            'image_path' => "gallery/coast-and-cay-{$index}.jpg",
            'category' => $index % 2 === 0 ? 'Cuisine' : 'Ambiance',
            'sort_order' => $index,
            'is_visible' => true,
        ]);
    }

    $expectedFirstPageTitles = collect(range(1, 12))
        ->map(
            fn (int $index): string => sprintf(
                'Coast and Cay Moment %02d',
                $index,
            ),
        )
        ->all();

    $this->get(route('gallery'))
        ->assertOk()
        ->assertViewHas(
            'galleryImages',
            function (Paginator $galleryImages) use ($expectedFirstPageTitles): bool {
                return $galleryImages->perPage() === 12
                    && $galleryImages->currentPage() === 1
                    && $galleryImages->getCollection()->pluck('title')->all()
                        === $expectedFirstPageTitles;
            },
        )
        ->assertSeeText('Coast and Cay Moment 01')
        ->assertSeeText('Coast and Cay Moment 12')
        ->assertDontSeeText('Coast and Cay Moment 13')
        ->assertSee('rel="next"', false);
});

test('gallery page renders sanitized rich editor content', function (): void {
    Page::query()->create([
        'slug' => 'gallery',
        'title' => 'Gallery',
        'content' => '<p>Every image reflects <strong>Caribbean hospitality</strong>.</p><p onclick="alert(1)">Managed through the CMS.</p>',
        'is_published' => true,
    ]);

    $this->get(route('gallery'))
        ->assertOk()
        ->assertSee('<strong>Caribbean hospitality</strong>', false)
        ->assertSeeText('Managed through the CMS.')
        ->assertDontSee('onclick=', false);
});

test('gallery page uses a scope-safe empty state', function (): void {
    $this->get(route('gallery'))
        ->assertOk()
        ->assertSeeText('Our gallery is currently being curated.')
        ->assertSeeText('Contact Us')
        ->assertDontSeeText('Le Jardin')
        ->assertDontSeeText('Private Celebrations');
});
PHP

# Preserve the high-value public page tests while replacing the obsolete final
# assertion that incorrectly classified ecommerce as out of scope.
head -n 290 tests/Feature/PublicPagesTest.php > /tmp/PublicPagesTest.php

cat >> /tmp/PublicPagesTest.php <<'PHP'

test('public pages do not expose retired inquiry or event workflows', function (string $routeName): void {
    $response = $this->get(route($routeName))->assertOk();

    foreach (
        [
            'Reservation Request',
            'Order Inquiry',
            'Banquet Hall',
            'Private Celebrations',
            'private-event',
            'Submit Order Inquiry',
        ] as $retiredLabel
    ) {
        $response->assertDontSeeText($retiredLabel);
    }
})->with([
    'home' => 'home',
    'menu' => 'menu',
    'gallery' => 'gallery',
    'contact' => 'contact.create',
]);
PHP

cat /tmp/PublicPagesTest.php > tests/Feature/PublicPagesTest.php
rm -f /tmp/PublicPagesTest.php

rm -f \
    app/Jobs/.editorconfig \
    database/seeders/InquirySeeder.php \
    database/seeders/PremiumPublicContentSeeder.php \
    resources/js/forms/inquiry-form.js \
    resources/js/home-gallery-carousel.js \
    resources/views/components/public/home-gallery-carousel.blade.php \
    tests/Feature/HomePageLuxuryDesignTest.php \
    tests/Feature/MenuPageLuxuryDesignTest.php \
    tests/Feature/PremiumPublicContentTest.php \
    tests/Feature/PublicInquiryAlpineTest.php \
    tests/Feature/Jobs/SendOrderAndContactInquiryNotificationTest.php

./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed

./vendor/bin/sail lint --dirty --format agent

./vendor/bin/sail artisan test --compact \
    tests/Feature/ContactFormAlpineTest.php \
    tests/Feature/ContactInquirySubmissionTest.php \
    tests/Feature/DatabaseSeederTest.php \
    tests/Feature/GalleryPageTest.php \
    tests/Feature/HomePageDesignTest.php \
    tests/Feature/MenuPageDesignTest.php \
    tests/Feature/PublicContentTest.php \
    tests/Feature/PublicPagesTest.php \
    tests/Feature/Admin/DashboardTest.php \
    tests/Feature/Admin/InquiryDetailPageTest.php \
    tests/Feature/Jobs/SendContactInquiryNotificationTest.php

./vendor/bin/sail composer ci:check

echo
echo "Remaining active-code scope traces:"
grep -RInE \
    'Le Jardin|Reservation Request|Order Inquiry|Banquet Hall|Submit Order Inquiry|private-event|private dining|home-gallery-carousel|inquiryForm' \
    AGENTS.md app database/seeders docs resources routes tests \
    || true

echo
echo "Cleanup complete. Review git diff before committing:"
git status --short
git diff --stat
