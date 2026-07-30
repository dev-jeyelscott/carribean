import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

const reducedMotionQuery =
    "(prefers-reduced-motion: reduce)";

const desktopQuery =
    "(min-width: 1024px) and (pointer: fine)";

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
                reducedMotionQuery,
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
 * Return the Gallery panel currently closest to its expected viewport top.
 */
function getClosestPanel(panels) {
    let closestPanel = panels[0];
    let closestDistance =
        Number.POSITIVE_INFINITY;

    panels.forEach((panel, index) => {
        const expectedTop =
            index === 0 ? 0 : 80;

        const distance = Math.abs(
            panel.getBoundingClientRect().top
            - expectedTop,
        );

        if (distance >= closestDistance) {
            return;
        }

        closestDistance = distance;
        closestPanel = panel;
    });

    return closestPanel;
}

/**
 * Count the Gallery-specific visual ScrollTriggers for diagnostics.
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
 * Animate the Gallery hero immediately without depending on scroll.
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
                    id: "gallery-parallax-hero",
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
 * Create one paused, one-time reveal controller for a non-hero panel.
 */
function createPanelReveal(
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

        return {
            kill: () => {},
            play: () => {},
        };
    }

    gsap.set(revealTargets, {
        autoAlpha: 0,
        y: 42,
    });

    gsap.set(itemTargets, {
        autoAlpha: 0,
        y: 48,
    });

    gsap.set(itemImages, {
        scale: 1.045,
    });

    panel.dataset.galleryAnimationState =
        "pending";

    const timeline = gsap.timeline({
        paused: true,
        defaults: {
            ease: "power3.out",
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
        timeline.to(
            revealTargets,
            {
                autoAlpha: 1,
                duration: 0.85,
                stagger: 0.08,
                y: 0,
            },
            0,
        );
    }

    if (itemTargets.length > 0) {
        timeline.to(
            itemTargets,
            {
                autoAlpha: 1,
                duration: 0.82,
                stagger: 0.07,
                y: 0,
            },
            0.08,
        );
    }

    if (itemImages.length > 0) {
        timeline.to(
            itemImages,
            {
                duration: 1,
                scale: 1,
                stagger: 0.07,
            },
            0.08,
        );
    }

    let hasPlayed = false;

    /**
     * Play the timeline once without hiding readable content again.
     */
    const play = () => {
        if (hasPlayed) {
            return;
        }

        hasPlayed = true;
        timeline.play(0);
    };

    const trigger = ScrollTrigger.create({
        id:
            `gallery-animation-${panel.id}`,
        trigger: panel,
        start: "top 76%",
        end: "bottom 24%",
        invalidateOnRefresh: true,
        markers: showMarkers,
        onEnter: play,
        onEnterBack: play,
    });

    return {
        play,
        kill: () => {
            trigger.kill();
            timeline.kill();
        },
    };
}

/**
 * Add restrained depth to the closing background on desktop.
 */
function initializeClosingParallax(
    root,
    showMarkers,
) {
    const panel = root.querySelector(
        "#gallery-invitation",
    );

    const image = panel?.querySelector(
        ".gallery-cta__background img",
    );

    if (
        !(panel instanceof HTMLElement)
        || !(
            image
            instanceof HTMLImageElement
        )
    ) {
        return;
    }

    gsap.fromTo(
        image,
        {
            yPercent: -3,
        },
        {
            ease: "none",
            yPercent: 3,
            scrollTrigger: {
                id:
                    "gallery-parallax-invitation",
                trigger: panel,
                start: "top bottom",
                end: "bottom top",
                scrub: true,
                invalidateOnRefresh: true,
                markers: showMarkers,
            },
        },
    );
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
 * Initialize section-controlled Gallery animation timelines.
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

    const media = gsap.matchMedia();

    media.add(
        {
            desktop: desktopQuery,
            reducedMotion: reducedMotionQuery,
        },
        (mediaContext) => {
            const {
                desktop = false,
                reducedMotion = false,
            } = mediaContext.conditions ?? {};

            const cleanupCallbacks = [];
            const revealControllers =
                new Map();

            let disposed = false;

            const animationContext =
                gsap.context(() => {
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
                            const controller =
                                createPanelReveal(
                                    panel,
                                    showMarkers,
                                );

                            revealControllers.set(
                                panel.id,
                                controller,
                            );

                            cleanupCallbacks.push(
                                controller.kill,
                            );
                        });

                    if (desktop) {
                        initializeClosingParallax(
                            root,
                            showMarkers,
                        );
                    }

                    root.dataset
                        .galleryMotionState =
                        "ready";
                }, root);

            /**
             * Play the reveal belonging to the section selected by the pager.
             */
            const playSection = (sectionId) => {
                revealControllers
                    .get(sectionId)
                    ?.play();
            };

            /**
             * Synchronize explicit pager navigation with the panel timeline.
             */
            const handleSectionActivation = (
                event,
            ) => {
                if (
                    !(
                        event
                        instanceof CustomEvent
                    )
                    || event.detail?.context
                        !== "gallery"
                ) {
                    return;
                }

                playSection(
                    event.detail.sectionId,
                );
            };

            root.addEventListener(
                "section-pager:activate",
                handleSectionActivation,
            );

            cleanupCallbacks.push(() => {
                root.removeEventListener(
                    "section-pager:activate",
                    handleSectionActivation,
                );
            });

            /**
             * Refresh geometry and activate the initially aligned panel.
             */
            const refreshLayout = () => {
                if (disposed) {
                    return;
                }

                ScrollTrigger.refresh();

                const pager = root.querySelector(
                    '[data-section-pager-context="gallery"]',
                );

                const activeSectionId =
                    pager?.dataset
                        .sectionPagerActive;

                const currentPanel =
                    activeSectionId
                        ? panels.find(
                            (panel) =>
                                panel.id
                                === activeSectionId,
                        )
                        : getClosestPanel(panels);

                if (currentPanel) {
                    playSection(
                        currentPanel.id,
                    );
                }

                updateGalleryTriggerCount(root);
            };

            const refreshFrame =
                window.requestAnimationFrame(
                    refreshLayout,
                );

            window.addEventListener(
                "load",
                refreshLayout,
                {
                    once: true,
                },
            );

            document.fonts?.ready
                ?.then(refreshLayout)
                .catch(() => {});

            cleanupCallbacks.push(() => {
                disposed = true;

                window.cancelAnimationFrame(
                    refreshFrame,
                );

                window.removeEventListener(
                    "load",
                    refreshLayout,
                );
            });

            return () => {
                cleanupCallbacks
                    .reverse()
                    .forEach(
                        (callback) => callback(),
                    );

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

        panels.forEach((panel) => {
            panel.removeAttribute(
                "data-gallery-animation-state",
            );
        });
    };
}

/**
 * Initialize Gallery lightbox and section animation systems.
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
