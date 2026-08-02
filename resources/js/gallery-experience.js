import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import { gallerySectionScrollTriggerConfig } from "./section-scroll-trigger-config.js";

gsap.registerPlugin(ScrollTrigger);

const {
    media: mediaQueries,
    reveal,
} = gallerySectionScrollTriggerConfig;

const swipeThreshold = 52;

/**
 * Return every currently rendered collage card.
 */
function getGalleryCards(root) {
    return [
        ...root.querySelectorAll("[data-gallery-item]"),
    ].filter(
        (card) => card instanceof HTMLElement,
    );
}

/**
 * Return every currently rendered fullscreen opener.
 *
 * This query is intentionally dynamic so cards appended through Load More
 * automatically participate in modal navigation.
 */
function getGalleryOpeners(root) {
    return [
        ...root.querySelectorAll("[data-gallery-open]"),
    ].filter(
        (opener) => opener instanceof HTMLAnchorElement,
    );
}

/**
 * Record the Gallery runtime state for diagnostics and browser tests.
 */
function setGalleryMotionState(root, state) {
    root.dataset.galleryMotionState = state;
}

/**
 * Remove Gallery-owned ScrollTriggers left by the current page lifecycle.
 */
function killGalleryScrollTriggers() {
    ScrollTrigger
        .getAll()
        .filter((trigger) => {
            const id = trigger.vars.id;

            return typeof id === "string"
                && id.startsWith("gallery-");
        })
        .forEach((trigger) => {
            trigger.kill();
        });
}

/**
 * Track thumbnail image loading without hiding content when JavaScript fails.
 */
function initializeThumbnailState(cards) {
    const cleanups = [];

    cards.forEach((card) => {
        const image = card.querySelector(
            ".gallery-collage-card__image",
        );

        if (!(image instanceof HTMLImageElement)) {
            card.dataset.galleryImageState = "loaded";

            return;
        }

        /**
         * Mark the card as ready once its responsive image has loaded.
         */
        const markLoaded = () => {
            card.dataset.galleryImageState = "loaded";
        };

        /**
         * Stop displaying a loading shimmer when an image cannot be loaded.
         */
        const markError = () => {
            card.dataset.galleryImageState = "error";
        };

        if (image.complete) {
            if (image.naturalWidth > 0) {
                markLoaded();
            } else {
                markError();
            }

            return;
        }

        image.addEventListener(
            "load",
            markLoaded,
            {
                once: true,
            },
        );

        image.addEventListener(
            "error",
            markError,
            {
                once: true,
            },
        );

        cleanups.push(() => {
            image.removeEventListener(
                "load",
                markLoaded,
            );

            image.removeEventListener(
                "error",
                markError,
            );
        });
    });

    return () => {
        cleanups.forEach((cleanup) => {
            cleanup();
        });
    };
}

/**
 * Animate the page heading and initial collage cards.
 */
function animateInitialGallery(root) {
    const headingTargets = [
        ...root.querySelectorAll(
            "[data-gallery-heading-reveal]",
        ),
    ];

    if (headingTargets.length > 0) {
        gsap.fromTo(
            headingTargets,
            {
                autoAlpha: 0,
                y: 28,
            },
            {
                autoAlpha: 1,
                duration: 0.9,
                ease: "power3.out",
                stagger: 0.09,
                y: 0,
            },
        );
    }

    const cards = getGalleryCards(root);

    if (cards.length === 0) {
        return;
    }

    gsap.set(
        cards,
        {
            autoAlpha: 0,
            scale: 0.985,
            y: reveal.distance,
        },
    );

    ScrollTrigger.batch(
        cards,
        {
            id: "gallery-collage-cards",
            start: reveal.trigger.start,
            once: true,
            onEnter: (batch) => {
                gsap.to(
                    batch,
                    {
                        autoAlpha: 1,
                        duration: reveal.duration,
                        ease: reveal.ease,
                        scale: 1,
                        stagger: reveal.stagger,
                        y: 0,
                        overwrite: true,
                    },
                );
            },
        },
    );
}

/**
 * Animate cards that were appended through the Load More interaction.
 */
function animateAppendedCards(cards) {
    if (cards.length === 0) {
        return;
    }

    gsap.fromTo(
        cards,
        {
            autoAlpha: 0,
            scale: 0.975,
            y: 34,
        },
        {
            autoAlpha: 1,
            duration: 0.78,
            ease: "power3.out",
            scale: 1,
            stagger: 0.07,
            y: 0,
            clearProps: "opacity,visibility,transform",
        },
    );
}

/**
 * Validate and normalize the JSON returned by the Gallery controller.
 */
function parseGalleryPayload(payload) {
    if (
        payload === null
        || typeof payload !== "object"
        || typeof payload.html !== "string"
    ) {
        throw new TypeError(
            "The Gallery response did not contain valid HTML.",
        );
    }

    if (
        payload.next_page_url !== null
        && typeof payload.next_page_url !== "string"
    ) {
        throw new TypeError(
            "The Gallery response contained an invalid next-page URL.",
        );
    }

    return {
        html: payload.html,
        nextPageUrl: payload.next_page_url,
    };
}

/**
 * Progressively append the next server-rendered Gallery page.
 *
 * The real anchor URL remains available as a non-JavaScript fallback.
 */
function initializeLoadMore(
    root,
    onCardsAppended,
) {
    const grid = root.querySelector(
        "[data-gallery-grid]",
    );

    if (!(grid instanceof HTMLElement)) {
        return () => {};
    }

    let activeController = null;

    /**
     * Load and append the next Gallery fragment.
     */
    const handleLoadMoreClick = async (event) => {
        const target = event.target instanceof Element
            ? event.target
            : null;

        const button = target?.closest(
            "[data-gallery-load-more]",
        );

        if (!(button instanceof HTMLAnchorElement)) {
            return;
        }

        event.preventDefault();

        if (button.dataset.galleryBusy === "true") {
            return;
        }

        const shell = button.closest(
            "[data-gallery-load-more-shell]",
        );

        const label = button.querySelector(
            "[data-gallery-load-more-label]",
        );

        const status = shell?.querySelector(
            "[data-gallery-load-more-status]",
        );

        activeController?.abort();
        activeController = new AbortController();

        button.dataset.galleryBusy = "true";
        button.setAttribute("aria-disabled", "true");

        if (label) {
            label.textContent = "Loading moments";
        }

        if (status) {
            status.textContent =
                "Loading more Gallery images.";
        }

        try {
            const response = await fetch(
                button.href,
                {
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With":
                            "XMLHttpRequest",
                    },
                    signal: activeController.signal,
                },
            );

            if (!response.ok) {
                throw new Error(
                    `Gallery request failed with status ${response.status}.`,
                );
            }

            const payload = parseGalleryPayload(
                await response.json(),
            );

            const template =
                document.createElement("template");

            template.innerHTML =
                payload.html.trim();

            const newCards = [
                ...template.content.querySelectorAll(
                    "[data-gallery-item]",
                ),
            ].filter(
                (card) =>
                    card instanceof HTMLElement,
            );

            grid.append(template.content);

            onCardsAppended(newCards);

            if (payload.nextPageUrl) {
                button.href =
                    payload.nextPageUrl;

                button.dataset.galleryBusy =
                    "false";

                button.removeAttribute(
                    "aria-disabled",
                );

                if (label) {
                    label.textContent =
                        "Load more moments";
                }

                if (status) {
                    status.textContent =
                        `${newCards.length} more moments are now in view.`;
                }
            } else {
                button.remove();

                if (status) {
                    status.textContent =
                        "All Gallery moments are now in view.";
                }
            }

            ScrollTrigger.refresh();
        } catch (error) {
            if (error.name === "AbortError") {
                return;
            }

            console.error(
                "Unable to load additional Gallery images.",
                error,
            );

            button.dataset.galleryBusy =
                "false";

            button.removeAttribute(
                "aria-disabled",
            );

            if (label) {
                label.textContent = "Try again";
            }

            if (status) {
                status.textContent =
                    "The additional images could not be loaded. Please try again.";
            }
        }
    };

    root.addEventListener(
        "click",
        handleLoadMoreClick,
    );

    return () => {
        activeController?.abort();

        root.removeEventListener(
            "click",
            handleLoadMoreClick,
        );
    };
}

/**
 * Populate the fullscreen viewer from one Gallery card.
 */
function setDialogContent(
    dialog,
    opener,
    position,
    total,
) {
    const image = dialog.querySelector(
        "[data-gallery-dialog-image]",
    );

    const title = dialog.querySelector(
        "[data-gallery-dialog-title]",
    );

    const category = dialog.querySelector(
        "[data-gallery-dialog-category]",
    );

    const counter = dialog.querySelector(
        "[data-gallery-dialog-counter]",
    );

    const status = dialog.querySelector(
        "[data-gallery-dialog-status]",
    );

    if (!(image instanceof HTMLImageElement)) {
        return;
    }

    const displayTitle =
        opener.dataset.galleryTitle
        ?? "Coast & Cay moment";

    dialog.dataset.galleryImageState =
        "loading";

    image.removeAttribute("src");
    image.removeAttribute("srcset");

    image.alt =
        opener.dataset.galleryAlt
        ?? displayTitle;

    image.sizes =
        "min(92vw, 1800px)";

    if (opener.dataset.gallerySrcset) {
        image.srcset =
            opener.dataset.gallerySrcset;
    }

    image.src =
        opener.dataset.gallerySrc
        ?? opener.href;

    if (title) {
        title.textContent = displayTitle;
    }

    if (category) {
        category.textContent =
            opener.dataset.galleryCategory
            ?? "Coast & Cay";
    }

    if (counter) {
        counter.textContent =
            `${String(position).padStart(2, "0")} / `
            + String(total).padStart(2, "0");
    }

    if (status) {
        status.textContent =
            `Loading image ${position} of ${total}: ${displayTitle}.`;
    }
}

/**
 * Resolve after the current fullscreen image loads or fails.
 */
async function waitForDialogImage(image) {
    if (!image.complete) {
        await new Promise((resolve) => {
            /**
             * Resolve the pending image wait and remove temporary listeners.
             */
            const finish = () => {
                image.removeEventListener(
                    "load",
                    finish,
                );

                image.removeEventListener(
                    "error",
                    finish,
                );

                resolve();
            };

            image.addEventListener(
                "load",
                finish,
            );

            image.addEventListener(
                "error",
                finish,
            );
        });
    }

    if (image.naturalWidth === 0) {
        return false;
    }

    if (typeof image.decode === "function") {
        try {
            await image.decode();
        } catch {
            /*
             * A browser may reject decode after the image has already become
             * displayable. naturalWidth remains the final success signal.
             */
        }
    }

    return image.naturalWidth > 0;
}

/**
 * Convert one GSAP tween into an awaitable operation.
 */
function tweenToPromise(target, variables) {
    return new Promise((resolve) => {
        gsap.to(
            target,
            {
                ...variables,
                onComplete: resolve,
                onInterrupt: resolve,
            },
        );
    });
}

/**
 * Initialize the fullscreen dialog and all of its navigation behavior.
 */
function initializeGalleryDialog(root) {
    if (
        typeof HTMLDialogElement
        === "undefined"
    ) {
        return () => {};
    }

    const dialog = root.querySelector(
        "[data-gallery-dialog]",
    );

    if (!(dialog instanceof HTMLDialogElement)) {
        return () => {};
    }

    const panel = dialog.querySelector(
        "[data-gallery-dialog-panel]",
    );

    const stage = dialog.querySelector(
        "[data-gallery-dialog-stage]",
    );

    const image = dialog.querySelector(
        "[data-gallery-dialog-image]",
    );

    const previousButton = dialog.querySelector(
        "[data-gallery-previous]",
    );

    const nextButton = dialog.querySelector(
        "[data-gallery-next]",
    );

    if (
        !(panel instanceof HTMLElement)
        || !(stage instanceof HTMLElement)
        || !(image instanceof HTMLImageElement)
    ) {
        return () => {};
    }

    let activeIndex = 0;
    let activeOpener = null;
    let contentVersion = 0;
    let isClosing = false;
    let pointerStart = null;

    /**
     * Determine whether animated transitions are currently permitted.
     */
    const motionIsAllowed = () => window
        .matchMedia(
            mediaQueries.motionAllowed,
        )
        .matches;

    /**
     * Enable or disable directional controls based on available cards.
     */
    const updateNavigationState = () => {
        const navigationDisabled =
            getGalleryOpeners(root).length < 2;

        if (
            previousButton
            instanceof HTMLButtonElement
        ) {
            previousButton.disabled =
                navigationDisabled;
        }

        if (
            nextButton
            instanceof HTMLButtonElement
        ) {
            nextButton.disabled =
                navigationDisabled;
        }
    };

    /**
     * Reveal the image after the browser finishes loading it.
     */
    const revealDialogImage = async (
        version,
        direction,
    ) => {
        const loaded =
            await waitForDialogImage(image);

        if (
            version !== contentVersion
            || !dialog.open
        ) {
            return;
        }

        const status = dialog.querySelector(
            "[data-gallery-dialog-status]",
        );

        if (!loaded) {
            dialog.dataset.galleryImageState =
                "error";

            if (status) {
                status.textContent =
                    "The selected Gallery image could not be loaded.";
            }

            gsap.set(
                image,
                {
                    autoAlpha: 1,
                    clearProps: "transform",
                },
            );

            return;
        }

        dialog.dataset.galleryImageState =
            "loaded";

        const title = dialog.querySelector(
            "[data-gallery-dialog-title]",
        )?.textContent ?? "Gallery image";

        if (status) {
            status.textContent =
                `Showing ${title}.`;
        }

        if (!motionIsAllowed()) {
            gsap.set(
                image,
                {
                    autoAlpha: 1,
                    clearProps: "transform",
                },
            );

            return;
        }

        const entrance = direction === 0
            ? {
                x: 0,
                y: 18,
            }
            : {
                x: direction * 42,
                y: 0,
            };

        gsap.fromTo(
            image,
            {
                autoAlpha: 0,
                scale: 1.018,
                ...entrance,
            },
            {
                autoAlpha: 1,
                clearProps:
                    "opacity,visibility,transform",
                duration: 0.58,
                ease: "power3.out",
                scale: 1,
                x: 0,
                y: 0,
            },
        );
    };

    /**
     * Play the viewer panel's opening animation.
     */
    const playOpenAnimation = () => {
        gsap.killTweensOf([
            dialog,
            panel,
        ]);

        if (!motionIsAllowed()) {
            gsap.set(
                [
                    dialog,
                    panel,
                ],
                {
                    autoAlpha: 1,
                    clearProps: "transform",
                },
            );

            return;
        }

        gsap.timeline({
            defaults: {
                ease: "power4.out",
            },
        })
            .fromTo(
                dialog,
                {
                    autoAlpha: 0,
                },
                {
                    autoAlpha: 1,
                    duration: 0.24,
                },
                0,
            )
            .fromTo(
                panel,
                {
                    autoAlpha: 0,
                    scale: 0.982,
                    y: 24,
                },
                {
                    autoAlpha: 1,
                    duration: 0.52,
                    scale: 1,
                    y: 0,
                },
                0.04,
            );
    };

    /**
     * Display a different image while the dialog remains open.
     */
    const displayImage = async (
        requestedIndex,
        direction,
    ) => {
        const openers =
            getGalleryOpeners(root);

        if (openers.length === 0) {
            return;
        }

        const normalizedIndex =
            (
                requestedIndex
                + openers.length
            )
            % openers.length;

        const version = ++contentVersion;

        gsap.killTweensOf(image);

        if (
            motionIsAllowed()
            && dialog.open
        ) {
            await tweenToPromise(
                image,
                {
                    autoAlpha: 0,
                    duration: 0.18,
                    ease: "power2.in",
                    overwrite: true,
                    scale: 0.992,
                    x: direction > 0
                        ? -28
                        : 28,
                },
            );
        }

        if (
            version !== contentVersion
            || !dialog.open
        ) {
            return;
        }

        activeIndex = normalizedIndex;

        setDialogContent(
            dialog,
            openers[activeIndex],
            activeIndex + 1,
            openers.length,
        );

        updateNavigationState();

        await revealDialogImage(
            version,
            direction,
        );
    };

    /**
     * Open the dialog from the selected Gallery card.
     */
    const openDialog = (opener) => {
        const openers =
            getGalleryOpeners(root);

        const openerIndex =
            openers.indexOf(opener);

        if (openerIndex < 0) {
            return;
        }

        activeIndex = openerIndex;
        activeOpener = opener;
        isClosing = false;

        const version = ++contentVersion;

        setDialogContent(
            dialog,
            opener,
            activeIndex + 1,
            openers.length,
        );

        updateNavigationState();

        dialog.classList.remove(
            "is-closing",
        );

        document.documentElement.classList.add(
            "gallery-dialog-open",
        );

        if (!dialog.open) {
            dialog.showModal();
        }

        playOpenAnimation();

        void revealDialogImage(
            version,
            0,
        );
    };

    /**
     * Navigate relative to the currently displayed image.
     */
    const navigate = (step) => {
        if (
            !dialog.open
            || getGalleryOpeners(root).length < 2
        ) {
            return;
        }

        void displayImage(
            activeIndex + step,
            step,
        );
    };

    /**
     * Complete dialog closure and allow the native close event to run.
     */
    const finalizeClose = () => {
        if (dialog.open) {
            dialog.close();
        }
    };

    /**
     * Close the dialog immediately or through an elegant exit timeline.
     */
    const closeDialog = () => {
        if (
            !dialog.open
            || isClosing
        ) {
            return;
        }

        isClosing = true;
        contentVersion += 1;

        dialog.classList.add(
            "is-closing",
        );

        gsap.killTweensOf([
            dialog,
            panel,
            image,
        ]);

        if (!motionIsAllowed()) {
            finalizeClose();

            return;
        }

        gsap.timeline({
            onComplete: finalizeClose,
        })
            .to(
                panel,
                {
                    autoAlpha: 0,
                    duration: 0.2,
                    ease: "power2.in",
                    scale: 0.985,
                    y: 16,
                },
                0,
            )
            .to(
                dialog,
                {
                    autoAlpha: 0,
                    duration: 0.22,
                    ease: "power2.in",
                },
                0.05,
            );
    };

    /**
     * Open Gallery cards through delegated click handling.
     */
    const handleRootClick = (event) => {
        const target = event.target instanceof Element
            ? event.target
            : null;

        const opener = target?.closest(
            "[data-gallery-open]",
        );

        if (!(opener instanceof HTMLAnchorElement)) {
            return;
        }

        event.preventDefault();

        openDialog(opener);
    };

    /**
     * Handle backdrop, close, previous, and next clicks.
     */
    const handleDialogClick = (event) => {
        if (event.target === dialog) {
            closeDialog();

            return;
        }

        const target = event.target instanceof Element
            ? event.target
            : null;

        if (
            target?.closest(
                "[data-gallery-close]",
            )
        ) {
            closeDialog();

            return;
        }

        if (
            target?.closest(
                "[data-gallery-previous]",
            )
        ) {
            navigate(-1);

            return;
        }

        if (
            target?.closest(
                "[data-gallery-next]",
            )
        ) {
            navigate(1);
        }
    };

    /**
     * Animate the browser's native Escape cancellation instead of closing
     * the dialog immediately.
     */
    const handleDialogCancel = (event) => {
        event.preventDefault();

        closeDialog();
    };

    /**
     * Support directional and boundary keyboard navigation.
     */
    const handleDialogKeydown = (event) => {
        if (event.key === "ArrowLeft") {
            event.preventDefault();
            navigate(-1);

            return;
        }

        if (event.key === "ArrowRight") {
            event.preventDefault();
            navigate(1);

            return;
        }

        if (event.key === "Home") {
            event.preventDefault();

            void displayImage(
                0,
                -1,
            );

            return;
        }

        if (event.key === "End") {
            event.preventDefault();

            const lastIndex =
                getGalleryOpeners(root).length - 1;

            void displayImage(
                lastIndex,
                1,
            );
        }
    };

    /**
     * Record the beginning of a primary touch or pointer gesture.
     */
    const handlePointerDown = (event) => {
        if (!event.isPrimary) {
            return;
        }

        pointerStart = {
            x: event.clientX,
            y: event.clientY,
        };
    };

    /**
     * Convert a sufficiently horizontal gesture into previous/next navigation.
     */
    const handlePointerUp = (event) => {
        if (
            !event.isPrimary
            || pointerStart === null
        ) {
            pointerStart = null;

            return;
        }

        const horizontalDistance =
            event.clientX - pointerStart.x;

        const verticalDistance =
            event.clientY - pointerStart.y;

        pointerStart = null;

        if (
            Math.abs(horizontalDistance)
                < swipeThreshold
            || Math.abs(horizontalDistance)
                <= Math.abs(verticalDistance)
        ) {
            return;
        }

        navigate(
            horizontalDistance < 0
                ? 1
                : -1,
        );
    };

    /**
     * Clear an interrupted pointer gesture.
     */
    const handlePointerCancel = () => {
        pointerStart = null;
    };

    /**
     * Reset viewer state and return focus to the originating card.
     */
    const handleDialogClose = () => {
        isClosing = false;

        dialog.classList.remove(
            "is-closing",
        );

        dialog.dataset.galleryImageState =
            "idle";

        document.documentElement.classList.remove(
            "gallery-dialog-open",
        );

        gsap.set(
            [
                dialog,
                panel,
                image,
            ],
            {
                clearProps: "all",
            },
        );

        image.removeAttribute("src");
        image.removeAttribute("srcset");
        image.alt = "";

        activeOpener?.focus({
            preventScroll: true,
        });

        activeOpener = null;
    };

    root.addEventListener(
        "click",
        handleRootClick,
    );

    dialog.addEventListener(
        "click",
        handleDialogClick,
    );

    dialog.addEventListener(
        "cancel",
        handleDialogCancel,
    );

    dialog.addEventListener(
        "keydown",
        handleDialogKeydown,
    );

    dialog.addEventListener(
        "close",
        handleDialogClose,
    );

    stage.addEventListener(
        "pointerdown",
        handlePointerDown,
    );

    stage.addEventListener(
        "pointerup",
        handlePointerUp,
    );

    stage.addEventListener(
        "pointercancel",
        handlePointerCancel,
    );

    return () => {
        root.removeEventListener(
            "click",
            handleRootClick,
        );

        dialog.removeEventListener(
            "click",
            handleDialogClick,
        );

        dialog.removeEventListener(
            "cancel",
            handleDialogCancel,
        );

        dialog.removeEventListener(
            "keydown",
            handleDialogKeydown,
        );

        dialog.removeEventListener(
            "close",
            handleDialogClose,
        );

        stage.removeEventListener(
            "pointerdown",
            handlePointerDown,
        );

        stage.removeEventListener(
            "pointerup",
            handlePointerUp,
        );

        stage.removeEventListener(
            "pointercancel",
            handlePointerCancel,
        );

        gsap.killTweensOf([
            dialog,
            panel,
            image,
        ]);

        document.documentElement.classList.remove(
            "gallery-dialog-open",
        );

        if (dialog.open) {
            dialog.close();
        }
    };
}

/**
 * Initialize the complete one-section Gallery experience.
 */
export function initGalleryExperience(root) {
    if (!(root instanceof HTMLElement)) {
        return () => {};
    }

    killGalleryScrollTriggers();

    root.dataset.galleryEnhanced = "true";

    setGalleryMotionState(
        root,
        "booting",
    );

    const thumbnailCleanups = [
        initializeThumbnailState(
            getGalleryCards(root),
        ),
    ];

    const matchMedia =
        gsap.matchMedia();

    matchMedia.add(
        mediaQueries.motionAllowed,
        () => {
            const context = gsap.context(
                () => {
                    animateInitialGallery(root);
                },
                root,
            );

            return () => {
                context.revert();
            };
        },
    );

    matchMedia.add(
        mediaQueries.reducedMotion,
        () => {
            gsap.set(
                [
                    ...root.querySelectorAll(
                        "[data-gallery-heading-reveal]",
                    ),
                    ...getGalleryCards(root),
                ],
                {
                    autoAlpha: 1,
                    clearProps: "transform",
                },
            );
        },
    );

    const cleanupDialog =
        initializeGalleryDialog(root);

    const cleanupLoadMore =
        initializeLoadMore(
            root,
            (cards) => {
                thumbnailCleanups.push(
                    initializeThumbnailState(cards),
                );

                if (
                    window
                        .matchMedia(
                            mediaQueries.motionAllowed,
                        )
                        .matches
                ) {
                    animateAppendedCards(cards);
                } else {
                    gsap.set(
                        cards,
                        {
                            autoAlpha: 1,
                            clearProps: "transform",
                        },
                    );
                }
            },
        );

    setGalleryMotionState(
        root,
        "ready",
    );

    const refreshFrame =
        window.requestAnimationFrame(
            () => {
                ScrollTrigger.refresh();
            },
        );

    return () => {
        window.cancelAnimationFrame(
            refreshFrame,
        );

        cleanupLoadMore();
        cleanupDialog();

        thumbnailCleanups.forEach(
            (cleanup) => {
                cleanup();
            },
        );

        matchMedia.revert();

        killGalleryScrollTriggers();

        gsap.killTweensOf(
            root.querySelectorAll(
                "[data-gallery-heading-reveal], "
                + "[data-gallery-item]",
            ),
        );

        root.removeAttribute(
            "data-gallery-enhanced",
        );

        setGalleryMotionState(
            root,
            "destroyed",
        );
    };
}
