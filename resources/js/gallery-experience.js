import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

const reducedMotionQuery = "(prefers-reduced-motion: reduce)";
const desktopQuery = "(min-width: 1024px) and (pointer: fine)";
const dialogCloseDuration = 220;

/**
 * Populate the shared lightbox from one server-rendered gallery link.
 */
function setDialogContent(dialog, opener) {
    const image = dialog.querySelector("[data-gallery-dialog-image]");
    const title = dialog.querySelector("[data-gallery-dialog-title]");
    const category = dialog.querySelector("[data-gallery-dialog-category]");

    if (!(image instanceof HTMLImageElement)) {
        return;
    }

    dialog.dataset.galleryImageState = "loading";
    image.src = opener.dataset.gallerySrc ?? opener.href;
    image.alt = opener.dataset.galleryAlt ?? "";
    image.sizes = "min(88vw, 1440px)";

    if (opener.dataset.gallerySrcset) {
        image.srcset = opener.dataset.gallerySrcset;
    } else {
        image.removeAttribute("srcset");
    }

    if (title) {
        title.textContent = opener.dataset.galleryTitle ?? "Coast & Cay moment";
    }

    if (category) {
        category.textContent = opener.dataset.galleryCategory ?? "Coast & Cay";
    }

    if (image.complete) {
        dialog.dataset.galleryImageState = "loaded";
    }
}

/**
 * Initialize the native dialog viewer, keyboard navigation, and focus return.
 */
function initializeGalleryDialog(root) {
    const dialog = root.querySelector("[data-gallery-dialog]");
    const openers = [...root.querySelectorAll("[data-gallery-open]")].filter(
        (opener) => opener instanceof HTMLAnchorElement,
    );

    if (!(dialog instanceof HTMLDialogElement) || openers.length === 0) {
        return () => {};
    }

    const image = dialog.querySelector("[data-gallery-dialog-image]");
    const previousButton = dialog.querySelector("[data-gallery-previous]");
    const nextButton = dialog.querySelector("[data-gallery-next]");

    let activeIndex = 0;
    let activeOpener = null;
    let closeTimer = null;

    /**
     * Render one image and wrap navigation within the current result page.
     */
    const showImage = (requestedIndex) => {
        activeIndex = (requestedIndex + openers.length) % openers.length;
        setDialogContent(dialog, openers[activeIndex]);
    };

    /**
     * Open the viewer from one gallery link and remember its focus origin.
     */
    const openDialog = (opener) => {
        const openerIndex = openers.indexOf(opener);

        if (openerIndex < 0) {
            return;
        }

        activeOpener = opener;
        showImage(openerIndex);
        dialog.classList.remove("is-closing");
        document.documentElement.classList.add("gallery-dialog-open");

        const navigationDisabled = openers.length < 2;

        if (previousButton instanceof HTMLButtonElement) {
            previousButton.disabled = navigationDisabled;
        }

        if (nextButton instanceof HTMLButtonElement) {
            nextButton.disabled = navigationDisabled;
        }

        if (!dialog.open) {
            dialog.showModal();
        }
    };

    /**
     * Complete closure after the optional CSS exit transition.
     */
    const finalizeClose = () => {
        if (closeTimer !== null) {
            window.clearTimeout(closeTimer);
            closeTimer = null;
        }

        if (dialog.open) {
            dialog.close();
        }
    };

    /**
     * Close the viewer without delaying reduced-motion users.
     */
    const closeDialog = () => {
        if (!dialog.open || dialog.classList.contains("is-closing")) {
            return;
        }

        if (window.matchMedia(reducedMotionQuery).matches) {
            finalizeClose();

            return;
        }

        dialog.classList.add("is-closing");
        closeTimer = window.setTimeout(finalizeClose, dialogCloseDuration);
    };

    /**
     * Intercept gallery links while preserving their no-JavaScript fallback URL.
     */
    const handleRootClick = (event) => {
        const target = event.target instanceof Element ? event.target : null;
        const opener = target?.closest("[data-gallery-open]");

        if (!(opener instanceof HTMLAnchorElement)) {
            return;
        }

        event.preventDefault();
        openDialog(opener);
    };

    /**
     * Handle close, previous, next, and backdrop interactions.
     */
    const handleDialogClick = (event) => {
        if (event.target === dialog) {
            closeDialog();

            return;
        }

        const target = event.target instanceof Element ? event.target : null;

        if (target?.closest("[data-gallery-close]")) {
            closeDialog();
        } else if (target?.closest("[data-gallery-previous]")) {
            showImage(activeIndex - 1);
        } else if (target?.closest("[data-gallery-next]")) {
            showImage(activeIndex + 1);
        }
    };

    /**
     * Preserve Escape-to-close while allowing the exit transition to finish.
     */
    const handleDialogCancel = (event) => {
        event.preventDefault();
        closeDialog();
    };

    /**
     * Support familiar arrow-key image navigation.
     */
    const handleDialogKeydown = (event) => {
        if (event.key === "ArrowLeft") {
            event.preventDefault();
            showImage(activeIndex - 1);
        } else if (event.key === "ArrowRight") {
            event.preventDefault();
            showImage(activeIndex + 1);
        }
    };

    /**
     * Reveal the current image only after it has loaded.
     */
    const handleImageLoad = () => {
        dialog.dataset.galleryImageState = "loaded";
    };

    /**
     * Reset media state and restore focus after native dialog closure.
     */
    const handleDialogClose = () => {
        dialog.classList.remove("is-closing");
        dialog.dataset.galleryImageState = "idle";
        document.documentElement.classList.remove("gallery-dialog-open");

        if (image instanceof HTMLImageElement) {
            image.removeAttribute("src");
            image.removeAttribute("srcset");
            image.alt = "";
        }

        activeOpener?.focus({ preventScroll: true });
        activeOpener = null;
    };

    root.addEventListener("click", handleRootClick);
    dialog.addEventListener("click", handleDialogClick);
    dialog.addEventListener("cancel", handleDialogCancel);
    dialog.addEventListener("keydown", handleDialogKeydown);
    dialog.addEventListener("close", handleDialogClose);
    image?.addEventListener("load", handleImageLoad);

    return () => {
        root.removeEventListener("click", handleRootClick);
        dialog.removeEventListener("click", handleDialogClick);
        dialog.removeEventListener("cancel", handleDialogCancel);
        dialog.removeEventListener("keydown", handleDialogKeydown);
        dialog.removeEventListener("close", handleDialogClose);
        image?.removeEventListener("load", handleImageLoad);

        if (closeTimer !== null) {
            window.clearTimeout(closeTimer);
        }

        document.documentElement.classList.remove("gallery-dialog-open");

        if (dialog.open) {
            dialog.close();
        }
    };
}

/**
 * Restore the final readable state for reduced-motion users.
 */
function setReducedMotionState(root) {
    root.dataset.galleryMotionState = "reduced";

    gsap.set(
        root.querySelectorAll(
            "[data-gallery-reveal], [data-gallery-stack-card], [data-gallery-item]",
        ),
        {
            autoAlpha: 1,
            clearProps: "transform,opacity,visibility",
        },
    );
}

/**
 * Initialize restrained hero, section, parallax, and contact-sheet motion.
 */
function initializeGalleryMotion(root) {
    const media = gsap.matchMedia();

    media.add(
        {
            desktop: desktopQuery,
            reducedMotion: reducedMotionQuery,
        },
        (context) => {
            const { desktop = false, reducedMotion = false } =
                context.conditions;

            if (reducedMotion) {
                setReducedMotionState(root);

                return;
            }

            root.dataset.galleryMotionState = "ready";

            const hero = root.querySelector("[data-gallery-hero]");
            const heroImage = root.querySelector("[data-gallery-hero-image] img");
            const heroCopy = root.querySelectorAll(
                "[data-gallery-hero-copy] [data-gallery-reveal]",
            );
            const heroCards = root.querySelectorAll(
                "[data-gallery-stack-card]",
            );

            const heroTimeline = gsap.timeline({
                defaults: { ease: "power4.out" },
            });

            if (heroImage instanceof HTMLImageElement) {
                heroTimeline.fromTo(
                    heroImage,
                    { scale: 1.06 },
                    { duration: 1.6, scale: 1 },
                    0,
                );
            }

            heroTimeline
                .fromTo(
                    heroCopy,
                    { autoAlpha: 0, y: 32 },
                    {
                        autoAlpha: 1,
                        duration: 1,
                        stagger: 0.1,
                        y: 0,
                    },
                    0.08,
                )
                .fromTo(
                    heroCards,
                    { autoAlpha: 0, y: 72 },
                    {
                        autoAlpha: 1,
                        duration: 1.05,
                        stagger: 0.12,
                        y: 0,
                    },
                    0.2,
                );

            if (
                hero instanceof HTMLElement
                && heroImage instanceof HTMLImageElement
            ) {
                gsap.fromTo(
                    heroImage,
                    { yPercent: 0 },
                    {
                        ease: "none",
                        scrollTrigger: {
                            id: "gallery-hero-parallax",
                            trigger: hero,
                            start: "top top",
                            end: "bottom top",
                            scrub: true,
                            invalidateOnRefresh: true,
                        },
                        yPercent: desktop ? 7 : 3,
                    },
                );
            }

            const revealTargets = [
                ...root.querySelectorAll("[data-gallery-reveal]"),
            ].filter((target) => !target.closest("[data-gallery-hero]"));

            revealTargets.forEach((target) => {
                gsap.fromTo(
                    target,
                    { autoAlpha: 0, y: 34 },
                    {
                        autoAlpha: 1,
                        duration: 0.82,
                        ease: "power3.out",
                        scrollTrigger: {
                            trigger: target,
                            start: "top 86%",
                            once: true,
                        },
                        y: 0,
                    },
                );
            });

            const galleryItems = root.querySelectorAll("[data-gallery-item]");

            if (galleryItems.length > 0) {
                gsap.set(galleryItems, { autoAlpha: 0, y: 46 });

                ScrollTrigger.batch(galleryItems, {
                    interval: 0.08,
                    once: true,
                    start: "top 90%",
                    onEnter: (batch) => {
                        gsap.to(batch, {
                            autoAlpha: 1,
                            duration: 0.8,
                            ease: "power3.out",
                            stagger: 0.08,
                            y: 0,
                        });
                    },
                });
            }
        },
    );

    /**
     * Recalculate trigger positions after responsive media has settled.
     */
    const refreshTriggers = () => {
        ScrollTrigger.refresh();
    };

    if (document.readyState === "complete") {
        refreshTriggers();
    } else {
        window.addEventListener("load", refreshTriggers, { once: true });
    }

    return () => {
        window.removeEventListener("load", refreshTriggers);
        media.revert();
    };
}

/**
 * Initialize the complete gallery experience and return one cleanup callback.
 */
export function initGalleryExperience(
    root = document.querySelector("[data-gallery-page]"),
) {
    if (!root) {
        return () => {};
    }

    const cleanupDialog = initializeGalleryDialog(root);
    const cleanupMotion = initializeGalleryMotion(root);

    const cleanup = () => {
        cleanupDialog();
        cleanupMotion();
    };

    if (import.meta.hot) {
        import.meta.hot.dispose(cleanup);
    }

    return cleanup;
}
