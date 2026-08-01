import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import { gallerySectionScrollTriggerConfig } from "./section-scroll-trigger-config.js";

gsap.registerPlugin(ScrollTrigger);

const {
    depth,
    media: mediaQueries,
    reveal,
    tracking,
} = gallerySectionScrollTriggerConfig;

const dialogCloseDuration = 220;

/**
 * Return every valid Gallery panel in document order.
 */
function getGalleryPanels(root) {
    return [
        ...root.querySelectorAll("[data-gallery-panel]"),
    ].filter(
        (panel) =>
            panel instanceof HTMLElement
            && panel.id !== "",
    );
}

/**
 * Set the Gallery runtime state for browser diagnostics.
 */
function setGalleryMotionState(root, state) {
    root.dataset.galleryMotionState = state;
}

/**
 * Remove Gallery-owned ScrollTriggers left by an earlier initialization.
 */
function killExistingGalleryTriggers() {
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
 * Expose the number of active Gallery ScrollTriggers for diagnostics and tests.
 */
function updateGalleryTriggerCount(root) {
    const triggerCount = ScrollTrigger
        .getAll()
        .filter((trigger) => {
            const id = trigger.vars.id;

            return typeof id === "string"
                && id.startsWith("gallery-");
        })
        .length;

    root.dataset.galleryTriggerCount =
        String(triggerCount);
}

/**
 * Mark one Gallery panel as the active section.
 */
function setActiveGalleryPanel(
    root,
    panels,
    activePanel,
) {
    if (!(activePanel instanceof HTMLElement)) {
        return;
    }

    root.dataset.galleryActiveSection =
        activePanel.id;

    panels.forEach((panel) => {
        panel.setAttribute(
            "data-gallery-active",
            panel === activePanel
                ? "true"
                : "false",
        );
    });
}

/**
 * Populate the shared lightbox from one server-rendered Gallery link.
 */
function setDialogContent(dialog, opener) {
    const image = dialog.querySelector(
        "[data-gallery-dialog-image]",
    );

    const title = dialog.querySelector(
        "[data-gallery-dialog-title]",
    );

    const category = dialog.querySelector(
        "[data-gallery-dialog-category]",
    );

    if (!(image instanceof HTMLImageElement)) {
        return;
    }

    dialog.dataset.galleryImageState =
        "loading";

    image.src =
        opener.dataset.gallerySrc
        ?? opener.href;

    image.alt =
        opener.dataset.galleryAlt
        ?? "";

    image.sizes = "min(88vw, 1440px)";

    if (opener.dataset.gallerySrcset) {
        image.srcset =
            opener.dataset.gallerySrcset;
    } else {
        image.removeAttribute("srcset");
    }

    if (title) {
        title.textContent =
            opener.dataset.galleryTitle
            ?? "Coast & Cay moment";
    }

    if (category) {
        category.textContent =
            opener.dataset.galleryCategory
            ?? "Coast & Cay";
    }

    if (
        image.complete
        && image.naturalWidth > 0
    ) {
        dialog.dataset.galleryImageState =
            "loaded";
    }
}

/**
 * Initialize the native lightbox while retaining normal anchor fallbacks.
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

    const openers = [
        ...root.querySelectorAll(
            "[data-gallery-open]",
        ),
    ].filter(
        (opener) =>
            opener instanceof HTMLAnchorElement,
    );

    if (
        !(dialog instanceof HTMLDialogElement)
        || openers.length === 0
    ) {
        return () => {};
    }

    const image = dialog.querySelector(
        "[data-gallery-dialog-image]",
    );

    const previousButton = dialog.querySelector(
        "[data-gallery-previous]",
    );

    const nextButton = dialog.querySelector(
        "[data-gallery-next]",
    );

    let activeIndex = 0;
    let activeOpener = null;
    let closeTimer = null;

    /**
     * Display one Gallery image and wrap navigation at either end.
     */
    const showImage = (requestedIndex) => {
        activeIndex =
            (
                requestedIndex
                + openers.length
            )
            % openers.length;

        setDialogContent(
            dialog,
            openers[activeIndex],
        );
    };

    /**
     * Open the lightbox and remember where focus should return.
     */
    const openDialog = (opener) => {
        const openerIndex =
            openers.indexOf(opener);

        if (openerIndex < 0) {
            return;
        }

        activeOpener = opener;

        showImage(openerIndex);

        dialog.classList.remove("is-closing");

        document.documentElement.classList.add(
            "gallery-dialog-open",
        );

        const navigationDisabled =
            openers.length < 2;

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

        if (!dialog.open) {
            dialog.showModal();
        }
    };

    /**
     * Finish closing the dialog and cancel any pending timer.
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
     * Close immediately for reduced motion or use the CSS exit transition.
     */
    const closeDialog = () => {
        if (
            !dialog.open
            || dialog.classList.contains(
                "is-closing",
            )
        ) {
            return;
        }

        const reducedMotion =
            window.matchMedia(
                mediaQueries.reducedMotion,
            ).matches;

        if (reducedMotion) {
            finalizeClose();

            return;
        }

        dialog.classList.add("is-closing");

        closeTimer = window.setTimeout(
            finalizeClose,
            dialogCloseDuration,
        );
    };

    /**
     * Intercept Gallery links only when the native lightbox is available.
     */
    const handleRootClick = (event) => {
        const target =
            event.target instanceof Element
                ? event.target
                : null;

        const opener = target?.closest(
            "[data-gallery-open]",
        );

        if (
            !(
                opener
                instanceof HTMLAnchorElement
            )
        ) {
            return;
        }

        event.preventDefault();
        openDialog(opener);
    };

    /**
     * Handle close, backdrop, previous, and next interactions.
     */
    const handleDialogClick = (event) => {
        if (event.target === dialog) {
            closeDialog();

            return;
        }

        const target =
            event.target instanceof Element
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
            showImage(activeIndex - 1);

            return;
        }

        if (
            target?.closest(
                "[data-gallery-next]",
            )
        ) {
            showImage(activeIndex + 1);
        }
    };

    /**
     * Preserve an animated Escape close rather than closing immediately.
     */
    const handleDialogCancel = (event) => {
        event.preventDefault();
        closeDialog();
    };

    /**
     * Support arrow-key navigation inside the lightbox.
     */
    const handleDialogKeydown = (event) => {
        if (event.key === "ArrowLeft") {
            event.preventDefault();
            showImage(activeIndex - 1);
        }

        if (event.key === "ArrowRight") {
            event.preventDefault();
            showImage(activeIndex + 1);
        }
    };

    /**
     * Reveal the full-size image after loading finishes.
     */
    const handleImageLoad = () => {
        dialog.dataset.galleryImageState =
            "loaded";
    };

    /**
     * Restore focus and release the loaded image after closure.
     */
    const handleDialogClose = () => {
        dialog.classList.remove("is-closing");

        dialog.dataset.galleryImageState =
            "idle";

        document.documentElement.classList.remove(
            "gallery-dialog-open",
        );

        if (
            image instanceof HTMLImageElement
        ) {
            image.removeAttribute("src");
            image.removeAttribute("srcset");
            image.alt = "";
        }

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

    image?.addEventListener(
        "load",
        handleImageLoad,
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

        image?.removeEventListener(
            "load",
            handleImageLoad,
        );

        if (closeTimer !== null) {
            window.clearTimeout(closeTimer);
        }

        document.documentElement.classList.remove(
            "gallery-dialog-open",
        );

        if (dialog.open) {
            dialog.close();
        }
    };
}

/**
 * Animate the Gallery hero immediately after the page runtime loads.
 */
function initializeGalleryHero(
    root,
    showMarkers,
) {
    const hero = root.querySelector(
        "#gallery-hero",
    );

    if (!(hero instanceof HTMLElement)) {
        return;
    }

    const heroImage = hero.querySelector(
        "[data-gallery-hero-image] img",
    );

    const copyTargets = [
        ...hero.querySelectorAll(
            "[data-gallery-hero-copy] "
            + "[data-gallery-reveal]",
        ),
    ];

    const stackCards = [
        ...hero.querySelectorAll(
            "[data-gallery-stack-card]",
        ),
    ];

    hero.dataset.galleryAnimationState =
        "active";

    const timeline = gsap.timeline({
        defaults: {
            ease: "power4.out",
        },
        onComplete: () => {
            hero.dataset.galleryAnimationState =
                "complete";
        },
    });

    if (
        heroImage
        instanceof HTMLImageElement
    ) {
        timeline.fromTo(
            heroImage,
            {
                scale: 1.065,
            },
            {
                duration: 1.45,
                scale: 1,
            },
            0,
        );
    }

    if (copyTargets.length > 0) {
        timeline.fromTo(
            copyTargets,
            {
                autoAlpha: 0,
                y: 36,
            },
            {
                autoAlpha: 1,
                duration: 0.9,
                stagger: 0.09,
                y: 0,
            },
            0.08,
        );
    }

    if (stackCards.length > 0) {
        timeline.fromTo(
            stackCards,
            {
                autoAlpha: 0,
                rotation: (index) =>
                    index % 2 === 0
                        ? 2
                        : -2,
                y: 68,
            },
            {
                autoAlpha: 1,
                duration: 1,
                rotation: 0,
                stagger: 0.11,
                y: 0,
            },
            0.2,
        );
    }

    if (
        heroImage
        instanceof HTMLImageElement
    ) {
        gsap.fromTo(
            heroImage,
            {
                yPercent: 0,
            },
            {
                ease: "none",
                yPercent: 6,
                scrollTrigger: {
                    id: "gallery-depth-hero",
                    trigger: hero,
                    start: "top top",
                    end: "bottom top",
                    scrub: true,
                    invalidateOnRefresh: true,
                    markers: showMarkers,
                },
            },
        );
    }
}

/**
 * Reveal the headings and supporting copy belonging to one Gallery panel.
 */
function initializePanelReveal(
    panel,
    showMarkers,
) {
    const targets = [
        ...panel.querySelectorAll(
            "[data-gallery-reveal]",
        ),
    ];

    if (targets.length === 0) {
        panel.dataset.galleryAnimationState =
            "complete";

        return;
    }

    panel.dataset.galleryAnimationState =
        "pending";

    gsap.timeline({
        scrollTrigger: {
            id: `gallery-reveal-${panel.id}`,
            trigger: panel,
            ...reveal.trigger,
            once: true,
            markers: showMarkers,
        },
        onStart: () => {
            panel.dataset.galleryAnimationState =
                "active";
        },
        onComplete: () => {
            panel.dataset.galleryAnimationState =
                "complete";
        },
    }).fromTo(
        targets,
        {
            autoAlpha: 0,
            y: reveal.distance,
        },
        {
            autoAlpha: 1,
            duration: reveal.duration,
            ease: reveal.ease,
            stagger: reveal.stagger,
            y: 0,
        },
    );
}

/**
 * Give each contact-sheet card its own viewport-based ScrollTrigger.
 */
function initializeGalleryItems(
    root,
    showMarkers,
) {
    const galleryItems = [
        ...root.querySelectorAll(
            "[data-gallery-item]",
        ),
    ].filter(
        (item) =>
            item instanceof HTMLElement,
    );

    galleryItems.forEach((item, index) => {
        const image = item.querySelector("img");

        item.dataset.galleryItemState =
            "pending";

        const timeline = gsap.timeline({
            scrollTrigger: {
                id: `gallery-item-${index + 1}`,
                trigger: item,
                start: "top 86%",
                end: "bottom 18%",
                once: true,
                invalidateOnRefresh: true,
                markers: showMarkers,
            },
            onStart: () => {
                item.dataset.galleryItemState =
                    "active";
            },
            onComplete: () => {
                item.dataset.galleryItemState =
                    "complete";
            },
        });

        timeline.fromTo(
            item,
            {
                autoAlpha: 0,
                y: reveal.distance + 10,
            },
            {
                autoAlpha: 1,
                duration: reveal.duration,
                ease: reveal.ease,
                y: 0,
            },
            0,
        );

        if (
            image
            instanceof HTMLImageElement
        ) {
            timeline.fromTo(
                image,
                {
                    scale: 1.055,
                },
                {
                    duration:
                        reveal.duration + 0.18,
                    ease: reveal.ease,
                    scale: 1,
                },
                0,
            );
        }
    });
}

/**
 * Track the Gallery section crossing the center of the viewport.
 */
function initializeSectionTracking(
    root,
    panels,
    showMarkers,
) {
    panels.forEach((panel) => {
        ScrollTrigger.create({
            id: `gallery-track-${panel.id}`,
            trigger: panel,
            ...tracking.trigger,
            markers: showMarkers,
            onEnter: () => {
                setActiveGalleryPanel(
                    root,
                    panels,
                    panel,
                );
            },
            onEnterBack: () => {
                setActiveGalleryPanel(
                    root,
                    panels,
                    panel,
                );
            },
        });
    });
}

/**
 * Add restrained desktop-only depth motion to marked backgrounds.
 */
function initializeGalleryDepth(
    root,
    showMarkers,
) {
    root.querySelectorAll(
        "[data-gallery-depth]",
    ).forEach((container, index) => {
        const image =
            container.querySelector("img");

        const panel =
            container.closest(
                "[data-gallery-panel]",
            );

        if (
            !(image instanceof HTMLImageElement)
            || !(panel instanceof HTMLElement)
        ) {
            return;
        }

        gsap.fromTo(
            image,
            {
                ...depth.from,
            },
            {
                ...depth.to,
                scrollTrigger: {
                    id:
                        `gallery-depth-${panel.id}-${index}`,
                    trigger: panel,
                    ...depth.trigger,
                    markers: showMarkers,
                },
            },
        );
    });
}

/**
 * Restore all Gallery targets to readable final states for reduced motion.
 */
function setReducedMotionState(
    root,
    panels,
) {
    const targets = root.querySelectorAll(
        [
            "[data-gallery-reveal]",
            "[data-gallery-stack-card]",
            "[data-gallery-item]",
            "[data-gallery-panel] img",
        ].join(", "),
    );

    gsap.set(
        targets,
        {
            autoAlpha: 1,
            clearProps:
                "transform,opacity,"
                + "visibility,willChange",
        },
    );

    panels.forEach((panel) => {
        panel.dataset.galleryAnimationState =
            "complete";
    });

    root.querySelectorAll(
        "[data-gallery-item]",
    ).forEach((item) => {
        if (item instanceof HTMLElement) {
            item.dataset.galleryItemState =
                "complete";
        }
    });

    setGalleryMotionState(
        root,
        "reduced",
    );
}

/**
 * Refresh ScrollTrigger when fonts or responsive images affect layout.
 */
function createRefreshLifecycle(root) {
    let disposed = false;
    let refreshFrame = null;

    /**
     * Queue one refresh on the next browser animation frame.
     */
    const queueRefresh = () => {
        if (disposed) {
            return;
        }

        if (refreshFrame !== null) {
            window.cancelAnimationFrame(
                refreshFrame,
            );
        }

        refreshFrame =
            window.requestAnimationFrame(() => {
                refreshFrame = null;

                ScrollTrigger.refresh();
                updateGalleryTriggerCount(root);
            });
    };

    window.addEventListener(
        "load",
        queueRefresh,
        {
            once: true,
        },
    );

    if (document.fonts?.ready) {
        document.fonts.ready
            .then(queueRefresh)
            .catch(() => {});
    }

    const pendingImages = [
        ...root.querySelectorAll("img"),
    ].filter(
        (image) =>
            image instanceof HTMLImageElement
            && !image.complete,
    );

    pendingImages.forEach((image) => {
        image.addEventListener(
            "load",
            queueRefresh,
            {
                once: true,
            },
        );

        image.addEventListener(
            "error",
            queueRefresh,
            {
                once: true,
            },
        );
    });

    queueRefresh();

    return () => {
        disposed = true;

        if (refreshFrame !== null) {
            window.cancelAnimationFrame(
                refreshFrame,
            );
        }

        window.removeEventListener(
            "load",
            queueRefresh,
        );

        pendingImages.forEach((image) => {
            image.removeEventListener(
                "load",
                queueRefresh,
            );

            image.removeEventListener(
                "error",
                queueRefresh,
            );
        });
    };
}

/**
 * Initialize the complete Gallery ScrollTrigger experience.
 */
function initializeGalleryMotion(root) {
    const panels = getGalleryPanels(root);

    if (panels.length === 0) {
        setGalleryMotionState(
            root,
            "unavailable",
        );

        return () => {};
    }

    killExistingGalleryTriggers();

    setGalleryMotionState(
        root,
        "initializing",
    );

    setActiveGalleryPanel(
        root,
        panels,
        panels[0],
    );

    const showMarkers =
        new URLSearchParams(
            window.location.search,
        ).has("debug-scroll");

    ScrollTrigger.saveStyles(
        root.querySelectorAll(
            [
                "[data-gallery-reveal]",
                "[data-gallery-stack-card]",
                "[data-gallery-item]",
                "[data-gallery-panel] img",
            ].join(", "),
        ),
    );

    const media = gsap.matchMedia();

    media.add(
        {
            desktop:
                mediaQueries.desktop,
            motionAllowed:
                mediaQueries.motionAllowed,
            reducedMotion:
                mediaQueries.reducedMotion,
        },
        (mediaContext) => {
            const {
                desktop = false,
                motionAllowed = true,
                reducedMotion = false,
            } = mediaContext.conditions ?? {};

            initializeSectionTracking(
                root,
                panels,
                showMarkers,
            );

            if (
                reducedMotion
                || !motionAllowed
            ) {
                setReducedMotionState(
                    root,
                    panels,
                );

                updateGalleryTriggerCount(root);

                return () => {};
            }

            initializeGalleryHero(
                root,
                showMarkers,
            );

            panels
                .slice(1)
                .forEach((panel) => {
                    initializePanelReveal(
                        panel,
                        showMarkers,
                    );
                });

            initializeGalleryItems(
                root,
                showMarkers,
            );

            if (desktop) {
                initializeGalleryDepth(
                    root,
                    showMarkers,
                );
            }

            setGalleryMotionState(
                root,
                "ready",
            );

            const cleanupRefresh =
                createRefreshLifecycle(root);

            updateGalleryTriggerCount(root);

            return cleanupRefresh;
        },
    );

    return () => {
        media.revert();

        killExistingGalleryTriggers();

        delete root.dataset
            .galleryTriggerCount;

        delete root.dataset
            .galleryActiveSection;

        panels.forEach((panel) => {
            panel.removeAttribute(
                "data-gallery-active",
            );

            panel.removeAttribute(
                "data-gallery-animation-state",
            );
        });

        root.querySelectorAll(
            "[data-gallery-item]",
        ).forEach((item) => {
            item.removeAttribute(
                "data-gallery-item-state",
            );
        });
    };
}

/**
 * Initialize Gallery lightbox and ScrollTrigger systems.
 */
export function initGalleryExperience(
    root = document.querySelector(
        "[data-gallery-page]",
    ),
) {
    if (!(root instanceof HTMLElement)) {
        return () => {};
    }

    const cleanupMotion =
        initializeGalleryMotion(root);

    const cleanupDialog =
        initializeGalleryDialog(root);

    /**
     * Clean up every Gallery-owned listener, tween, and ScrollTrigger.
     */
    const cleanup = () => {
        cleanupDialog();
        cleanupMotion();
    };

    if (import.meta.hot) {
        import.meta.hot.dispose(cleanup);
    }

    return cleanup;
}
