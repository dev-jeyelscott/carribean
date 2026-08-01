import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import { gallerySectionScrollTriggerConfig } from "./section-scroll-trigger-config";

gsap.registerPlugin(ScrollTrigger);

const {
    depth,
    media: mediaQueries,
    reveal,
    tracking,
} = gallerySectionScrollTriggerConfig;

const dialogCloseDuration = 220;

/**
 * Populate the shared dialog from one server-rendered gallery link.
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

    dialog.dataset.galleryImageState = "loading";

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

    if (image.complete) {
        dialog.dataset.galleryImageState =
            "loaded";
    }
}

/**
 * Initialize the native dialog viewer, keyboard controls, and focus return.
 */
function initializeGalleryDialog(root) {
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
     * Render one image and wrap navigation inside the current result page.
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
     * Open the lightbox and retain the element that should regain focus.
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
     * Complete dialog closure after the CSS exit transition.
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
     * Close immediately for reduced-motion users or animate the normal exit.
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

        if (
            window.matchMedia(
                mediaQueries.reducedMotion,
            ).matches
        ) {
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
     * Intercept image links while retaining their non-JavaScript fallback URL.
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
     * Handle close, previous, next, and backdrop interactions.
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
        } else if (
            target?.closest(
                "[data-gallery-previous]",
            )
        ) {
            showImage(activeIndex - 1);
        } else if (
            target?.closest(
                "[data-gallery-next]",
            )
        ) {
            showImage(activeIndex + 1);
        }
    };

    /**
     * Keep Escape-to-close while allowing the exit transition to finish.
     */
    const handleDialogCancel = (event) => {
        event.preventDefault();
        closeDialog();
    };

    /**
     * Support arrow-key image navigation.
     */
    const handleDialogKeydown = (event) => {
        if (event.key === "ArrowLeft") {
            event.preventDefault();
            showImage(activeIndex - 1);
        } else if (
            event.key === "ArrowRight"
        ) {
            event.preventDefault();
            showImage(activeIndex + 1);
        }
    };

    /**
     * Reveal the full-size image after loading completes.
     */
    const handleImageLoad = () => {
        dialog.dataset.galleryImageState =
            "loaded";
    };

    /**
     * Restore focus and reset temporary dialog media after closure.
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
 * Return every valid Gallery panel in document order.
 */
function getPanels(root) {
    return [
        ...root.querySelectorAll(
            "[data-gallery-panel]",
        ),
    ].filter(
        (panel) =>
            panel instanceof HTMLElement
            && panel.id,
    );
}

/**
 * Mark one Gallery panel as active for diagnostics and future navigation UI.
 */
function setActivePanel(root, panels, activePanel) {
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
 * Count Gallery-specific ScrollTriggers for browser diagnostics.
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
 * Animate the Gallery hero immediately without making readable content depend on scroll.
 */
function initializeHero(root, showMarkers) {
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
            hero.dataset
                .galleryAnimationState =
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
                scale: 1.06,
            },
            {
                duration: 1.55,
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
                y: 34,
            },
            {
                autoAlpha: 1,
                duration: 0.95,
                stagger: 0.1,
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
                y: 72,
            },
            {
                autoAlpha: 1,
                duration: 1.05,
                stagger: 0.12,
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
 * Create one one-time ScrollTrigger reveal timeline for a non-hero panel.
 */
function initializePanelReveal(
    panel,
    showMarkers,
) {
    const revealTargets = [
        ...panel.querySelectorAll(
            "[data-gallery-reveal]",
        ),
    ];

    const itemTargets = [
        ...panel.querySelectorAll(
            "[data-gallery-item]",
        ),
    ];

    const itemImages = itemTargets
        .map((item) =>
            item.querySelector("img"),
        )
        .filter(
            (image) =>
                image
                instanceof HTMLImageElement,
        );

    if (
        revealTargets.length === 0
        && itemTargets.length === 0
    ) {
        panel.dataset.galleryAnimationState =
            "complete";

        return;
    }

    panel.dataset.galleryAnimationState =
        "pending";

    const timeline = gsap.timeline({
        scrollTrigger: {
            id:
                `gallery-reveal-${panel.id}`,
            trigger: panel,
            ...reveal.trigger,
            once: true,
            markers: showMarkers,
        },
        onStart: () => {
            panel.dataset
                .galleryAnimationState =
                "active";
        },
        onComplete: () => {
            panel.dataset
                .galleryAnimationState =
                "complete";
        },
    });

    if (revealTargets.length > 0) {
        timeline.fromTo(
            revealTargets,
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
            0,
        );
    }

    if (itemTargets.length > 0) {
        timeline.fromTo(
            itemTargets,
            {
                autoAlpha: 0,
                y: reveal.distance + 6,
            },
            {
                autoAlpha: 1,
                duration: reveal.duration,
                ease: reveal.ease,
                stagger: reveal.stagger,
                y: 0,
            },
            0.08,
        );
    }

    if (itemImages.length > 0) {
        timeline.fromTo(
            itemImages,
            {
                scale: 1.045,
            },
            {
                duration:
                    reveal.duration + 0.15,
                ease: reveal.ease,
                scale: 1,
                stagger: reveal.stagger,
            },
            0.08,
        );
    }
}

/**
 * Track the panel crossing the viewport center.
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
                setActivePanel(
                    root,
                    panels,
                    panel,
                );
            },
            onEnterBack: () => {
                setActivePanel(
                    root,
                    panels,
                    panel,
                );
            },
        });
    });
}

/**
 * Add restrained shared depth motion to marked Gallery backgrounds.
 */
function initializeDepth(root, showMarkers) {
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
 * Restore every Gallery target to its final readable reduced-motion state.
 */
function setReducedMotionState(
    root,
    panels,
) {
    gsap.set(
        root.querySelectorAll(
            [
                "[data-gallery-reveal]",
                "[data-gallery-stack-card]",
                "[data-gallery-item]",
                "[data-gallery-panel] img",
            ].join(", "),
        ),
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

    root.dataset.galleryMotionState =
        "reduced";
}

/**
 * Refresh ScrollTrigger after layout-affecting resources settle.
 */
function createRefreshLifecycle(root) {
    let disposed = false;

    const refresh = () => {
        if (disposed) {
            return;
        }

        ScrollTrigger.refresh();
        updateGalleryTriggerCount(root);
    };

    const refreshFrame =
        window.requestAnimationFrame(refresh);

    const handleWindowLoad = () => {
        refresh();
    };

    window.addEventListener(
        "load",
        handleWindowLoad,
        {
            once: true,
        },
    );

    document.fonts?.ready
        ?.then(refresh)
        .catch(() => {});

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
            refresh,
            {
                once: true,
            },
        );

        image.addEventListener(
            "error",
            refresh,
            {
                once: true,
            },
        );
    });

    return () => {
        disposed = true;

        window.cancelAnimationFrame(
            refreshFrame,
        );

        window.removeEventListener(
            "load",
            handleWindowLoad,
        );

        pendingImages.forEach((image) => {
            image.removeEventListener(
                "load",
                refresh,
            );

            image.removeEventListener(
                "error",
                refresh,
            );
        });
    };
}

/**
 * Initialize the complete Gallery ScrollTrigger system.
 */
function initializeGalleryMotion(root) {
    const panels = getPanels(root);

    if (panels.length === 0) {
        root.dataset.galleryMotionState =
            "unavailable";

        return () => {};
    }

    const showMarkers = new URLSearchParams(
        window.location.search,
    ).has("debug-scroll");

    setActivePanel(
        root,
        panels,
        panels[0],
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
                reducedMotion = false,
            } = mediaContext.conditions ?? {};

            const cleanupRefresh =
                createRefreshLifecycle(root);

            const animationContext =
                gsap.context(() => {
                    initializeSectionTracking(
                        root,
                        panels,
                        showMarkers,
                    );

                    if (reducedMotion) {
                        setReducedMotionState(
                            root,
                            panels,
                        );

                        return;
                    }

                    initializeHero(
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

                    if (desktop) {
                        initializeDepth(
                            root,
                            showMarkers,
                        );
                    }

                    root.dataset
                        .galleryMotionState =
                        "ready";
                }, root);

            updateGalleryTriggerCount(root);

            return () => {
                cleanupRefresh();
                animationContext.revert();
            };
        },
    );

    return () => {
        media.revert();

        delete root.dataset
            .galleryTriggerCount;

        delete root.dataset
            .galleryMotionState;

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

    const cleanupDialog =
        initializeGalleryDialog(root);

    const cleanupMotion =
        initializeGalleryMotion(root);

    const cleanup = () => {
        cleanupDialog();
        cleanupMotion();
    };

    if (import.meta.hot) {
        import.meta.hot.dispose(cleanup);
    }

    return cleanup;
}
