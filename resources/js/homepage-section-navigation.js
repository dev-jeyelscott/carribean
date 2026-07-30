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
const sectionRevealDuration = 0.9;
const sectionRevealStagger = 0.09;

/**
 * Return every valid top-level homepage panel in document order.
 */
function getPanels(root) {
    return [
        ...root.querySelectorAll(":scope > [data-home-panel]"),
    ].filter(
        (panel) => panel instanceof HTMLElement,
    );
}

/**
 * Mark one panel as the currently active homepage section.
 */
function setActivePanel(root, panels, activeIndex) {
    const activePanel = panels[activeIndex];

    if (!(activePanel instanceof HTMLElement)) {
        return;
    }

    panels.forEach((panel, index) => {
        panel.setAttribute(
            "data-home-active",
            index === activeIndex ? "true" : "false",
        );
    });

    root.dataset.homeActiveSection =
        activePanel.dataset.homeLabel ?? String(activeIndex);
}

/**
 * Expose navigation state for diagnostics and browser tests.
 */
function setNavigationState(root, state) {
    root.dataset.homeSectionNavigation = state;
}

/**
 * Record one completed section transition.
 */
function recordCompletedSnap(root) {
    const currentCount = Number.parseInt(
        root.dataset.homeSnapCount ?? "0",
        10,
    );

    root.dataset.homeSnapCount = String(currentCount + 1);
}

/**
 * Return the panel aligned closest to the viewport top.
 */
function getClosestPanelIndex(panels) {
    let closestIndex = 0;
    let closestDistance = Number.POSITIVE_INFINITY;

    panels.forEach((panel, index) => {
        const distance = Math.abs(
            panel.getBoundingClientRect().top,
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
 * Normalize browser wheel values into approximate CSS pixels.
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
 * Determine whether the viewport center remains inside the homepage pager.
 */
function isPagerActive(root) {
    const bounds = root.getBoundingClientRect();
    const viewportCenter = window.innerHeight / 2;

    return bounds.top <= viewportCenter
        && bounds.bottom > viewportCenter;
}

/**
 * Preserve native scrolling inside independently scrollable descendants.
 */
function hasScrollableAncestor(target, root, deltaY) {
    let element = target instanceof Element
        ? target
        : null;

    while (element && element !== root) {
        const styles = window.getComputedStyle(element);

        const allowsVerticalScrolling =
            styles.overflowY === "auto"
            || styles.overflowY === "scroll";

        if (
            allowsVerticalScrolling
            && element.scrollHeight > element.clientHeight
        ) {
            const canScrollUp =
                deltaY < 0
                && element.scrollTop > 0;

            const canScrollDown =
                deltaY > 0
                && element.scrollTop + element.clientHeight
                    < element.scrollHeight - 1;

            if (canScrollUp || canScrollDown) {
                return true;
            }
        }

        element = element.parentElement;
    }

    return false;
}

/**
 * Create a one-time coordinated content reveal for one homepage panel.
 */
function createPanelReveal(panel, immediate = false) {
    const targets = [
        ...panel.querySelectorAll("[data-home-reveal]"),
    ];

    if (targets.length === 0) {
        return () => {};
    }

    gsap.set(targets, {
        autoAlpha: 0,
        y: 34,
    });

    const timeline = gsap.timeline({
        paused: true,
        defaults: {
            duration: sectionRevealDuration,
            ease: "power3.out",
        },
    });

    timeline.to(targets, {
        autoAlpha: 1,
        stagger: sectionRevealStagger,
        y: 0,
    });

    let hasPlayed = false;

    /**
     * Reveal the panel once and keep its content visible afterward.
     */
    const play = () => {
        if (hasPlayed) {
            return;
        }

        hasPlayed = true;
        timeline.play(0);
    };

    if (immediate) {
        play();

        return () => {
            timeline.kill();
        };
    }

    const trigger = ScrollTrigger.create({
        id: `home-reveal-${panel.id}`,
        trigger: panel,
        start: "top 76%",
        end: "bottom 24%",
        invalidateOnRefresh: true,
        onEnter: play,
        onEnterBack: play,
    });

    return () => {
        trigger.kill();
        timeline.kill();
    };
}

/**
 * Add restrained image depth to the redesigned Story section.
 */
function initializeStoryDepth(
    root,
    {
        desktop,
        reducedMotion,
    },
) {
    const story = root.querySelector("#story");

    const image = story?.querySelector(
        "[data-home-story-media] img",
    );

    if (
        !desktop
        || reducedMotion
        || !(story instanceof HTMLElement)
        || !(image instanceof HTMLImageElement)
    ) {
        return () => {};
    }

    const tween = gsap.fromTo(
        image,
        {
            scale: 1.06,
            yPercent: -2,
        },
        {
            ease: "none",
            scale: 1.02,
            yPercent: 2,
            scrollTrigger: {
                id: "home-story-depth",
                trigger: story,
                start: "top bottom",
                end: "bottom top",
                scrub: true,
                invalidateOnRefresh: true,
            },
        },
    );

    return () => {
        tween.scrollTrigger?.kill();
        tween.kill();
    };
}

/**
 * Bind explicit homepage links such as the hero Scroll control.
 */
function bindScrollLinks(root, reducedMotion) {
    const cleanupCallbacks = [];

    root.querySelectorAll("[data-home-scroll-link]").forEach(
        (link) => {
            if (!(link instanceof HTMLAnchorElement)) {
                return;
            }

            const handleClick = (event) => {
                const selector = link.getAttribute("href");

                if (!selector?.startsWith("#")) {
                    return;
                }

                const target = document.querySelector(selector);

                if (!(target instanceof HTMLElement)) {
                    return;
                }

                event.preventDefault();

                if (reducedMotion) {
                    window.scrollTo({
                        behavior: "auto",
                        left: 0,
                        top: target.offsetTop,
                    });

                    return;
                }

                gsap.to(window, {
                    duration: sectionTransitionDuration,
                    ease: "power3.out",
                    overwrite: "auto",
                    scrollTo: {
                        autoKill: false,
                        y: target,
                    },
                });
            };

            link.addEventListener("click", handleClick);

            cleanupCallbacks.push(() => {
                link.removeEventListener(
                    "click",
                    handleClick,
                );
            });
        },
    );

    return () => {
        cleanupCallbacks.forEach(
            (callback) => callback(),
        );
    };
}

/**
 * Restore final readable states for reduced-motion visitors.
 */
function setReducedMotionState(root) {
    gsap.set(
        root.querySelectorAll(
            [
                "[data-home-reveal]",
                "[data-home-story-media] img",
            ].join(", "),
        ),
        {
            autoAlpha: 1,
            clearProps: "opacity,transform,visibility,willChange",
        },
    );
}

/**
 * Initialize the complete homepage navigation and reveal experience.
 */
export function initHomepageSectionNavigation(
    root = document.querySelector(
        "[data-home-section-pager]",
    ),
) {
    if (!(root instanceof HTMLElement)) {
        return () => {};
    }

    const panels = getPanels(root);

    if (panels.length === 0) {
        return () => {};
    }

    root.dataset.homeSnapCount = "0";

    setNavigationState(root, "initializing");

    const media = gsap.matchMedia();

    media.add(
        {
            desktop: desktopQuery,
            reducedMotion: reducedMotionQuery,
            shortViewport: shortViewportQuery,
        },
        (context) => {
            const {
                desktop = false,
                reducedMotion = false,
                shortViewport = false,
            } = context.conditions ?? {};

            const cleanupCallbacks = [];

            let disposed = false;
            let activeIndex = getClosestPanelIndex(panels);
            let activeTween = null;
            let isAnimating = false;
            let isGestureLocked = false;
            let wheelIsActive = false;
            let accumulatedWheelDelta = 0;
            let gestureReleaseTimer = null;

            /**
             * Synchronize active panel state before navigation begins.
             */
            const activatePanel = (index) => {
                activeIndex = Math.max(
                    0,
                    Math.min(index, panels.length - 1),
                );

                setActivePanel(
                    root,
                    panels,
                    activeIndex,
                );
            };

            if (reducedMotion) {
                setReducedMotionState(root);
            } else {
                panels.forEach((panel, index) => {
                    cleanupCallbacks.push(
                        createPanelReveal(
                            panel,
                            index === 0,
                        ),
                    );
                });
            }

            cleanupCallbacks.push(
                initializeStoryDepth(root, {
                    desktop,
                    reducedMotion,
                }),
            );

            cleanupCallbacks.push(
                bindScrollLinks(
                    root,
                    reducedMotion,
                ),
            );

            /*
             * Track the active panel independently from the navigation tween.
             */
            const trackingTriggers = panels.map(
                (panel, index) =>
                    ScrollTrigger.create({
                        id: `home-active-${panel.id}`,
                        trigger: panel,
                        start: "top 52%",
                        end: "bottom 48%",
                        invalidateOnRefresh: true,
                        onEnter: () => {
                            activatePanel(index);
                        },
                        onEnterBack: () => {
                            activatePanel(index);
                        },
                    }),
            );

            cleanupCallbacks.push(() => {
                trackingTriggers.forEach(
                    (trigger) => trigger.kill(),
                );
            });

            activatePanel(activeIndex);

            const canSnap =
                desktop
                && !reducedMotion
                && !shortViewport
                && panels.length > 1;

            if (canSnap) {
                document.documentElement.classList.add(
                    "home-section-snap-active",
                );

                /**
                 * Release one wheel gesture after its momentum settles.
                 */
                const scheduleGestureRelease = () => {
                    wheelIsActive = true;

                    if (gestureReleaseTimer !== null) {
                        window.clearTimeout(
                            gestureReleaseTimer,
                        );
                    }

                    gestureReleaseTimer =
                        window.setTimeout(() => {
                            wheelIsActive = false;
                            accumulatedWheelDelta = 0;
                            gestureReleaseTimer = null;

                            if (!isAnimating) {
                                isGestureLocked = false;
                            }
                        }, wheelGestureReleaseDelay);
                };

                /**
                 * Return navigation to a ready state after a tween settles.
                 */
                const settleNavigation = (
                    completed = false,
                ) => {
                    isAnimating = false;
                    activeTween = null;

                    if (completed) {
                        recordCompletedSnap(root);
                    }

                    setNavigationState(root, "ready");

                    ScrollTrigger.update();

                    if (!wheelIsActive) {
                        isGestureLocked = false;
                    }
                };

                /**
                 * Move to one exact adjacent homepage panel.
                 */
                const navigateToPanel = (targetIndex) => {
                    const targetPanel =
                        panels[targetIndex];

                    if (
                        !(targetPanel instanceof HTMLElement)
                    ) {
                        return;
                    }

                    activeTween?.kill();

                    isAnimating = true;
                    isGestureLocked = true;

                    activatePanel(targetIndex);
                    setNavigationState(root, "snapping");

                    activeTween = gsap.to(window, {
                        duration: sectionTransitionDuration,
                        ease: "power3.out",
                        overwrite: "auto",
                        scrollTo: {
                            autoKill: false,
                            y: targetPanel,
                        },
                        onComplete: () => {
                            settleNavigation(true);
                        },
                        onInterrupt: () => {
                            settleNavigation(false);
                        },
                    });
                };

                /**
                 * Convert one desktop wheel gesture into one adjacent section.
                 */
                const handleWheel = (event) => {
                    if (
                        event.defaultPrevented
                        || event.ctrlKey
                        || !isPagerActive(root)
                    ) {
                        return;
                    }

                    const deltaY =
                        normalizeWheelDelta(event);

                    if (
                        Math.abs(event.deltaX)
                            > Math.abs(deltaY)
                        || Math.abs(deltaY) < 0.5
                    ) {
                        return;
                    }

                    if (
                        hasScrollableAncestor(
                            event.target,
                            root,
                            deltaY,
                        )
                    ) {
                        return;
                    }

                    if (
                        isAnimating
                        || isGestureLocked
                    ) {
                        event.preventDefault();
                        scheduleGestureRelease();

                        return;
                    }

                    activeIndex =
                        getClosestPanelIndex(panels);

                    const direction =
                        deltaY > 0 ? 1 : -1;

                    const movingBeforeFirst =
                        activeIndex === 0
                        && direction < 0;

                    const movingAfterFinal =
                        activeIndex
                            === panels.length - 1
                        && direction > 0;

                    if (
                        movingBeforeFirst
                        || movingAfterFinal
                    ) {
                        accumulatedWheelDelta = 0;

                        return;
                    }

                    event.preventDefault();
                    scheduleGestureRelease();

                    accumulatedWheelDelta += deltaY;

                    if (
                        Math.abs(accumulatedWheelDelta)
                            < wheelActivationThreshold
                    ) {
                        return;
                    }

                    const targetIndex =
                        activeIndex
                        + (
                            accumulatedWheelDelta > 0
                                ? 1
                                : -1
                        );

                    accumulatedWheelDelta = 0;
                    isGestureLocked = true;

                    navigateToPanel(targetIndex);
                };

                window.addEventListener(
                    "wheel",
                    handleWheel,
                    {
                        passive: false,
                    },
                );

                cleanupCallbacks.push(() => {
                    window.removeEventListener(
                        "wheel",
                        handleWheel,
                    );

                    document.documentElement
                        .classList
                        .remove(
                            "home-section-snap-active",
                        );
                });
            }

            /**
             * Refresh panel and trigger geometry after layout resources settle.
             */
            const refreshLayout = () => {
                if (disposed || isAnimating) {
                    return;
                }

                setNavigationState(
                    root,
                    "initializing",
                );

                ScrollTrigger.refresh();

                activatePanel(
                    getClosestPanelIndex(panels),
                );

                setNavigationState(
                    root,
                    reducedMotion
                        ? "reduced"
                        : "ready",
                );
            };

            const refreshFrame =
                window.requestAnimationFrame(
                    refreshLayout,
                );

            const handleWindowLoad = () => {
                refreshLayout();
            };

            window.addEventListener(
                "load",
                handleWindowLoad,
                {
                    once: true,
                },
            );

            document.fonts?.ready
                ?.then(() => {
                    refreshLayout();
                })
                .catch(() => {
                    setNavigationState(
                        root,
                        reducedMotion
                            ? "reduced"
                            : "ready",
                    );
                });

            cleanupCallbacks.push(() => {
                disposed = true;

                window.cancelAnimationFrame(
                    refreshFrame,
                );

                window.removeEventListener(
                    "load",
                    handleWindowLoad,
                );

                if (
                    gestureReleaseTimer !== null
                ) {
                    window.clearTimeout(
                        gestureReleaseTimer,
                    );
                }

                activeTween?.kill();
            });

            setNavigationState(
                root,
                reducedMotion
                    ? "reduced"
                    : "ready",
            );

            return () => {
                cleanupCallbacks
                    .reverse()
                    .forEach(
                        (callback) => callback(),
                    );
            };
        },
        root,
    );

    /**
     * Remove every responsive controller and diagnostic state.
     */
    const cleanup = () => {
        media.revert();

        document.documentElement.classList.remove(
            "home-section-snap-active",
        );

        delete root.dataset.homeSectionNavigation;
        delete root.dataset.homeSnapCount;
        delete root.dataset.homeActiveSection;

        panels.forEach((panel) => {
            panel.removeAttribute(
                "data-home-active",
            );
        });
    };

    if (import.meta.hot) {
        import.meta.hot.dispose(cleanup);
    }

    return cleanup;
}
