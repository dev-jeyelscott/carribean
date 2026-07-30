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
 * Return the compact fixed-header height used by section destinations.
 */
function getHeaderOffset() {
    return window.matchMedia("(max-width: 1023px)").matches
        ? 76
        : 80;
}

/**
 * Safely extract a section ID from one same-page hash.
 */
function getSectionId(href) {
    if (typeof href !== "string" || !href.startsWith("#")) {
        return null;
    }

    try {
        return decodeURIComponent(href.slice(1));
    } catch {
        return href.slice(1);
    }
}

/**
 * Resolve every pager link and its matching page section.
 */
function getEntries(root) {
    return [...root.querySelectorAll("[data-section-pager-link]")]
        .map((link, index) => {
            if (!(link instanceof HTMLAnchorElement)) {
                return null;
            }

            const sectionId = getSectionId(
                link.getAttribute("href"),
            );

            const section = sectionId
                ? document.getElementById(sectionId)
                : null;

            if (!(section instanceof HTMLElement)) {
                return null;
            }

            return {
                index,
                link,
                section,
            };
        })
        .filter((entry) => entry !== null);
}

/**
 * Return the page root containing both the pager and target sections.
 */
function getPageRoot(root, context) {
    return root.closest(`[data-${context}-page]`)
        ?? root.parentElement
        ?? document.body;
}

/**
 * Return the expected viewport top for one active section.
 */
function getExpectedTop(entry) {
    return entry.index === 0
        ? 0
        : getHeaderOffset();
}

/**
 * Return the exact document destination for one section.
 */
function getSectionScrollPosition(entry) {
    const sectionTop =
        window.scrollY
        + entry.section.getBoundingClientRect().top;

    return Math.max(
        0,
        Math.round(sectionTop - getExpectedTop(entry)),
    );
}

/**
 * Find the section currently aligned closest to its expected viewport top.
 */
function getClosestEntryIndex(entries) {
    let closestIndex = 0;
    let closestDistance = Number.POSITIVE_INFINITY;

    entries.forEach((entry, index) => {
        const distance = Math.abs(
            entry.section.getBoundingClientRect().top
            - getExpectedTop(entry),
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
 * Determine whether the viewport center is inside the pager-controlled region.
 */
function isPagerRegionActive(entries) {
    const firstSection = entries[0]?.section;
    const lastSection = entries.at(-1)?.section;

    if (
        !(firstSection instanceof HTMLElement)
        || !(lastSection instanceof HTMLElement)
    ) {
        return false;
    }

    const viewportCenter = window.innerHeight / 2;

    return firstSection.getBoundingClientRect().top
        <= viewportCenter
        && lastSection.getBoundingClientRect().bottom
        > viewportCenter;
}

/**
 * Normalize browser wheel values reported as pixels, lines, or pages.
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
 * Determine whether one scrollable element can continue in this direction.
 */
function canElementScroll(element, deltaY) {
    const styles = window.getComputedStyle(element);

    const allowsVerticalScrolling =
        styles.overflowY === "auto"
        || styles.overflowY === "scroll";

    if (
        !allowsVerticalScrolling
        || element.scrollHeight <= element.clientHeight
    ) {
        return false;
    }

    const canScrollUp =
        deltaY < 0
        && element.scrollTop > 0;

    const canScrollDown =
        deltaY > 0
        && element.scrollTop + element.clientHeight
            < element.scrollHeight - 1;

    return canScrollUp || canScrollDown;
}

/**
 * Preserve native scrolling inside the Gallery collection and other nested
 * scrolling regions.
 */
function hasScrollableAncestor(target, deltaY) {
    let element = target instanceof Element
        ? target
        : null;

    while (
        element
        && element !== document.body
        && element !== document.documentElement
    ) {
        if (canElementScroll(element, deltaY)) {
            return true;
        }

        element = element.parentElement;
    }

    return false;
}

/**
 * Mark one entry and section active and notify the associated page controller.
 */
function setActiveEntry(
    root,
    entries,
    activeIndex,
    context,
) {
    const activeEntry = entries[activeIndex];

    if (!activeEntry) {
        return;
    }

    const previousSectionId =
        root.dataset.sectionPagerActive;

    entries.forEach((entry, index) => {
        const isActive = index === activeIndex;

        entry.link.setAttribute(
            "aria-current",
            isActive ? "location" : "false",
        );

        entry.section.setAttribute(
            "data-section-active",
            isActive ? "true" : "false",
        );

        if (context === "gallery") {
            entry.section.setAttribute(
                "data-gallery-active",
                isActive ? "true" : "false",
            );
        }
    });

    root.dataset.sectionPagerActive =
        activeEntry.section.id;

    if (
        previousSectionId
        === activeEntry.section.id
    ) {
        return;
    }

    root.dispatchEvent(
        new CustomEvent("section-pager:activate", {
            bubbles: true,
            detail: {
                context,
                index: activeIndex,
                sectionId: activeEntry.section.id,
            },
        }),
    );
}

/**
 * Expose the current navigation lifecycle for diagnostics and tests.
 */
function setNavigationState(root, state) {
    root.dataset.sectionPagerState = state;
}

/**
 * Increment the completed desktop section-transition counter.
 */
function recordCompletedSnap(root) {
    const currentCount = Number.parseInt(
        root.dataset.sectionPagerSnapCount ?? "0",
        10,
    );

    root.dataset.sectionPagerSnapCount =
        String(currentCount + 1);
}

/**
 * Count the shared pager's active ScrollTriggers.
 */
function updateTriggerCount(root, context) {
    const triggerCount = ScrollTrigger
        .getAll()
        .filter((trigger) => {
            const id = trigger.vars.id;

            return typeof id === "string"
                && id.startsWith(
                    `section-pager-${context}-`,
                );
        })
        .length;

    root.dataset.sectionPagerTriggerCount =
        String(triggerCount);
}

/**
 * Resolve a section entry from the current or supplied URL hash.
 */
function getEntryFromHash(
    entries,
    hash = window.location.hash,
) {
    const sectionId = getSectionId(hash);

    return entries.find(
        (entry) => entry.section.id === sectionId,
    ) ?? null;
}

/**
 * Initialize accessible navigation, full-screen transitions, and active-section
 * ScrollTriggers.
 */
export function initSectionPager(
    root = document.querySelector(
        '[data-section-pager][data-section-pager-enhancer="shared"]',
    ),
) {
    if (!(root instanceof HTMLElement)) {
        return () => {};
    }

    const entries = getEntries(root);

    if (entries.length === 0) {
        setNavigationState(root, "unavailable");

        return () => {};
    }

    const context =
        root.dataset.sectionPagerContext ?? "page";

    const pageRoot = getPageRoot(root, context);

    const snapRequested =
        root.dataset.sectionPagerSnap === "true";

    const showMarkers = new URLSearchParams(
        window.location.search,
    ).has("debug-scroll");

    document.documentElement.classList.add(
        "section-pager-navigation-active",
    );

    root.dataset.sectionPagerSnapCount = "0";

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

            let disposed = false;
            let activeIndex =
                getClosestEntryIndex(entries);

            let activeTween = null;
            let isAnimating = false;
            let isGestureLocked = false;
            let wheelIsActive = false;
            let accumulatedWheelDelta = 0;
            let gestureReleaseTimer = null;

            /**
             * Activate one section before or during navigation.
             */
            const activateEntry = (index) => {
                activeIndex = Math.max(
                    0,
                    Math.min(index, entries.length - 1),
                );

                setActiveEntry(
                    root,
                    entries,
                    activeIndex,
                    context,
                );
            };

            /**
             * Restore navigation readiness after a scroll tween settles.
             */
            const settleNavigation = (
                completedSnap = false,
            ) => {
                isAnimating = false;
                activeTween = null;

                if (completedSnap) {
                    recordCompletedSnap(root);
                }

                setNavigationState(
                    root,
                    reducedMotion ? "reduced" : "ready",
                );

                ScrollTrigger.update();
                updateTriggerCount(root, context);

                if (!wheelIsActive) {
                    isGestureLocked = false;
                }
            };

            /**
             * Move the document to one exact panel destination.
             */
            const navigateToEntry = (
                targetIndex,
                {
                    updateHistory = false,
                    recordSnap = false,
                } = {},
            ) => {
                const entry = entries[targetIndex];

                if (!entry) {
                    return;
                }

                activeTween?.kill();
                activeTween = null;

                if (updateHistory) {
                    const targetHash =
                        `#${entry.section.id}`;

                    if (
                        window.location.hash
                        !== targetHash
                    ) {
                        window.history.pushState(
                            null,
                            "",
                            targetHash,
                        );
                    }
                }

                activateEntry(targetIndex);

                const destination =
                    getSectionScrollPosition(entry);

                setNavigationState(
                    root,
                    "navigating",
                );

                if (reducedMotion) {
                    window.scrollTo({
                        behavior: "auto",
                        left: 0,
                        top: destination,
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
                        y: destination,
                    },
                    onComplete: () => {
                        settleNavigation(recordSnap);
                    },
                    onInterrupt: () => {
                        settleNavigation(false);
                    },
                });
            };

            /*
             * Track the active panel independently from the animation timelines.
             */
            const trackingTriggers = entries.map(
                (entry, index) =>
                    ScrollTrigger.create({
                        id:
                            `section-pager-${context}-`
                            + entry.section.id,
                        trigger: entry.section,
                        start: "top 52%",
                        end: "bottom 48%",
                        invalidateOnRefresh: true,
                        markers: showMarkers,
                        onEnter: () => {
                            activateEntry(index);
                        },
                        onEnterBack: () => {
                            activateEntry(index);
                        },
                    }),
            );

            cleanupCallbacks.push(() => {
                trackingTriggers.forEach(
                    (trigger) => trigger.kill(),
                );
            });

            /*
             * Bind the pager and other same-page Gallery links to the same exact
             * navigation function.
             */
            pageRoot
                .querySelectorAll('a[href^="#"]')
                .forEach((link) => {
                    const targetEntry =
                        getEntryFromHash(
                            entries,
                            link.getAttribute("href") ?? "",
                        );

                    if (!targetEntry) {
                        return;
                    }

                    const targetIndex =
                        entries.indexOf(targetEntry);

                    const handleClick = (event) => {
                        event.preventDefault();

                        navigateToEntry(targetIndex, {
                            updateHistory: true,
                        });
                    };

                    link.addEventListener(
                        "click",
                        handleClick,
                    );

                    cleanupCallbacks.push(() => {
                        link.removeEventListener(
                            "click",
                            handleClick,
                        );
                    });
                });

            /**
             * Restore the correct panel when browser history changes.
             */
            const handlePopState = () => {
                const requestedEntry =
                    getEntryFromHash(entries)
                    ?? entries[0];

                navigateToEntry(
                    entries.indexOf(requestedEntry),
                );
            };

            window.addEventListener(
                "popstate",
                handlePopState,
            );

            cleanupCallbacks.push(() => {
                window.removeEventListener(
                    "popstate",
                    handlePopState,
                );
            });

            const canSnap =
                snapRequested
                && desktop
                && !reducedMotion
                && !shortViewport
                && entries.length > 1;

            if (canSnap) {
                document.documentElement.classList.add(
                    "section-pager-snap-active",
                );

                /**
                 * Release one wheel gesture after its momentum finishes.
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
                 * Convert one desktop wheel gesture into one adjacent section.
                 */
                const handleWheel = (event) => {
                    if (
                        event.defaultPrevented
                        || event.ctrlKey
                        || document.documentElement
                            .classList
                            .contains("gallery-dialog-open")
                        || !isPagerRegionActive(entries)
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
                        getClosestEntryIndex(entries);

                    const direction =
                        deltaY > 0 ? 1 : -1;

                    const movingBeforeFirst =
                        activeIndex === 0
                        && direction < 0;

                    const movingAfterFinal =
                        activeIndex
                            === entries.length - 1
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

                    navigateToEntry(targetIndex, {
                        recordSnap: true,
                    });
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
                            "section-pager-snap-active",
                        );
                });
            }

            /**
             * Recalculate all trigger positions after layout resources settle.
             */
            const refreshLayout = ({
                alignHash = false,
            } = {}) => {
                if (disposed || isAnimating) {
                    return;
                }

                setNavigationState(
                    root,
                    "initializing",
                );

                ScrollTrigger.refresh();

                const requestedEntry = alignHash
                    ? getEntryFromHash(entries)
                    : null;

                if (requestedEntry) {
                    const requestedIndex =
                        entries.indexOf(requestedEntry);

                    window.scrollTo({
                        behavior: "auto",
                        left: 0,
                        top:
                            getSectionScrollPosition(
                                requestedEntry,
                            ),
                    });

                    activateEntry(requestedIndex);
                    ScrollTrigger.update();
                } else {
                    activateEntry(
                        getClosestEntryIndex(entries),
                    );
                }

                updateTriggerCount(root, context);

                setNavigationState(
                    root,
                    reducedMotion ? "reduced" : "ready",
                );
            };

            const refreshFrame =
                window.requestAnimationFrame(() => {
                    refreshLayout({
                        alignHash: true,
                    });
                });

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

            return () => {
                cleanupCallbacks
                    .reverse()
                    .forEach(
                        (callback) => callback(),
                    );
            };
        },
    );

    /**
     * Remove all state, triggers, listeners, and section attributes.
     */
    const cleanup = () => {
        media.revert();

        document.documentElement.classList.remove(
            "section-pager-navigation-active",
            "section-pager-snap-active",
        );

        delete root.dataset.sectionPagerActive;
        delete root.dataset.sectionPagerTriggerCount;
        delete root.dataset.sectionPagerSnapCount;
        delete root.dataset.sectionPagerState;

        entries.forEach((entry) => {
            entry.section.removeAttribute(
                "data-section-active",
            );

            entry.section.removeAttribute(
                "data-gallery-active",
            );
        });
    };

    if (import.meta.hot) {
        import.meta.hot.dispose(cleanup);
    }

    return cleanup;
}
