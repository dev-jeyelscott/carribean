import gsap from "gsap";
import { ScrollToPlugin } from "gsap/ScrollToPlugin";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollToPlugin, ScrollTrigger);

const desktopQuery =
    "(min-width: 1024px) and (pointer: fine)";
const reducedMotionQuery =
    "(prefers-reduced-motion: reduce)";
const shortViewportQuery =
    "(max-height: 719px)";

const wheelActivationThreshold = 8;
const wheelGestureReleaseDelay = 140;
const sectionTransitionDuration = 0.48;

/**
 * Return the current fixed public-header height.
 */
function headerOffset() {
    const header = document.querySelector(
        "[data-public-header-shell] > header",
    );

    return header instanceof HTMLElement
        ? header.getBoundingClientRect().height
        : 88;
}

/**
 * Return every menu category section.
 */
function categorySections(root) {
    return [
        ...root.querySelectorAll("[data-menu-section]"),
    ].filter((section) => section instanceof HTMLElement);
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
 * Return the category positioned closest to the fixed-header offset.
 */
function closestCategoryIndex(sections) {
    const expectedTop = headerOffset() + 16;

    let closestIndex = 0;
    let closestDistance = Number.POSITIVE_INFINITY;

    sections.forEach((section, index) => {
        const distance = Math.abs(
            section.getBoundingClientRect().top
                - expectedTop,
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
 * Determine whether the viewport is inside the menu category region.
 *
 * Native document scrolling remains available in the menu hero, closing
 * section, and footer.
 */
function categoryRegionIsActive(sections) {
    const firstSection = sections[0];
    const finalSection = sections.at(-1);

    if (!firstSection || !finalSection) {
        return false;
    }

    const availableViewport =
        window.innerHeight - headerOffset();

    const activationLine =
        headerOffset()
        + Math.max(120, availableViewport / 2);

    const firstBounds =
        firstSection.getBoundingClientRect();

    const finalBounds =
        finalSection.getBoundingClientRect();

    return (
        firstBounds.top <= activationLine
        && finalBounds.bottom > activationLine
    );
}

/**
 * Preserve native scrolling inside independently scrollable UI.
 *
 * This is required for the product modal and the category sidebar when its
 * content exceeds the available viewport height.
 */
function hasScrollableAncestor(target, root, deltaY) {
    let element =
        target instanceof Element
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
                && element.scrollTop
                    + element.clientHeight
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
 * Determine whether the reusable native product dialog is currently open.
 */
function productModalIsOpen(root) {
    const dialog = root.querySelector(
        "[data-product-modal]",
    );

    return (
        dialog instanceof HTMLDialogElement
        && dialog.open
    );
}

/**
 * Initialize direct desktop category navigation and modal scroll recovery.
 *
 * Desktop mouse-wheel and trackpad gestures move exactly one category.
 * Mobile, touch, short viewports, and reduced-motion environments retain
 * native document scrolling.
 */
export function initMenuCategoryScroll(
    root = document.querySelector("[data-menu-page]"),
) {
    if (!(root instanceof HTMLElement)) {
        return () => {};
    }

    const sections = categorySections(root);
    const dialog = root.querySelector(
        "[data-product-modal]",
    );

    const media = gsap.matchMedia();

    let modalScrollPosition = null;
    let modalRefreshFrame = null;

    root.dataset.menuCategoryNavigation =
        "initializing";

    root.dataset.menuCategorySnapCount = "0";

    /**
     * Remember the underlying page position before the dialog takes focus.
     */
    const handleModalOpen = () => {
        modalScrollPosition = window.scrollY;
    };

    /**
     * Guarantee that the page is unlocked after every native dialog close.
     *
     * This also repairs ScrollTrigger measurements after the browser removes
     * the dialog from its top layer.
     */
    const handleNativeDialogClose = () => {
        document.documentElement.classList.remove(
            "menu-modal-open",
        );

        if (modalRefreshFrame !== null) {
            window.cancelAnimationFrame(
                modalRefreshFrame,
            );
        }

        const restorePosition =
            modalScrollPosition;

        modalScrollPosition = null;

        modalRefreshFrame =
            window.requestAnimationFrame(() => {
                modalRefreshFrame = null;

                if (restorePosition !== null) {
                    window.scrollTo({
                        behavior: "auto",
                        left: 0,
                        top: restorePosition,
                    });
                }

                ScrollTrigger.refresh(true);
                ScrollTrigger.update();
            });
    };

    window.addEventListener(
        "product-modal-open",
        handleModalOpen,
    );

    if (dialog instanceof HTMLDialogElement) {
        dialog.addEventListener(
            "close",
            handleNativeDialogClose,
        );
    }

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

            const canSnap =
                desktop
                && !reducedMotion
                && !shortViewport
                && sections.length > 1;

            if (!canSnap) {
                root.dataset.menuCategoryNavigation =
                    "native";

                return () => {};
            }

            let activeIndex =
                closestCategoryIndex(sections);

            let isAnimating = false;
            let isGestureLocked = false;
            let wheelIsActive = false;
            let accumulatedWheelDelta = 0;
            let gestureReleaseTimer = null;
            let activeTween = null;

            document.documentElement.classList.add(
                "menu-category-snap-active",
            );

            /**
             * Record a completed movement for browser regression tests.
             */
            const recordCompletedSnap = () => {
                const currentCount =
                    Number.parseInt(
                        root.dataset
                            .menuCategorySnapCount
                            ?? "0",
                        10,
                    );

                root.dataset.menuCategorySnapCount =
                    String(currentCount + 1);
            };

            /**
             * Release momentum after the wheel gesture settles.
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
             * Return the navigation system to its ready state.
             */
            const settleNavigation = (completed) => {
                isAnimating = false;
                activeTween = null;

                if (completed) {
                    recordCompletedSnap();
                }

                root.dataset.menuCategoryNavigation =
                    "ready";

                ScrollTrigger.update();

                if (!wheelIsActive) {
                    isGestureLocked = false;
                }
            };

            /**
             * Animate to exactly one category section.
             */
            const navigateToCategory = (
                targetIndex,
            ) => {
                const target =
                    sections[targetIndex];

                if (!(target instanceof HTMLElement)) {
                    return;
                }

                activeIndex = targetIndex;
                isAnimating = true;
                isGestureLocked = true;

                root.dataset.menuCategoryNavigation =
                    "snapping";

                activeTween = gsap.to(window, {
                    duration:
                        sectionTransitionDuration,
                    ease: "power3.out",
                    overwrite: "auto",
                    scrollTo: {
                        autoKill: false,
                        offsetY:
                            headerOffset() + 16,
                        y: target,
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
             * Convert one vertical wheel gesture into one adjacent category.
             */
            const handleWheel = (event) => {
                if (
                    event.defaultPrevented
                    || event.ctrlKey
                    || productModalIsOpen(root)
                    || !categoryRegionIsActive(
                        sections,
                    )
                ) {
                    return;
                }

                const deltaY =
                    normalizeWheelDelta(event);

                /*
                 * Horizontal trackpad gestures remain available for the menu
                 * item carousels.
                 */
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

                /*
                 * Consume momentum events while one transition is active.
                 */
                if (
                    isAnimating
                    || isGestureLocked
                ) {
                    event.preventDefault();
                    scheduleGestureRelease();

                    return;
                }

                activeIndex =
                    closestCategoryIndex(sections);

                const direction =
                    deltaY > 0 ? 1 : -1;

                const movingBeforeFirst =
                    activeIndex === 0
                    && direction < 0;

                const movingAfterFinal =
                    activeIndex
                        === sections.length - 1
                    && direction > 0;

                /*
                 * Never trap the visitor at either end of the menu.
                 */
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
                    Math.abs(
                        accumulatedWheelDelta,
                    )
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

                navigateToCategory(targetIndex);
            };

            window.addEventListener(
                "wheel",
                handleWheel,
                {
                    passive: false,
                },
            );

            ScrollTrigger.refresh();
            activeIndex =
                closestCategoryIndex(sections);

            root.dataset.menuCategoryNavigation =
                "ready";

            return () => {
                window.removeEventListener(
                    "wheel",
                    handleWheel,
                );

                if (
                    gestureReleaseTimer !== null
                ) {
                    window.clearTimeout(
                        gestureReleaseTimer,
                    );
                }

                activeTween?.kill();

                document.documentElement.classList
                    .remove(
                        "menu-category-snap-active",
                    );
            };
        },
    );

    /**
     * Remove every listener and media-query resource owned by this module.
     */
    const cleanup = () => {
        media.revert();

        window.removeEventListener(
            "product-modal-open",
            handleModalOpen,
        );

        if (dialog instanceof HTMLDialogElement) {
            dialog.removeEventListener(
                "close",
                handleNativeDialogClose,
            );
        }

        if (modalRefreshFrame !== null) {
            window.cancelAnimationFrame(
                modalRefreshFrame,
            );
        }

        document.documentElement.classList.remove(
            "menu-category-snap-active",
            "menu-modal-open",
        );

        delete root.dataset
            .menuCategoryNavigation;

        delete root.dataset.menuCategorySnapCount;
    };

    if (import.meta.hot) {
        import.meta.hot.dispose(cleanup);
    }

    return cleanup;
}
