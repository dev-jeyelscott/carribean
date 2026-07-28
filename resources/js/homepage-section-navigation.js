import gsap from "gsap";
import { ScrollToPlugin } from "gsap/ScrollToPlugin";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollToPlugin, ScrollTrigger);

const desktopQuery = "(min-width: 1024px) and (pointer: fine)";
const reducedMotionQuery = "(prefers-reduced-motion: reduce)";
const shortViewportQuery = "(max-height: 719px)";

/*
 * Desktop wheel-navigation tuning.
 *
 * A low activation threshold keeps slight mouse-wheel movements responsive.
 * The release delay groups a burst of wheel events into one deliberate gesture.
 */
const wheelActivationThreshold = 6;
const wheelGestureReleaseDelay = 120;
const wheelTransitionDuration = 0.42;

/**
 * Mark one homepage panel as the currently active section.
 */
function setActivePanel(root, panels, activeIndex) {
    panels.forEach((panel, index) => {
        panel.setAttribute(
            "data-home-active",
            index === activeIndex ? "true" : "false",
        );
    });

    root.dataset.homeActiveSection =
        panels[activeIndex]?.dataset.homeLabel ?? String(activeIndex);
}

/**
 * Expose the current section-navigation state for browser testing and
 * diagnostics without coupling tests to arbitrary animation delays.
 */
function setNavigationState(root, state) {
    root.dataset.homeSectionNavigation = state;
}

/**
 * Increment the completed-transition counter after a section movement settles.
 */
function recordCompletedSnap(root) {
    const currentCount = Number.parseInt(root.dataset.homeSnapCount ?? "0", 10);

    root.dataset.homeSnapCount = String(currentCount + 1);
}

/**
 * Return the panel whose top edge is currently closest to the viewport top.
 *
 * This handles page restoration, anchor navigation, and browser back-forward
 * cache restoration without assuming that the homepage always starts at panel 0.
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
 * Normalize wheel movement into approximate CSS pixels.
 *
 * Most browsers report pixel values, but traditional mouse devices may report
 * line or page units instead.
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
 * Determine whether the viewport is currently inside the homepage pager.
 *
 * The viewport center is used so native scrolling is restored after the pager
 * moves above the page and the visitor reaches footer content.
 */
function isPagerActive(root) {
    const bounds = root.getBoundingClientRect();
    const viewportCenter = window.innerHeight / 2;

    return bounds.top <= viewportCenter && bounds.bottom > viewportCenter;
}

/**
 * Determine whether a nested element should retain its native vertical scroll.
 *
 * This prevents the homepage pager from blocking an independently scrollable
 * element such as a modal, menu, or intentionally scrollable content region.
 */
function hasScrollableAncestor(target, root, deltaY) {
    let element = target instanceof Element ? target : null;

    while (element && element !== root) {
        const styles = window.getComputedStyle(element);
        const allowsScrolling =
            styles.overflowY === "auto" || styles.overflowY === "scroll";

        if (allowsScrolling && element.scrollHeight > element.clientHeight) {
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
 * Create a one-time content reveal for one homepage panel.
 *
 * Content remains visible before JavaScript initializes. GSAP applies the
 * hidden starting state only after this module successfully loads.
 */
function createPanelReveal(panel, immediate = false) {
    const targets = [...panel.querySelectorAll("[data-home-reveal]")];

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
        stagger: 0.1,
        y: 0,
    });

    let hasPlayed = false;

    /**
     * Play the reveal once so returning to a previous panel does not repeatedly
     * hide and reanimate readable content.
     */
    const play = () => {
        if (hasPlayed) {
            return;
        }

        hasPlayed = true;
        timeline.play();
    };

    if (immediate) {
        play();

        return () => {
            timeline.kill();
        };
    }

    const trigger = ScrollTrigger.create({
        trigger: panel,
        start: "top 72%",
        end: "bottom 28%",
        onEnter: play,
        onEnterBack: play,
    });

    return () => {
        trigger.kill();
        timeline.kill();
    };
}

/**
 * Bind explicit in-page links such as the hero's Scroll control.
 */
function bindScrollLinks(root, reducedMotion) {
    const cleanup = [];

    root.querySelectorAll("[data-home-scroll-link]").forEach((link) => {
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
                    top: target.offsetTop,
                    behavior: "auto",
                });

                return;
            }

            gsap.to(window, {
                duration: 0.5,
                ease: "power3.out",
                overwrite: "auto",
                scrollTo: {
                    y: target,
                    autoKill: true,
                },
            });
        };

        link.addEventListener("click", handleClick);

        cleanup.push(() => {
            link.removeEventListener("click", handleClick);
        });
    });

    return () => {
        cleanup.forEach((callback) => callback());
    };
}

/**
 * Initialize full-screen homepage panel navigation.
 *
 * Desktop:
 * - The first meaningful wheel movement responds immediately.
 * - One complete wheel or trackpad gesture moves exactly one panel.
 * - Momentum events are consumed instead of queued.
 * - Native scrolling is restored above the first and below the final panel.
 *
 * Mobile, short viewports, and reduced-motion environments retain native
 * document scrolling without forced section navigation.
 */
export function initHomepageSectionNavigation(
    root = document.querySelector("[data-home-section-pager]"),
) {
    if (!(root instanceof HTMLElement)) {
        return () => {};
    }

    const panels = [
        ...root.querySelectorAll(":scope > [data-home-panel]"),
    ].filter((panel) => panel instanceof HTMLElement);

    if (panels.length === 0) {
        return () => {};
    }

    setNavigationState(root, "initializing");
    root.dataset.homeSnapCount = "0";

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
            } = context.conditions;

            const cleanup = [];
            const revealTargets = root.querySelectorAll("[data-home-reveal]");

            let activeIndex = getClosestPanelIndex(panels);
            let isAnimating = false;
            let isGestureLocked = false;
            let wheelIsActive = false;
            let accumulatedWheelDelta = 0;
            let gestureReleaseTimer = null;
            let activeTween = null;

            /**
             * Update both the internal navigation index and public diagnostics.
             */
            const updateActivePanel = (index) => {
                activeIndex = Math.max(
                    0,
                    Math.min(index, panels.length - 1),
                );

                setActivePanel(root, panels, activeIndex);
            };

            setNavigationState(root, "initializing");

            if (reducedMotion) {
                gsap.set(revealTargets, {
                    autoAlpha: 1,
                    clearProps: "transform",
                });
            } else {
                panels.forEach((panel, index) => {
                    cleanup.push(createPanelReveal(panel, index === 0));
                });
            }

            cleanup.push(bindScrollLinks(root, reducedMotion));

            /*
             * ScrollTrigger still observes the visible panel for diagnostics,
             * direct anchor navigation, native boundary scrolling, and browser
             * restoration. It no longer performs delayed snapping.
             */
            const activeTriggers = panels.map((panel, index) =>
                ScrollTrigger.create({
                    trigger: panel,
                    start: "top 52%",
                    end: "bottom 48%",
                    onEnter: () => updateActivePanel(index),
                    onEnterBack: () => updateActivePanel(index),
                }),
            );

            cleanup.push(() => {
                activeTriggers.forEach((trigger) => trigger.kill());
            });

            updateActivePanel(activeIndex);

            if (
                desktop &&
                !reducedMotion &&
                !shortViewport &&
                panels.length > 1
            ) {
                document.documentElement.classList.add(
                    "home-section-snap-active",
                );

                /**
                 * Release the current gesture only after wheel activity has
                 * stopped and the active section transition has completed.
                 *
                 * Fast wheels and trackpads emit many momentum events. Keeping
                 * the gesture locked prevents those events from becoming queued
                 * section transitions.
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
                 * Restore navigation state when a transition completes or is
                 * interrupted by explicit programmatic navigation.
                 */
                const settleNavigation = (completed) => {
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
                 * Animate directly to one adjacent panel.
                 *
                 * The active index is updated before the animation begins so
                 * momentum events cannot select another panel during movement.
                 */
                const navigateToPanel = (targetIndex) => {
                    const targetPanel = panels[targetIndex];

                    if (!(targetPanel instanceof HTMLElement)) {
                        return;
                    }

                    isAnimating = true;
                    isGestureLocked = true;

                    updateActivePanel(targetIndex);
                    setNavigationState(root, "snapping");

                    activeTween = gsap.to(window, {
                        duration: wheelTransitionDuration,
                        ease: "power3.out",
                        overwrite: "auto",
                        scrollTo: {
                            y: targetPanel,
                            autoKill: false,
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
                 * Convert desktop wheel input into immediate, sequential
                 * one-panel navigation.
                 */
                const handleWheel = (event) => {
                    if (
                        event.defaultPrevented ||
                        event.ctrlKey ||
                        !isPagerActive(root)
                    ) {
                        return;
                    }

                    const deltaY = normalizeWheelDelta(event);

                    /*
                     * Ignore primarily horizontal trackpad gestures and
                     * negligible browser noise.
                     */
                    if (
                        Math.abs(event.deltaX) > Math.abs(deltaY) ||
                        Math.abs(deltaY) < 0.5
                    ) {
                        return;
                    }

                    /*
                     * Preserve native scrolling for independently scrollable
                     * content nested inside a homepage panel.
                     */
                    if (hasScrollableAncestor(event.target, root, deltaY)) {
                        return;
                    }

                    /*
                     * While a transition or its originating gesture remains
                     * active, consume every residual momentum event.
                     */
                    if (isAnimating || isGestureLocked) {
                        event.preventDefault();
                        scheduleGestureRelease();

                        return;
                    }

                    const direction = deltaY > 0 ? 1 : -1;
                    const isBeforeFirstPanel =
                        activeIndex === 0 && direction < 0;
                    const isAfterFinalPanel =
                        activeIndex === panels.length - 1 && direction > 0;

                    /*
                     * Do not trap the visitor at either pager boundary.
                     *
                     * Scrolling down from the final panel reaches the footer.
                     * Scrolling up from the first panel retains normal browser
                     * behavior.
                     */
                    if (isBeforeFirstPanel || isAfterFinalPanel) {
                        accumulatedWheelDelta = 0;

                        return;
                    }

                    /*
                     * Prevent native movement immediately so the page never
                     * drifts between sections while the gesture is evaluated.
                     */
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

                    navigateToPanel(targetIndex);
                };

                window.addEventListener("wheel", handleWheel, {
                    passive: false,
                });

                cleanup.push(() => {
                    window.removeEventListener("wheel", handleWheel);

                    if (gestureReleaseTimer !== null) {
                        window.clearTimeout(gestureReleaseTimer);
                    }

                    activeTween?.kill();

                    document.documentElement.classList.remove(
                        "home-section-snap-active",
                    );
                });
            }

            /*
             * Calculate panel positions before exposing the ready state.
             * This prevents the first real or Playwright wheel gesture from
             * arriving before ScrollTrigger knows the section boundaries.
             */
            ScrollTrigger.refresh();
            updateActivePanel(getClosestPanelIndex(panels));
            setNavigationState(root, "ready");

            /**
             * Recalculate section positions after load or font changes.
             */
            const refresh = () => {
                if (isAnimating) {
                    return;
                }

                setNavigationState(root, "initializing");
                ScrollTrigger.refresh();
                updateActivePanel(getClosestPanelIndex(panels));
                setNavigationState(root, "ready");
            };

            window.addEventListener("load", refresh, {
                once: true,
            });

            cleanup.push(() => {
                window.removeEventListener("load", refresh);
            });

            document.fonts?.ready?.then(refresh).catch(() => {
                /*
                 * Font loading must never prevent homepage interaction.
                 */
                setNavigationState(root, "ready");
            });

            return () => {
                cleanup.reverse().forEach((callback) => callback());
            };
        },
    );

    /**
     * Remove every media-query, listener, tween, and ScrollTrigger resource
     * created by this module.
     */
    const cleanup = () => {
        media.revert();

        document.documentElement.classList.remove(
            "home-section-snap-active",
        );

        delete root.dataset.homeSectionNavigation;
        delete root.dataset.homeSnapCount;
        delete root.dataset.homeActiveSection;
    };

    if (import.meta.hot) {
        import.meta.hot.dispose(cleanup);
    }

    return cleanup;
}
