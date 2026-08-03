import { initGalleryInteractions } from "./gallery-interactions.js";

let cleanupGalleryInteractions = () => {};

/**
 * Initialize Gallery loading and fullscreen interactions without GSAP.
 */
function bootGalleryPage() {
    cleanupGalleryInteractions();

    const galleryRoot = document.querySelector("[data-gallery-page]");

    if (!(galleryRoot instanceof HTMLElement)) {
        cleanupGalleryInteractions = () => {};

        return;
    }

    try {
        cleanupGalleryInteractions = initGalleryInteractions(galleryRoot);
    } catch (error) {
        galleryRoot.dataset.galleryMotionState = "error";

        console.error(
            "Unable to initialize the Gallery interactions.",
            error,
        );

        cleanupGalleryInteractions = () => {};
    }
}

bootGalleryPage();

document.addEventListener("livewire:navigated", bootGalleryPage);

if (import.meta.hot) {
    import.meta.hot.dispose(() => {
        cleanupGalleryInteractions();
        document.removeEventListener("livewire:navigated", bootGalleryPage);
    });
}
