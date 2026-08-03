import {
    Alpine,
    Livewire,
} from "../../vendor/livewire/livewire/dist/livewire.esm";
import contactForm from "./forms/contact-form";

window.Alpine = Alpine;

Alpine.data("contactForm", contactForm);

Livewire.start();

const publicHeader = document.querySelector(
    "[data-public-header-shell] > header",
);

if (publicHeader) {
    import("./public-header")
        .then(({ initPublicHeader }) => {
            initPublicHeader(publicHeader);
        })
        .catch((error) => {
            console.error(
                "Unable to initialize the shared public header.",
                error,
            );
        });
}

/*
 * Ordinary public-page reveals use IntersectionObserver and CSS rather than
 * GSAP ScrollTrigger.
 */
if (document.querySelector("[data-reveal]")) {
    import("./public-reveals")
        .then(({ initPublicReveals }) => {
            initPublicReveals();
        })
        .catch((error) => {
            console.error(
                "Unable to initialize public reveals.",
                error,
            );
        });
}

/*
 * The homepage is the only public route allowed to load ScrollTrigger.
 */
const homepageRoot = document.querySelector(
    "[data-home-motion][data-home-section-pager]",
);

if (homepageRoot) {
    import("./public-animations")
        .then(({ initPublicAnimations }) => {
            initPublicAnimations(homepageRoot);
        })
        .catch((error) => {
            console.error(
                "Unable to initialize homepage animations.",
                error,
            );
        });

    import("./homepage-section-navigation")
        .then(({ initHomepageSectionNavigation }) => {
            initHomepageSectionNavigation(homepageRoot);
        })
        .catch((error) => {
            console.error(
                "Unable to initialize homepage section navigation.",
                error,
            );
        });
}

/*
 * Menu interactions use native scrolling, IntersectionObserver, and GSAP Core
 * for the product dialog. ScrollTrigger is intentionally excluded.
 */
const menuPageRoot = document.querySelector("[data-menu-page]");

if (menuPageRoot) {
    import("./menu-experience")
        .then(({ initMenuExperience }) => {
            initMenuExperience(menuPageRoot);
        })
        .catch((error) => {
            console.error(
                "Unable to initialize the menu experience.",
                error,
            );
        });
}
