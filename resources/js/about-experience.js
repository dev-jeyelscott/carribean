import gsap from "gsap";
import { ScrollToPlugin } from "gsap/ScrollToPlugin";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollToPlugin, ScrollTrigger);

const desktopQuery = "(min-width: 1024px) and (pointer: fine)";
const reducedMotionQuery = "(prefers-reduced-motion: reduce)";
const shortViewportQuery = "(max-height: 719px)";

const wheelActivationThreshold = 8;
const wheelGestureReleaseDelay = 140;
const sectionTransitionDuration = 0.48;

/**
 * Return every valid About panel in document order.
 */
function getPanels(root) {
    return [...root.querySelectorAll("[data-about-panel]")].filter(
        (panel) => panel instanceof HTMLElement && panel.id,
    );
}

/**
 * Return the compact public-header height used while content panels are active.
 */
function getHeaderOffset() {
    return window.matchMedia("(max-width: 1023px)").matches ? 76 : 80;
}

/**
 * Return the exact document destination for one About panel.
 */
function getPanelScrollPosition(panel) {
    const panelTop = window.scrollY + panel.getBoundingClientRect().top;
    const offset = panel.id === "about-hero" ? 0 : getHeaderOffset();

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
 * Return the panel aligned closest to its expected viewport position.
 */
function getClosestPanelIndex(panels) {
    let closestIndex = 0;
    let closestDistance = Number.POSITIVE_INFINITY;

    panels.forEach((panel, index) => {
        const expectedTop = panel.id === "about-hero" ? 0 : getHeaderOffset();
        const distance = Math.abs(
            panel.getBoundingClientRect().top - expectedTop,
        );

        if (distance >= closestDistance) {
            return;
        }

        closestDistance = distance;
        closestIndex = index;
    });

    return closestIndex;
}

/**
 * Determine whether the viewport center is inside the About panel region.
 */
function isAboutRegionActive(root) {
    const bounds = root.getBoundingClientRect();
    const viewportCenter = window.innerHeight / 2;

    return bounds.top <= viewportCenter && bounds.bottom > viewportCenter;
}

/**
 * Normalize wheel values reported as pixels, lines, or pages.
 */
function normalizeWheelDelta(event) {
    if (event.deltaMode === WheelEvent.DOM_DELTA_LINE) {
        return event.deltaY * 16;
    }

    if (event.deltaMode === WheelEvent.DOM_DELTA_PAGE) {
        return event.deltaY * window.innerHeight;
    }

    return event.deltaY;
}

/**
 * Preserve native scrolling inside independently scrollable descendants.
 */
function hasScrollableAncestor(target, root, deltaY) {
    let element = target instanceof Element ? target : null;

    while (element && element !== root) {
        const styles = window.getComputedStyle(element);
        const allowsVerticalScrolling =
            styles.overflowY === "auto" || styles.overflowY === "scroll";

        if (
            allowsVerticalScrolling &&
            element.scrollHeight > element.clientHeight
        ) {
            const canScrollUp = deltaY < 0 && element.scrollTop > 0;
            const canScrollDown =
                deltaY > 0 &&
                element.scrollTop + element.clientHeight <
                    element.scrollHeight - 1;

            if (canScrollUp || canScrollDown) {
                return true;
            }
        }

        element = element.parentElement;
    }

    return false;
}

/**
 * Mark one panel and its right-side navigation control as active.
 */
function setActivePanel(root, panels, activeIndex) {
    const activePanel = panels[activeIndex];

    if (!(activePanel instanceof HTMLElement)) {
        return;
    }

    root.dataset.aboutActiveSection = activePanel.id;

    panels.forEach((panel, index) => {
        panel.setAttribute(
            "data-about-active",
            index === activeIndex ? "true" : "false",
        );
    });

    root.querySelectorAll("[data-about-section-link]").forEach((link) => {
        const isActive = link.getAttribute("href") === `#${activePanel.id}`;

        link.setAttribute(
            "aria-current",
            isActive ? "location" : "false",
        );
    });
}

/**
 * Expose navigation readiness for browser diagnostics and regression tests.
 */
function setNavigationState(root, state) {
    root.dataset.aboutSectionNavigation = state;
}

/**
 * Increment the completed desktop section-transition counter.
 */
function recordCompletedSnap(root) {
    const currentCount = Number.parseInt(root.dataset.aboutSnapCount ?? "0", 10);

    root.dataset.aboutSnapCount = String(currentCount + 1);
}

/**
 * Count the About-specific ScrollTriggers currently registered.
 */
function updateTriggerCount(root) {
    const triggerCount = ScrollTrigger.getAll().filter((trigger) => {
        const id = trigger.vars.id;

        return typeof id === "string" && id.startsWith("about-");
    }).length;

    root.dataset.aboutTriggerCount = String(triggerCount);
}

/**
 * Animate the hero immediately without making initial content depend on scroll.
 */
function initializeHero(root) {
    const hero = root.querySelector("#about-hero");

    if (!(hero instanceof HTMLElement)) {
        return;
    }

    const image = hero.querySelector("[data-about-hero-image] img");
    const contentTargets = [
        ...hero.querySelectorAll(
            "[data-about-hero-content] [data-about-reveal]",
        ),
    ];
    const scrollControl = hero.querySelector(":scope > [data-about-reveal]");

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
                    invalidateOnRefresh: true,
                },
            },
        );
    }
}

/**
 * Create a one-time reveal controller for one non-hero panel.
 */
function createPanelReveal(panel, showMarkers) {
    const revealTargets = [
        ...panel.querySelectorAll("[data-about-reveal]"),
    ];
    const imageFrames = [
        ...panel.querySelectorAll("[data-about-image-reveal]"),
    ];
    const images = imageFrames
        .map((frame) => frame.querySelector("img"))
        .filter((image) => image instanceof HTMLImageElement);

    if (revealTargets.length === 0 && imageFrames.length === 0) {
        panel.dataset.aboutAnimationState = "complete";

        return {
            play: () => {},
            kill: () => {},
        };
    }

    gsap.set(revealTargets, {
        autoAlpha: 0,
        y: 42,
    });

    gsap.set(imageFrames, {
        clipPath: "inset(0 0 100% 0)",
    });

    gsap.set(images, {
        scale: 1.07,
    });

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
    });

    if (revealTargets.length > 0) {
        timeline.to(
            revealTargets,
            {
                autoAlpha: 1,
                duration: 0.9,
                stagger: 0.09,
                y: 0,
            },
            0,
        );
    }

    if (imageFrames.length > 0) {
        timeline.to(
            imageFrames,
            {
                clipPath: "inset(0 0 0% 0)",
                duration: 1,
                stagger: 0.08,
                ease: "power4.out",
            },
            0.06,
        );
    }

    if (images.length > 0) {
        timeline.to(
            images,
            {
                duration: 1.2,
                scale: 1,
                stagger: 0.08,
            },
            0.06,
        );
    }

    let hasPlayed = false;

    /**
     * Play the reveal once and never hide readable content again.
     */
    const play = () => {
        if (hasPlayed) {
            return;
        }

        hasPlayed = true;
        timeline.play(0);
    };

    const trigger = ScrollTrigger.create({
        id: `about-animation-${panel.id}`,
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
 * Keep the active right-side navigation item synchronized with scrolling.
 */
function initializeSectionTracking(root, panels, showMarkers) {
    return panels.map((panel, index) =>
        ScrollTrigger.create({
            id: `about-active-${panel.id}`,
            trigger: panel,
            start: "top 52%",
            end: "bottom 48%",
            invalidateOnRefresh: true,
            markers: showMarkers,
            onEnter: () => {
                setActivePanel(root, panels, index);
            },
            onEnterBack: () => {
                setActivePanel(root, panels, index);
            },
        }),
    );
}

/**
 * Add restrained desktop parallax to non-hero section backgrounds.
 */
function initializeParallax(root, showMarkers) {
    root.querySelectorAll("[data-about-parallax]").forEach((frame, index) => {
        if (frame.closest("#about-hero")) {
            return;
        }

        const image = frame.querySelector("img");
        const panel = frame.closest("[data-about-panel]");

        if (
            !(image instanceof HTMLImageElement) ||
            !(panel instanceof HTMLElement)
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
    });
}

/**
 * Restore every animated target to its accessible final state.
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
            clearProps: "clipPath,transform,willChange",
        },
    );

    panels.forEach((panel) => {
        panel.dataset.aboutAnimationState = "complete";
    });
}

/**
 * Initialize the complete About-page animation and section-navigation system.
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

    const showMarkers = new URLSearchParams(window.location.search).has(
        "debug-scroll",
    );

    document.documentElement.classList.add(
        "about-section-navigation-active",
    );

    root.dataset.aboutSnapCount = "0";
    setNavigationState(root, "initializing");

    const media = gsap.matchMedia();

    media.add(
        {
            desktop: desktopQuery,
            reducedMotion: reducedMotionQuery,
            shortViewport: shortViewportQuery,
        },
        (mediaContext) => {
            const {
                desktop = false,
                reducedMotion = false,
                shortViewport = false,
            } = mediaContext.conditions ?? {};

            const cleanupCallbacks = [];
            const revealControllers = new Map();
            let disposed = false;
            let activeIndex = getClosestPanelIndex(panels);
            let activeTween = null;
            let isAnimating = false;
            let isGestureLocked = false;
            let wheelIsActive = false;
            let accumulatedWheelDelta = 0;
            let gestureReleaseTimer = null;

            const animationContext = gsap.context(() => {
                const activeTriggers = initializeSectionTracking(
                    root,
                    panels,
                    showMarkers,
                );

                cleanupCallbacks.push(() => {
                    activeTriggers.forEach((trigger) => trigger.kill());
                });

                if (reducedMotion) {
                    setReducedMotionState(root, panels);
                    root.dataset.aboutMotion = "reduced";

                    return;
                }

                initializeHero(root);

                panels.slice(1).forEach((panel) => {
                    const controller = createPanelReveal(panel, showMarkers);

                    revealControllers.set(panel.id, controller);
                    cleanupCallbacks.push(controller.kill);
                });

                if (desktop) {
                    initializeParallax(root, showMarkers);
                }

                root.dataset.aboutMotion = "ready";
            }, root);

            /**
             * Update the active panel and start its reveal immediately.
             */
            const activatePanel = (index) => {
                activeIndex = Math.max(0, Math.min(index, panels.length - 1));
                setActivePanel(root, panels, activeIndex);

                const panel = panels[activeIndex];

                if (panel) {
                    revealControllers.get(panel.id)?.play();
                }
            };

            /**
             * Return navigation to its ready state after a tween settles.
             */
            const settleNavigation = (completedSnap = false) => {
                isAnimating = false;
                activeTween = null;

                if (completedSnap) {
                    recordCompletedSnap(root);
                }

                setNavigationState(root, "ready");
                ScrollTrigger.update();
                updateTriggerCount(root);

                if (!wheelIsActive) {
                    isGestureLocked = false;
                }
            };

            /**
             * Scroll to one panel and keep animation, active state, and history aligned.
             */
            const navigateToPanel = (
                targetIndex,
                {
                    updateHistory = false,
                    recordSnap = false,
                } = {},
            ) => {
                const panel = panels[targetIndex];

                if (!(panel instanceof HTMLElement)) {
                    return;
                }

                activeTween?.kill();
                activeTween = null;

                if (updateHistory) {
                    const targetHash = `#${panel.id}`;

                    if (window.location.hash !== targetHash) {
                        window.history.pushState(null, "", targetHash);
                    }
                }

                activatePanel(targetIndex);
                setNavigationState(root, "navigating");

                if (reducedMotion) {
                    window.scrollTo({
                        behavior: "auto",
                        left: 0,
                        top: getPanelScrollPosition(panel),
                    });

                    settleNavigation(recordSnap);

                    return;
                }

                isAnimating = true;

                activeTween = gsap.to(window, {
                    duration: sectionTransitionDuration,
                    ease: "power3.out",
                    overwrite: "auto",
                    scrollTo: {
                        autoKill: false,
                        y: getPanelScrollPosition(panel),
                    },
                    onComplete: () => {
                        settleNavigation(recordSnap);
                    },
                    onInterrupt: () => {
                        settleNavigation(false);
                    },
                });
            };

            /**
             * Bind all About-page hash links to the shared navigation function.
             */
            root.querySelectorAll('a[href^="#"]').forEach((link) => {
                const targetPanel = getPanelFromHash(
                    panels,
                    link.getAttribute("href") ?? "",
                );

                if (!targetPanel) {
                    return;
                }

                const targetIndex = panels.indexOf(targetPanel);
                const handleClick = (event) => {
                    event.preventDefault();
                    navigateToPanel(targetIndex, {
                        updateHistory: true,
                    });
                };

                link.addEventListener("click", handleClick);

                cleanupCallbacks.push(() => {
                    link.removeEventListener("click", handleClick);
                });
            });

            /**
             * Restore the requested panel when browser history changes.
             */
            const handlePopState = () => {
                const panel = getPanelFromHash(panels) ?? panels[0];
                const panelIndex = panels.indexOf(panel);

                navigateToPanel(panelIndex);
            };

            window.addEventListener("popstate", handlePopState);
            cleanupCallbacks.push(() => {
                window.removeEventListener("popstate", handlePopState);
            });

            const canSnap =
                desktop &&
                !reducedMotion &&
                !shortViewport &&
                panels.length > 1;

            if (canSnap) {
                document.documentElement.classList.add(
                    "about-section-snap-active",
                );

                /**
                 * Release one wheel gesture only after its momentum settles.
                 */
                const scheduleGestureRelease = () => {
                    wheelIsActive = true;

                    if (gestureReleaseTimer !== null) {
                        window.clearTimeout(gestureReleaseTimer);
                    }

                    gestureReleaseTimer = window.setTimeout(() => {
                        wheelIsActive = false;
                        accumulatedWheelDelta = 0;
                        gestureReleaseTimer = null;

                        if (!isAnimating) {
                            isGestureLocked = false;
                        }
                    }, wheelGestureReleaseDelay);
                };

                /**
                 * Convert one desktop wheel gesture into one adjacent section.
                 */
                const handleWheel = (event) => {
                    if (
                        event.defaultPrevented ||
                        event.ctrlKey ||
                        !isAboutRegionActive(root)
                    ) {
                        return;
                    }

                    const deltaY = normalizeWheelDelta(event);

                    if (
                        Math.abs(event.deltaX) > Math.abs(deltaY) ||
                        Math.abs(deltaY) < 0.5
                    ) {
                        return;
                    }

                    if (hasScrollableAncestor(event.target, root, deltaY)) {
                        return;
                    }

                    if (isAnimating || isGestureLocked) {
                        event.preventDefault();
                        scheduleGestureRelease();

                        return;
                    }

                    activeIndex = getClosestPanelIndex(panels);

                    const direction = deltaY > 0 ? 1 : -1;
                    const movingBeforeFirst =
                        activeIndex === 0 && direction < 0;
                    const movingAfterFinal =
                        activeIndex === panels.length - 1 && direction > 0;

                    if (movingBeforeFirst || movingAfterFinal) {
                        accumulatedWheelDelta = 0;

                        return;
                    }

                    event.preventDefault();
                    scheduleGestureRelease();

                    accumulatedWheelDelta += deltaY;

                    if (
                        Math.abs(accumulatedWheelDelta) <
                        wheelActivationThreshold
                    ) {
                        return;
                    }

                    const targetIndex =
                        activeIndex +
                        (accumulatedWheelDelta > 0 ? 1 : -1);

                    accumulatedWheelDelta = 0;
                    isGestureLocked = true;

                    navigateToPanel(targetIndex, {
                        recordSnap: true,
                    });
                };

                window.addEventListener("wheel", handleWheel, {
                    passive: false,
                });

                cleanupCallbacks.push(() => {
                    window.removeEventListener("wheel", handleWheel);
                    document.documentElement.classList.remove(
                        "about-section-snap-active",
                    );
                });
            }

            /**
             * Refresh trigger geometry after layout-affecting resources settle.
             */
            const refreshLayout = ({ alignHash = false } = {}) => {
                if (disposed || isAnimating) {
                    return;
                }

                setNavigationState(root, "initializing");
                ScrollTrigger.refresh();

                const hashPanel = alignHash
                    ? getPanelFromHash(panels)
                    : null;

                if (hashPanel) {
                    const hashIndex = panels.indexOf(hashPanel);

                    window.scrollTo({
                        behavior: "auto",
                        left: 0,
                        top: getPanelScrollPosition(hashPanel),
                    });

                    activatePanel(hashIndex);
                    ScrollTrigger.update();
                } else {
                    activatePanel(getClosestPanelIndex(panels));
                }

                updateTriggerCount(root);
                setNavigationState(root, "ready");
            };

            const refreshFrame = window.requestAnimationFrame(() => {
                refreshLayout({
                    alignHash: true,
                });
            });

            const handleWindowLoad = () => {
                refreshLayout();
            };

            window.addEventListener("load", handleWindowLoad, {
                once: true,
            });

            document.fonts?.ready
                ?.then(() => {
                    refreshLayout();
                })
                .catch(() => {
                    setNavigationState(root, "ready");
                });

            cleanupCallbacks.push(() => {
                disposed = true;
                window.cancelAnimationFrame(refreshFrame);
                window.removeEventListener("load", handleWindowLoad);

                if (gestureReleaseTimer !== null) {
                    window.clearTimeout(gestureReleaseTimer);
                }

                activeTween?.kill();
            });

            return () => {
                cleanupCallbacks.reverse().forEach((callback) => callback());
                animationContext.revert();
            };
        },
    );

    /**
     * Remove every About-specific handler, trigger, tween, and state value.
     */
    const cleanup = () => {
        media.revert();

        document.documentElement.classList.remove(
            "about-section-navigation-active",
            "about-section-snap-active",
        );

        delete root.dataset.aboutSectionNavigation;
        delete root.dataset.aboutActiveSection;
        delete root.dataset.aboutMotion;
        delete root.dataset.aboutTriggerCount;
        delete root.dataset.aboutSnapCount;

        panels.forEach((panel) => {
            panel.removeAttribute("data-about-active");
            panel.removeAttribute("data-about-animation-state");
        });
    };

    if (import.meta.hot) {
        import.meta.hot.dispose(cleanup);
    }

    return cleanup;
}
