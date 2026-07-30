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

const publicMotionRoot = document.querySelector(
    "[data-home-motion]",
);

if (publicMotionRoot) {
    import("./public-animations")
        .then(({ initPublicAnimations }) => {
            initPublicAnimations(publicMotionRoot);
        })
        .catch((error) => {
            console.error(
                "Unable to initialize public animations.",
                error,
            );
        });
}

const homepagePagerRoot = document.querySelector(
    "[data-home-section-pager]",
);

if (homepagePagerRoot) {
    import("./homepage-section-navigation")
        .then(({ initHomepageSectionNavigation }) => {
            initHomepageSectionNavigation(
                homepagePagerRoot,
            );
        })
        .catch((error) => {
            console.error(
                "Unable to initialize homepage section navigation.",
                error,
            );
        });
}

const menuPageRoot = document.querySelector(
    "[data-menu-page]",
);

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

    import("./menu-category-scroll")
        .then(({ initMenuCategoryScroll }) => {
            initMenuCategoryScroll(menuPageRoot);
        })
        .catch((error) => {
            console.error(
                "Unable to initialize menu category navigation.",
                error,
            );
        });
}

const aboutPageRoot = document.querySelector(
    "[data-about-page]",
);

if (aboutPageRoot) {
    import("./about-experience")
        .then(({ initAboutExperience }) => {
            initAboutExperience(aboutPageRoot);
        })
        .catch((error) => {
            console.error(
                "Unable to initialize the About page experience.",
                error,
            );
        });
}

const sharedSectionPagers = [
    ...document.querySelectorAll(
        '[data-section-pager][data-section-pager-enhancer="shared"]',
    ),
];

if (sharedSectionPagers.length > 0) {
    import("./section-pager")
        .then(({ initSectionPager }) => {
            sharedSectionPagers.forEach((pager) => {
                initSectionPager(pager);
            });
        })
        .catch((error) => {
            console.error(
                "Unable to initialize shared section navigation.",
                error,
            );
        });
}

const galleryPageRoot = document.querySelector(
    "[data-gallery-page]",
);

if (galleryPageRoot) {
    import("./gallery-experience")
        .then(({ initGalleryExperience }) => {
            initGalleryExperience(galleryPageRoot);
        })
        .catch((error) => {
            console.error(
                "Unable to initialize the Gallery page experience.",
                error,
            );
        });
}
