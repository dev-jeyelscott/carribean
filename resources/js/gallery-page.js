import { initGalleryExperience } from "./gallery-experience.js";

let cleanupGalleryExperience = () => {};

/**
 * Initialize the Gallery-only browser experience.
 *
 * This file is a dedicated Vite entry loaded only on the Gallery route.
 * Keeping the bootstrap synchronous removes the shared app.js dynamic-import
 * dependency and makes ScrollTrigger initialization deterministic.
 */
function bootGalleryPage() {
    cleanupGalleryExperience();

    const galleryRoot = document.querySelector(
        "[data-gallery-page]",
    );

    if (!(galleryRoot instanceof HTMLElement)) {
        cleanupGalleryExperience = () => {};

        return;
    }

    galleryRoot.dataset.galleryMotionState =
        "booting";

    try {
        cleanupGalleryExperience =
            initGalleryExperience(galleryRoot);
    } catch (error) {
        galleryRoot.dataset.galleryMotionState =
            "error";

        console.error(
            "Unable to initialize the Gallery ScrollTrigger experience.",
            error,
        );

        cleanupGalleryExperience = () => {};
    }
}

/*
 * Run after the module executes. ES modules are deferred automatically, so the
 * server-rendered Gallery root is already available at this point.
 */
bootGalleryPage();

/*
 * Retain compatibility if Livewire navigation is introduced on public links.
 */
document.addEventListener(
    "livewire:navigated",
    bootGalleryPage,
);

if (import.meta.hot) {
    import.meta.hot.dispose(() => {
        cleanupGalleryExperience();

        document.removeEventListener(
            "livewire:navigated",
            bootGalleryPage,
        );
    });
}
