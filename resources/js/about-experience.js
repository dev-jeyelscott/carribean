import gsap from "gsap";
import { ScrollToPlugin } from "gsap/ScrollToPlugin";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollToPlugin, ScrollTrigger);

const desktopQuery = "(min-width: 1024px) and (pointer: fine)";
const reducedMotionQuery = "(prefers-reduced-motion: reduce)";
const sectionNavigationDuration = 0.68;

/**
 * Return all valid About panels in document order.
 */
function getPanels(root) {
    return [...root.querySelectorAll("[data-about-panel]")].filter(
        (panel) => panel instanceof HTMLElement && panel.id,
    );
}

/**
 * Return the configured compact-header offset in pixels.
 */
function getHeaderOffset(root) {
    const rawValue = window
        .getComputedStyle(root)
        .getPropertyValue("--about-header-height");

    const offset = Number.parseFloat(rawValue);

    return Number.isFinite(offset) ? offset : 0;
}

/**
 * Return the exact document destination for one panel.
 */
function getPanelScrollPosition(root, panel) {
    const panelTop =
        window.scrollY +
        panel.getBoundingClientRect().top;

    const offset =
        panel.id === "about-hero"
            ? 0
            : getHeaderOffset(root);

    return Math.round(panelTop - offset);
}

/**
 * Resolve a hash to one of the registered About panels.
 */
function getPanelFromHash(panels, hash = window.location.hash) {
    if (!hash.startsWith("#")) {
        return null;
    }

    const panelId = decodeURIComponent(hash.slice(1));

    return panels.find((panel) => panel.id === panelId) ?? null;
}

/**
 * Return the panel whose visible area is closest to the viewport center.
 */
function getClosestPanel(panels) {
    const viewportCenter = window.innerHeight / 2;

    return panels.reduce(
        (closestPanel, panel) => {
            const bounds = panel.getBoundingClientRect();
            const panelCenter = bounds.top + bounds.height / 2;
            const distance = Math.abs(
                panelCenter - viewportCenter,
            );

            if (distance >= closestPanel.distance) {
                return closestPanel;
            }

            return {
                panel,
                distance,
            };
        },
        {
            panel: panels[0],
            distance: Number.POSITIVE_INFINITY,
        },
    ).panel;
}

/**
 * Mark one panel and its navigation control as active.
 */
function setActiveSection(root, panels, sectionId) {
    root.dataset.aboutActiveSection = sectionId;

    panels.forEach((panel) => {
        panel.setAttribute(
            "data-about-active",
            panel.id === sectionId ? "true" : "false",
        );
    });

    root.querySelectorAll("[data-about-section-link]").forEach((link) => {
        const isActive =
            link.getAttribute("href") === `#${sectionId}`;

        link.setAttribute(
            "aria-current",
            isActive ? "location" : "false",
        );
    });
}

/**
 * Expose navigation readiness for diagnostics and browser tests.
 */
function setNavigationState(root, state) {
    root.dataset.aboutSectionNavigation = state;
}

/**
 * Count the About-specific ScrollTriggers currently registered.
 */
function updateTriggerCount(root) {
    const triggerCount = ScrollTrigger.getAll().filter((trigger) => {
        const id = trigger.vars.id;

        return (
            typeof id === "string" &&
            id.startsWith("about-")
        );
    }).length;

    root.dataset.aboutTriggerCount = String(triggerCount);
}

/**
 * Animate the initial hero content and image.
 */
function initializeHero(root) {
    const hero = root.querySelector("#about-hero");

    if (!(hero instanceof HTMLElement)) {
        return;
    }

    const image = hero.querySelector(
        "[data-about-hero-image] img",
    );

    const contentTargets = [
        ...hero.querySelectorAll(
            "[data-about-hero-content] [data-about-reveal]",
        ),
    ];

    const scrollControl = hero.querySelector(
        ":scope > [data-about-reveal]",
    );

    hero.dataset.aboutAnimationState = "active";

    const timeline = gsap.timeline({
        defaults: {
            ease: "power4.out",
        },
        onComplete: () => {
            hero.dataset.aboutAnimationState = "complete";
        },
    });

    if (image) {
        timeline.fromTo(
            image,
            {
                scale: 1.07,
            },
            {
                duration: 1.55,
                scale: 1,
            },
            0,
        );
    }

    if (contentTargets.length > 0) {
        timeline.fromTo(
            contentTargets,
            {
                autoAlpha: 0,
                y: 34,
            },
            {
                autoAlpha: 1,
                duration: 0.95,
                stagger: 0.11,
                y: 0,
            },
            0.12,
        );
    }

    if (scrollControl) {
        timeline.fromTo(
            scrollControl,
            {
                autoAlpha: 0,
                y: 12,
            },
            {
                autoAlpha: 1,
                duration: 0.65,
                y: 0,
            },
            0.72,
        );
    }

    /*
     * Add a ScrollTrigger to the hero so its background receives restrained
     * depth while leaving the initial content entrance independent.
     */
    if (image) {
        gsap.fromTo(
            image,
            {
                yPercent: 0,
            },
            {
                ease: "none",
                yPercent: 5,
                scrollTrigger: {
                    id: "about-parallax-about-hero",
                    trigger: hero,
                    start: "top top",
                    end: "bottom top",
                    scrub: true,
                },
            },
        );
    }
}

/**
 * Create one ScrollTrigger-controlled reveal timeline for a section.
 */
function initializePanelAnimation(panel, showMarkers) {
    const revealTargets = [
        ...panel.querySelectorAll("[data-about-reveal]"),
    ];

    const imageFrames = [
        ...panel.querySelectorAll("[data-about-image-reveal]"),
    ];

    const images = imageFrames
        .map((frame) => frame.querySelector("img"))
        .filter((image) => image instanceof HTMLImageElement);

    if (
        revealTargets.length === 0 &&
        imageFrames.length === 0
    ) {
        panel.dataset.aboutAnimationState = "complete";

        return;
    }

    panel.dataset.aboutAnimationState = "pending";

    const timeline = gsap.timeline({
        paused: true,
        defaults: {
            ease: "power3.out",
        },
        onStart: () => {
            panel.dataset.aboutAnimationState = "active";
        },
        onComplete: () => {
            panel.dataset.aboutAnimationState = "complete";
        },
        onReverseComplete: () => {
            panel.dataset.aboutAnimationState = "pending";
        },
    });

    if (revealTargets.length > 0) {
        timeline.fromTo(
            revealTargets,
            {
                autoAlpha: 0,
                y: 48,
            },
            {
                autoAlpha: 1,
                duration: 0.95,
                stagger: 0.1,
                y: 0,
            },
            0,
        );
    }

    if (imageFrames.length > 0) {
        timeline.fromTo(
            imageFrames,
            {
                clipPath: "inset(0 0 100% 0)",
            },
            {
                clipPath: "inset(0 0 0% 0)",
                duration: 1.05,
                stagger: 0.08,
                ease: "power4.out",
            },
            0.08,
        );
    }

    if (images.length > 0) {
        timeline.fromTo(
            images,
            {
                scale: 1.08,
            },
            {
                duration: 1.25,
                scale: 1,
                stagger: 0.08,
            },
            0.08,
        );
    }

    ScrollTrigger.create({
        id: `about-animation-${panel.id}`,
        trigger: panel,
        animation: timeline,

        /*
         * Start after the section is substantially visible. This prevents the
         * animation from completing during the section-navigation tween.
         */
        start: "top 38%",
        end: "bottom 62%",

        toggleActions: "play none restart reverse",
        invalidateOnRefresh: true,
        markers: showMarkers,
    });
}

/**
 * Create active-panel tracking for every About section.
 */
function initializeSectionTracking(root, panels, showMarkers) {
    panels.forEach((panel) => {
        ScrollTrigger.create({
            id: `about-active-${panel.id}`,
            trigger: panel,
            start: "top 52%",
            end: "bottom 48%",
            invalidateOnRefresh: true,
            markers: showMarkers,
            onEnter: () => {
                setActiveSection(root, panels, panel.id);
            },
            onEnterBack: () => {
                setActiveSection(root, panels, panel.id);
            },
        });
    });
}

/**
 * Add restrained desktop parallax to section background images.
 */
function initializeParallax(root, showMarkers) {
    root.querySelectorAll("[data-about-parallax]").forEach(
        (frame, index) => {
            const image = frame.querySelector("img");

            if (!(image instanceof HTMLImageElement)) {
                return;
            }

            /*
             * The hero already owns a dedicated parallax animation.
             */
            if (frame.closest("#about-hero")) {
                return;
            }

            const panel = frame.closest("[data-about-panel]");

            if (!(panel instanceof HTMLElement)) {
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
                        id: `about-parallax-${panel.id}-${index}`,
                        trigger: panel,
                        start: "top bottom",
                        end: "bottom top",
                        scrub: true,
                        invalidateOnRefresh: true,
                        markers: showMarkers,
                    },
                },
            );
        },
    );
}

/**
 * Bind all internal About links to exact header-aware navigation.
 */
function bindSectionLinks(root, panels, reducedMotion) {
    const links = [
        ...root.querySelectorAll('a[href^="#"]'),
    ];

    const cleanupCallbacks = [];
    let activeTween = null;

    /**
     * Navigate one panel beneath the fixed compact header.
     */
    const navigateToPanel = (
        panel,
        {
            updateHistory = true,
        } = {},
    ) => {
        if (!(panel instanceof HTMLElement)) {
            return;
        }

        activeTween?.kill();
        activeTween = null;

        const targetHash = `#${panel.id}`;
        const offsetY =
            panel.id === "about-hero"
                ? 0
                : getHeaderOffset(root);

        if (
            updateHistory &&
            window.location.hash !== targetHash
        ) {
            window.history.pushState(
                null,
                "",
                targetHash,
            );
        }

        setActiveSection(root, panels, panel.id);
        setNavigationState(root, "navigating");

        /**
         * Restore trigger synchronization after navigation settles.
         */
        const settleNavigation = () => {
            activeTween = null;
            setNavigationState(root, "ready");
            ScrollTrigger.update();
            updateTriggerCount(root);
        };

        if (reducedMotion) {
            window.scrollTo({
                top: getPanelScrollPosition(root, panel),
                behavior: "auto",
            });

            settleNavigation();

            return;
        }

        activeTween = gsap.to(window, {
            duration: sectionNavigationDuration,
            ease: "power3.inOut",
            overwrite: "auto",
            scrollTo: {
                y: panel,
                offsetY,
                autoKill: true,
            },
            onComplete: settleNavigation,
            onInterrupt: settleNavigation,
        });
    };

    links.forEach((link) => {
        const targetPanel = getPanelFromHash(
            panels,
            link.getAttribute("href") ?? "",
        );

        if (!targetPanel) {
            return;
        }

        /**
         * Replace the native anchor jump with header-aware navigation.
         */
        const handleClick = (event) => {
            event.preventDefault();
            navigateToPanel(targetPanel);
        };

        link.addEventListener("click", handleClick);

        cleanupCallbacks.push(() => {
            link.removeEventListener(
                "click",
                handleClick,
            );
        });
    });

    /**
     * Restore the correct section when browser history changes.
     */
    const handlePopState = () => {
        const panel =
            getPanelFromHash(panels) ?? panels[0];

        navigateToPanel(panel, {
            updateHistory: false,
        });
    };

    window.addEventListener("popstate", handlePopState);

    cleanupCallbacks.push(() => {
        window.removeEventListener(
            "popstate",
            handlePopState,
        );

        activeTween?.kill();
    });

    return () => {
        cleanupCallbacks
            .reverse()
            .forEach((callback) => callback());
    };
}

/**
 * Restore all animation targets to their final accessible state.
 */
function setReducedMotionState(root, panels) {
    gsap.set(
        root.querySelectorAll(
            [
                "[data-about-reveal]",
                "[data-about-image-reveal]",
                "[data-about-hero-image] img",
                "[data-about-parallax] img",
            ].join(", "),
        ),
        {
            autoAlpha: 1,
            clearProps:
                "clipPath,transform,willChange",
        },
    );

    panels.forEach((panel) => {
        panel.dataset.aboutAnimationState = "complete";
    });
}

/**
 * Refresh trigger geometry and preserve direct hash alignment.
 */
function refreshLayout(root, panels) {
    if (
        root.dataset.aboutSectionNavigation ===
        "navigating"
    ) {
        return;
    }

    ScrollTrigger.refresh();

    const hashPanel = getPanelFromHash(panels);

    if (hashPanel) {
        window.scrollTo({
            top: getPanelScrollPosition(
                root,
                hashPanel,
            ),
            behavior: "auto",
        });

        setActiveSection(
            root,
            panels,
            hashPanel.id,
        );

        ScrollTrigger.update();
        updateTriggerCount(root);

        return;
    }

    const closestPanel = getClosestPanel(panels);

    if (closestPanel) {
        setActiveSection(
            root,
            panels,
            closestPanel.id,
        );
    }

    updateTriggerCount(root);
}

/**
 * Initialize the complete About-page animation experience.
 */
export function initAboutExperience(
    root = document.querySelector("[data-about-page]"),
) {
    if (!(root instanceof HTMLElement)) {
        return () => {};
    }

    const panels = getPanels(root);

    if (panels.length === 0) {
        return () => {};
    }

    const showMarkers = new URLSearchParams(
        window.location.search,
    ).has("debug-scroll");

    document.documentElement.classList.add(
        "about-section-navigation-active",
    );

    setNavigationState(root, "initializing");

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
            } = mediaContext.conditions;

            let disposed = false;

            const animationContext = gsap.context(() => {
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

                    root.dataset.aboutMotion =
                        "reduced";

                    return;
                }

                initializeHero(root);

                panels.slice(1).forEach((panel) => {
                    initializePanelAnimation(
                        panel,
                        showMarkers,
                    );
                });

                if (desktop) {
                    initializeParallax(
                        root,
                        showMarkers,
                    );
                }

                root.dataset.aboutMotion = "ready";
            }, root);

            const removeSectionLinks =
                bindSectionLinks(
                    root,
                    panels,
                    reducedMotion,
                );

            const initialPanel =
                getPanelFromHash(panels) ??
                getClosestPanel(panels);

            if (initialPanel) {
                setActiveSection(
                    root,
                    panels,
                    initialPanel.id,
                );
            }

            /**
             * Calculate trigger positions after the current frame commits.
             */
            const refreshFrame =
                window.requestAnimationFrame(() => {
                    if (disposed) {
                        return;
                    }

                    refreshLayout(root, panels);
                    setNavigationState(root, "ready");
                });

            /**
             * Recalculate after responsive images settle.
             */
            const handleWindowLoad = () => {
                if (!disposed) {
                    refreshLayout(root, panels);
                }
            };

            window.addEventListener(
                "load",
                handleWindowLoad,
                {
                    once: true,
                },
            );

            /**
             * Recalculate after web-font metrics settle.
             */
            document.fonts?.ready
                ?.then(() => {
                    if (!disposed) {
                        refreshLayout(root, panels);
                    }
                })
                .catch(() => {
                    if (!disposed) {
                        setNavigationState(
                            root,
                            "ready",
                        );
                    }
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

                removeSectionLinks();
                animationContext.revert();
            };
        },
    );

    /**
     * Remove every About-specific handler and state value.
     */
    const cleanup = () => {
        media.revert();

        document.documentElement.classList.remove(
            "about-section-navigation-active",
        );

        delete root.dataset.aboutSectionNavigation;
        delete root.dataset.aboutActiveSection;
        delete root.dataset.aboutMotion;
        delete root.dataset.aboutTriggerCount;

        panels.forEach((panel) => {
            panel.removeAttribute(
                "data-about-active",
            );

            panel.removeAttribute(
                "data-about-animation-state",
            );
        });
    };

    if (import.meta.hot) {
        import.meta.hot.dispose(cleanup);
    }

    return cleanup;
}
