import gsap from "gsap";
import { ScrollToPlugin } from "gsap/ScrollToPlugin";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollToPlugin, ScrollTrigger);

const desktopQuery = "(min-width: 1024px) and (pointer: fine)";
const shortViewportQuery = "(max-height: 899px)";
const reducedMotionQuery = "(prefers-reduced-motion: reduce)";

const sectionNavigationDuration = 0.62;

/**
 * Return every valid About panel in document order.
 */
function getPanels(root) {
    return [...root.querySelectorAll("[data-about-panel]")].filter(
        (panel) => panel instanceof HTMLElement && panel.id,
    );
}

/**
 * Return the panel whose top edge is nearest to the viewport top.
 */
function getClosestPanelIndex(panels) {
    let closestIndex = 0;
    let closestDistance = Number.POSITIVE_INFINITY;

    panels.forEach((panel, index) => {
        const distance = Math.abs(panel.getBoundingClientRect().top);

        if (distance >= closestDistance) {
            return;
        }

        closestDistance = distance;
        closestIndex = index;
    });

    return closestIndex;
}

/**
 * Return the exact document scroll position for a panel.
 *
 * A numeric value deliberately avoids native scroll-padding and scroll-margin
 * adjustments that would otherwise expose part of the previous section.
 */
function getPanelScrollPosition(panel) {
    return Math.round(
        window.scrollY + panel.getBoundingClientRect().top,
    );
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
 * Mark one About panel and its navigation link as active.
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
        const isActive = link.getAttribute("href") === `#${sectionId}`;

        link.setAttribute(
            "aria-current",
            isActive ? "location" : "false",
        );
    });
}

/**
 * Expose navigation readiness for diagnostics and browser acceptance tests.
 */
function setNavigationState(root, state) {
    root.dataset.aboutSectionNavigation = state;
}

/**
 * Create ScrollTriggers that keep the dot navigation synchronized with the
 * section crossing the center of the viewport.
 */
function initializeSectionTracking(root, panels) {
    return panels.map((panel) =>
        ScrollTrigger.create({
            trigger: panel,
            start: "top 51%",
            end: "bottom 49%",
            onEnter: () => {
                setActiveSection(root, panels, panel.id);
            },
            onEnterBack: () => {
                setActiveSection(root, panels, panel.id);
            },
        }),
    );
}

/**
 * Animate the hero image and content after JavaScript successfully loads.
 */
function initializeHero(root) {
    const cleanup = [];

    const heroImage = root.querySelector(
        "[data-about-hero-image] img",
    );

    if (heroImage) {
        const imageTween = gsap.fromTo(
            heroImage,
            {
                scale: 1.06,
            },
            {
                duration: 1.6,
                ease: "power4.out",
                scale: 1,
            },
        );

        cleanup.push(() => {
            imageTween.kill();
        });
    }

    const heroContent = root.querySelector(
        "[data-about-hero-content]",
    );

    if (heroContent) {
        const targets = [
            ...heroContent.querySelectorAll("[data-about-reveal]"),
        ];

        if (targets.length > 0) {
            gsap.set(targets, {
                autoAlpha: 0,
                y: 26,
            });

            const timeline = gsap.timeline({
                defaults: {
                    ease: "power4.out",
                },
            });

            timeline.to(targets, {
                autoAlpha: 1,
                duration: 1,
                stagger: 0.11,
                y: 0,
            });

            cleanup.push(() => {
                timeline.kill();
            });
        }
    }

    const scrollControl = root.querySelector(
        "#about-hero > [data-about-reveal]",
    );

    if (scrollControl) {
        const scrollTween = gsap.fromTo(
            scrollControl,
            {
                autoAlpha: 0,
                y: 12,
            },
            {
                autoAlpha: 1,
                delay: 0.75,
                duration: 0.7,
                ease: "power3.out",
                y: 0,
            },
        );

        cleanup.push(() => {
            scrollTween.kill();
        });
    }

    return () => {
        cleanup.reverse().forEach((callback) => callback());
    };
}

/**
 * Create one coordinated reveal timeline for one non-hero panel.
 *
 * The hidden starting state is applied only after JavaScript loads, preserving
 * readable server-rendered content if JavaScript fails.
 */
function createPanelReveal(panel) {
    const targets = [
        ...panel.querySelectorAll("[data-about-reveal]"),
    ];

    if (targets.length === 0) {
        return () => {};
    }

    gsap.set(targets, {
        autoAlpha: 0,
        y: 30,
    });

    const timeline = gsap.timeline({
        paused: true,
        defaults: {
            duration: 0.85,
            ease: "power3.out",
        },
    });

    timeline.to(targets, {
        autoAlpha: 1,
        stagger: 0.09,
        y: 0,
    });

    let hasPlayed = false;

    /**
     * Play the section reveal once.
     */
    const play = () => {
        if (hasPlayed) {
            return;
        }

        hasPlayed = true;
        timeline.play();
    };

    const trigger = ScrollTrigger.create({
        trigger: panel,
        start: "top 72%",
        end: "bottom 28%",
        onEnter: play,
        onEnterBack: play,
    });

    /*
     * A deep-linked section may already be visible by the time ScrollTrigger
     * initializes. Reveal it immediately instead of leaving content hidden.
     */
    if (ScrollTrigger.isInViewport(panel, 0.12)) {
        window.requestAnimationFrame(play);
    }

    return () => {
        trigger.kill();
        timeline.kill();
    };
}

/**
 * Add a reveal timeline to every non-hero About panel.
 */
function initializePanelReveals(panels) {
    return panels.slice(1).map((panel) => createPanelReveal(panel));
}

/**
 * Reveal editorial images through a restrained clipping transition.
 */
function initializeImageReveals(root) {
    const cleanup = [];

    root.querySelectorAll("[data-about-image-reveal]").forEach((frame) => {
        const image = frame.querySelector("img");

        if (!image) {
            return;
        }

        gsap.set(frame, {
            clipPath: "inset(0 0 100% 0)",
        });

        gsap.set(image, {
            scale: 1.05,
        });

        const timeline = gsap.timeline({
            paused: true,
        });

        timeline
            .to(frame, {
                clipPath: "inset(0 0 0% 0)",
                duration: 0.95,
                ease: "power4.out",
            })
            .to(
                image,
                {
                    duration: 1.15,
                    ease: "power3.out",
                    scale: 1,
                },
                "<",
            );

        let hasPlayed = false;

        /**
         * Play the image reveal once.
         */
        const play = () => {
            if (hasPlayed) {
                return;
            }

            hasPlayed = true;
            timeline.play();
        };

        const trigger = ScrollTrigger.create({
            trigger: frame,
            start: "top 82%",
            onEnter: play,
            onEnterBack: play,
        });

        if (ScrollTrigger.isInViewport(frame, 0.08)) {
            window.requestAnimationFrame(play);
        }

        cleanup.push(() => {
            trigger.kill();
            timeline.kill();
        });
    });

    return () => {
        cleanup.reverse().forEach((callback) => callback());
    };
}

/**
 * Add subtle image depth on tall desktop displays.
 *
 * Native scrolling remains fully controlled by the browser and visitor.
 */
function initializeParallax(root) {
    const cleanup = [];

    root.querySelectorAll("[data-about-parallax]").forEach((frame) => {
        const image = frame.querySelector("img");

        if (!image) {
            return;
        }

        const tween = gsap.fromTo(
            image,
            {
                yPercent: -3,
            },
            {
                ease: "none",
                yPercent: 3,
                scrollTrigger: {
                    trigger: frame,
                    start: "top bottom",
                    end: "bottom top",
                    scrub: true,
                },
            },
        );

        cleanup.push(() => {
            tween.scrollTrigger?.kill();
            tween.kill();
        });
    });

    return () => {
        cleanup.reverse().forEach((callback) => callback());
    };
}

/**
 * Bind every About-page hash link to exact GSAP section navigation.
 *
 * This includes:
 * - The fixed dot navigation
 * - Explore Our Story
 * - The hero Scroll control
 */
function bindSectionLinks(root, panels, reducedMotion) {
    const links = [...root.querySelectorAll('a[href^="#"]')];
    const cleanup = [];

    let activeTween = null;

    /**
     * Scroll one panel precisely to the top of the viewport.
     */
    const navigateToPanel = (
        targetPanel,
        {
            updateHistory = true,
        } = {},
    ) => {
        if (!(targetPanel instanceof HTMLElement)) {
            return;
        }

        activeTween?.kill();
        activeTween = null;

        const targetHash = `#${targetPanel.id}`;
        const targetY = getPanelScrollPosition(targetPanel);

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

        setActiveSection(root, panels, targetPanel.id);
        setNavigationState(root, "navigating");

        /**
         * Restore the ready state after a completed navigation.
         */
        const settle = () => {
            activeTween = null;
            setNavigationState(root, "ready");
            ScrollTrigger.update();
        };

        if (reducedMotion) {
            window.scrollTo({
                top: targetY,
                behavior: "auto",
            });

            settle();

            return;
        }

        activeTween = gsap.to(window, {
            duration: sectionNavigationDuration,
            ease: "power3.inOut",
            overwrite: "auto",
            scrollTo: {
                y: targetY,
                autoKill: true,
            },
            onComplete: settle,
            onInterrupt: settle,
        });
    };

    links.forEach((link) => {
        const selector = link.getAttribute("href");

        if (!selector?.startsWith("#")) {
            return;
        }

        const targetPanel = getPanelFromHash(
            panels,
            selector,
        );

        if (!targetPanel) {
            return;
        }

        /**
         * Replace native anchor scrolling with exact GSAP navigation.
         */
        const handleClick = (event) => {
            event.preventDefault();

            navigateToPanel(targetPanel);
        };

        link.addEventListener("click", handleClick);

        cleanup.push(() => {
            link.removeEventListener("click", handleClick);
        });
    });

    /**
     * Restore the corresponding section when browser history changes.
     */
    const handlePopState = () => {
        const targetPanel =
            getPanelFromHash(panels) ?? panels[0];

        if (targetPanel) {
            navigateToPanel(targetPanel, {
                updateHistory: false,
            });
        }
    };

    window.addEventListener("popstate", handlePopState);

    cleanup.push(() => {
        window.removeEventListener(
            "popstate",
            handlePopState,
        );

        activeTween?.kill();
    });

    return {
        navigateToPanel,

        cleanup: () => {
            cleanup.reverse().forEach((callback) => callback());
        },
    };
}

/**
 * Restore every animated element to its readable final state.
 */
function setReducedMotionState(root) {
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
            clearProps: "clipPath,transform,willChange",
        },
    );
}

/**
 * Refresh ScrollTrigger positions and preserve exact hash alignment.
 */
function refreshLayout(root, panels) {
    if (
        root.dataset.aboutSectionNavigation === "navigating"
    ) {
        return;
    }

    ScrollTrigger.refresh();

    const hashPanel = getPanelFromHash(panels);

    if (hashPanel) {
        window.scrollTo({
            top: getPanelScrollPosition(hashPanel),
            behavior: "auto",
        });

        setActiveSection(root, panels, hashPanel.id);
        ScrollTrigger.update();

        return;
    }

    const closestIndex = getClosestPanelIndex(panels);
    const closestPanel = panels[closestIndex];

    if (closestPanel) {
        setActiveSection(root, panels, closestPanel.id);
    }
}

/**
 * Initialize the complete About-page experience.
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

    document.documentElement.classList.add(
        "about-section-navigation-active",
    );

    setNavigationState(root, "initializing");

    const media = gsap.matchMedia();

    media.add(
        {
            desktop: desktopQuery,
            shortViewport: shortViewportQuery,
            reducedMotion: reducedMotionQuery,
        },
        (mediaContext) => {
            const {
                desktop = false,
                shortViewport = false,
                reducedMotion = false,
            } = mediaContext.conditions;

            const cleanup = [];

            const animationContext = gsap.context(() => {
                const sectionTriggers =
                    initializeSectionTracking(
                        root,
                        panels,
                    );

                cleanup.push(() => {
                    sectionTriggers.forEach((trigger) => {
                        trigger.kill();
                    });
                });

                const sectionLinks = bindSectionLinks(
                    root,
                    panels,
                    reducedMotion,
                );

                cleanup.push(sectionLinks.cleanup);

                if (reducedMotion) {
                    setReducedMotionState(root);
                    root.dataset.aboutMotion = "reduced";
                } else {
                    cleanup.push(initializeHero(root));

                    cleanup.push(
                        ...initializePanelReveals(panels),
                    );

                    cleanup.push(
                        initializeImageReveals(root),
                    );

                    if (desktop && !shortViewport) {
                        cleanup.push(
                            initializeParallax(root),
                        );
                    }

                    root.dataset.aboutMotion = "ready";
                }

                const initialPanel =
                    getPanelFromHash(panels) ??
                    panels[getClosestPanelIndex(panels)];

                if (initialPanel) {
                    setActiveSection(
                        root,
                        panels,
                        initialPanel.id,
                    );
                }
            }, root);

            cleanup.push(() => {
                animationContext.revert();
            });

            /**
             * Perform the initial layout calculation after the browser has
             * committed current styles.
             */
            const initialRefreshFrame =
                window.requestAnimationFrame(() => {
                    refreshLayout(root, panels);
                    setNavigationState(root, "ready");
                });

            cleanup.push(() => {
                window.cancelAnimationFrame(
                    initialRefreshFrame,
                );
            });

            /**
             * Recalculate after responsive images finish loading.
             */
            const handleLoad = () => {
                refreshLayout(root, panels);
            };

            window.addEventListener("load", handleLoad, {
                once: true,
            });

            cleanup.push(() => {
                window.removeEventListener(
                    "load",
                    handleLoad,
                );
            });

            /**
             * Web-font metrics can change section positions after first paint.
             */
            document.fonts?.ready
                ?.then(() => {
                    refreshLayout(root, panels);
                })
                .catch(() => {
                    setNavigationState(root, "ready");
                });

            return () => {
                cleanup.reverse().forEach((callback) => {
                    callback();
                });
            };
        },
    );

    /**
     * Remove all About-specific classes, media handlers, tweens, and triggers.
     */
    const cleanup = () => {
        media.revert();

        document.documentElement.classList.remove(
            "about-section-navigation-active",
        );

        delete root.dataset.aboutSectionNavigation;
        delete root.dataset.aboutActiveSection;
        delete root.dataset.aboutMotion;
    };

    if (import.meta.hot) {
        import.meta.hot.dispose(cleanup);
    }

    return cleanup;
}
