import gsap from "gsap";
import { ScrollToPlugin } from "gsap/ScrollToPlugin";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollToPlugin, ScrollTrigger);

const reducedMotionQuery =
    "(prefers-reduced-motion: reduce)";

const desktopPointerQuery =
    "(min-width: 1024px) and (pointer: fine) and (min-height: 720px)";

/**
 * Return the fixed public-header height used when aligning sections.
 */
function getHeaderOffset() {
    return window.matchMedia(
        "(max-width: 1023px)",
    ).matches
        ? 76
        : 80;
}

/**
 * Return the immersive public-page root currently rendered.
 */
function getPageRoot() {
    const main = document.getElementById(
        "main-content",
    );

    if (!(main instanceof HTMLElement)) {
        return null;
    }

    return main.querySelector(
        [
            "[data-menu-page]",
            "[data-about-page]",
            "[data-gallery-page]",
            "[data-public-editorial-page]",
            "[data-contact-page]",
        ].join(","),
    );
}

/**
 * Return unique elements while preserving their document order.
 */
function uniqueElements(elements) {
    return [...new Set(elements)]
        .filter(
            (element) =>
                element instanceof HTMLElement,
        )
        .sort((first, second) => {
            if (first === second) {
                return 0;
            }

            const position =
                first.compareDocumentPosition(
                    second,
                );

            return position
                & Node.DOCUMENT_POSITION_FOLLOWING
                ? -1
                : 1;
        });
}

/**
 * Return explicit public sections for the active page type.
 *
 * Explicit contracts are used instead of shallow :scope selectors because
 * Livewire and editorial components may add additional wrapper elements.
 */
function getPageSections() {
    const root = getPageRoot();

    if (!(root instanceof HTMLElement)) {
        return [];
    }

    let sections = [];

    if (root.matches("[data-about-page]")) {
        sections = [
            ...root.querySelectorAll(
                "[data-about-panel]",
            ),
        ];
    } else if (
        root.matches("[data-gallery-page]")
    ) {
        sections = [
            ...root.querySelectorAll(
                "[data-gallery-panel]",
            ),
        ];
    } else if (
        root.matches("[data-contact-page]")
    ) {
        sections = [
            ...root.querySelectorAll(
                ".contact-panel",
            ),
        ];
    } else if (
        root.matches("[data-menu-page]")
    ) {
        sections = [
            document.getElementById(
                "menu-hero",
            ),
            document.getElementById(
                "menu-catalog",
            ),
            ...root.querySelectorAll(
                [
                    "[data-menu-category-section]",
                    "[data-menu-category-panel]",
                ].join(","),
            ),
        ];
    } else if (
        root.matches(
            "[data-public-editorial-page]",
        )
    ) {
        sections = [
            ...root.querySelectorAll(
                [
                    ":scope > section",
                    "[data-public-fullscreen-hero]",
                    "[data-public-reveal-group]",
                    "[data-public-scroll-section]",
                ].join(","),
            ),
        ];
    }

    /*
     * Generic fallback for future immersive pages.
     */
    if (sections.length === 0) {
        sections = [
            ...root.querySelectorAll(
                [
                    "[data-public-scroll-section]",
                    "section[id]",
                ].join(","),
            ),
        ];
    }

    return uniqueElements(
        sections.filter(Boolean),
    );
}

/**
 * Return the section with the greatest visible viewport area.
 */
function getCurrentSection(sections) {
    let currentSection =
        sections[0] ?? null;

    let greatestVisibleHeight = 0;

    sections.forEach((section) => {
        const rect =
            section.getBoundingClientRect();

        const visibleHeight = Math.max(
            0,
            Math.min(
                rect.bottom,
                window.innerHeight,
            )
                - Math.max(rect.top, 0),
        );

        if (
            visibleHeight
            <= greatestVisibleHeight
        ) {
            return;
        }

        greatestVisibleHeight =
            visibleHeight;

        currentSection = section;
    });

    return currentSection;
}

/**
 * Return the next section after the currently visible section.
 */
function getNextSection(sections) {
    const currentSection =
        getCurrentSection(sections);

    if (
        !(
            currentSection
            instanceof HTMLElement
        )
    ) {
        return null;
    }

    const currentIndex =
        sections.indexOf(currentSection);

    return sections[
        currentIndex + 1
    ] ?? null;
}

/**
 * Return the absolute destination for one section.
 */
function getDestination(
    section,
    sections = getPageSections(),
) {
    const sectionTop =
        window.scrollY
        + section
            .getBoundingClientRect()
            .top;

    const isFirstSection =
        sections[0] === section;

    return Math.max(
        0,
        Math.round(
            sectionTop
                - (
                    isFirstSection
                        ? 0
                        : getHeaderOffset()
                ),
        ),
    );
}

/**
 * Scroll to one section while respecting reduced-motion preferences.
 */
function scrollToSection(
    section,
    sections,
    duration = 0.7,
) {
    if (
        !(
            section
            instanceof HTMLElement
        )
    ) {
        return;
    }

    const destination =
        getDestination(
            section,
            sections,
        );

    const reduceMotion =
        window.matchMedia(
            reducedMotionQuery,
        ).matches;

    if (reduceMotion) {
        window.scrollTo({
            top: destination,
            left: 0,
            behavior: "auto",
        });

        return;
    }

    gsap.to(window, {
        duration,
        ease: "power3.inOut",
        overwrite: "auto",
        scrollTo: {
            y: destination,
            autoKill: true,
        },
        onComplete: () => {
            ScrollTrigger.refresh();
        },
    });
}

/**
 * Activate or hide the reusable scroll identifier.
 */
function updateIndicatorState(
    indicator,
    sections,
) {
    const nextSection =
        getNextSection(sections);

    const isReady =
        nextSection
        instanceof HTMLElement;

    indicator.hidden = false;

    indicator.dataset.scrollState =
        isReady
            ? "ready"
            : "complete";

    indicator.setAttribute(
        "aria-hidden",
        isReady
            ? "false"
            : "true",
    );

    indicator.tabIndex =
        isReady ? 0 : -1;
}

/**
 * Initialize the reusable fixed scroll identifier.
 */
function initScrollIdentifier() {
    const indicator =
        document.querySelector(
            "[data-public-scroll-identifier]",
        );

    if (
        !(
            indicator
            instanceof HTMLButtonElement
        )
    ) {
        return () => {};
    }

    let sections =
        getPageSections();

    /*
     * Non-immersive pages keep the globally rendered component hidden.
     */
    if (sections.length < 2) {
        indicator.hidden = true;
        indicator.setAttribute(
            "aria-hidden",
            "true",
        );

        return () => {};
    }

    const dot =
        indicator.querySelector(
            ".public-scroll-identifier__dot",
        );

    let dotTween = null;

    /*
     * CSS supplies the base animation. GSAP enhances it when available.
     */
    if (
        dot instanceof HTMLElement
        && !window.matchMedia(
            reducedMotionQuery,
        ).matches
    ) {
        dot.classList.add(
            "public-scroll-identifier__dot--gsap",
        );

        dotTween = gsap.fromTo(
            dot,
            {
                y: 0,
                scale: 1,
            },
            {
                y: 22,
                scale: 0.82,
                duration: 1.05,
                ease: "power1.inOut",
                repeat: -1,
                yoyo: true,
            },
        );
    }

    const refreshSections = () => {
        sections =
            getPageSections();

        if (sections.length < 2) {
            indicator.hidden = true;

            return;
        }

        updateIndicatorState(
            indicator,
            sections,
        );
    };

    const handleClick = () => {
        const nextSection =
            getNextSection(sections);

        scrollToSection(
            nextSection,
            sections,
        );
    };

    indicator.addEventListener(
        "click",
        handleClick,
    );

    const triggers =
        sections.map((section, index) =>
            ScrollTrigger.create({
                id:
                    "public-scroll-indicator-"
                    + (
                        section.id
                        || index
                    ),
                trigger: section,
                start: "top 55%",
                end: "bottom 45%",
                invalidateOnRefresh: true,
                onEnter: refreshSections,
                onEnterBack:
                    refreshSections,
                onLeave:
                    refreshSections,
                onLeaveBack:
                    refreshSections,
            }),
        );

    /*
     * Livewire may complete the Menu catalogue after this script initializes.
     * Observe structural updates and refresh the section contract once.
     */
    const pageRoot =
        getPageRoot();

    let refreshTimer = null;

    const observer =
        pageRoot instanceof HTMLElement
            ? new MutationObserver(() => {
                if (
                    refreshTimer !== null
                ) {
                    window.clearTimeout(
                        refreshTimer,
                    );
                }

                refreshTimer =
                    window.setTimeout(
                        () => {
                            refreshSections();
                            ScrollTrigger.refresh();
                        },
                        80,
                    );
            })
            : null;

    observer?.observe(pageRoot, {
        childList: true,
        subtree: true,
    });

    refreshSections();

    const handleLoad = () => {
        refreshSections();
        ScrollTrigger.refresh();
    };

    window.addEventListener(
        "load",
        handleLoad,
        {
            once: true,
        },
    );

    return () => {
        indicator.removeEventListener(
            "click",
            handleClick,
        );

        observer?.disconnect();
        dotTween?.kill();

        if (refreshTimer !== null) {
            window.clearTimeout(
                refreshTimer,
            );
        }

        triggers.forEach((trigger) => {
            trigger.kill();
        });
    };
}

/**
 * Initialize one controlled Menu hero-to-catalogue wheel transition.
 */
function initMenuHeroTransition() {
    const menuPage =
        document.querySelector(
            "[data-menu-page]",
        );

    const hero =
        document.getElementById(
            "menu-hero",
        );

    const catalogue =
        document.getElementById(
            "menu-catalog",
        );

    if (
        !(
            menuPage
            instanceof HTMLElement
        )
        || !(
            hero
            instanceof HTMLElement
        )
        || !(
            catalogue
            instanceof HTMLElement
        )
    ) {
        return () => {};
    }

    const media =
        gsap.matchMedia();

    media.add(
        {
            desktop:
                desktopPointerQuery,
            reducedMotion:
                reducedMotionQuery,
        },
        (context) => {
            const {
                desktop = false,
                reducedMotion = false,
            } =
                context.conditions ?? {};

            if (
                !desktop
                || reducedMotion
            ) {
                return () => {};
            }

            let heroIsActive = false;
            let transitionIsRunning =
                false;

            let gestureLocked = false;
            let gestureReleaseTimer =
                null;

            const heroTrigger =
                ScrollTrigger.create({
                    id:
                        "menu-hero-to-catalog",
                    trigger: hero,
                    start: "top top",
                    end: "bottom top+=120",
                    invalidateOnRefresh:
                        true,
                    onToggle: (self) => {
                        heroIsActive =
                            self.isActive;
                    },
                });

            const scheduleGestureRelease =
                () => {
                    if (
                        gestureReleaseTimer
                        !== null
                    ) {
                        window.clearTimeout(
                            gestureReleaseTimer,
                        );
                    }

                    gestureReleaseTimer =
                        window.setTimeout(
                            () => {
                                gestureLocked =
                                    false;

                                gestureReleaseTimer =
                                    null;
                            },
                            180,
                        );
                };

            const handleWheel = (
                event,
            ) => {
                if (
                    !heroIsActive
                    || transitionIsRunning
                    || gestureLocked
                    || event.ctrlKey
                    || event.deltaY <= 8
                    || Math.abs(
                        event.deltaX,
                    )
                        > Math.abs(
                            event.deltaY,
                        )
                ) {
                    return;
                }

                event.preventDefault();

                gestureLocked = true;
                transitionIsRunning =
                    true;

                const sections =
                    getPageSections();

                gsap.to(window, {
                    duration: 0.58,
                    ease: "power3.inOut",
                    overwrite: "auto",
                    scrollTo: {
                        y: getDestination(
                            catalogue,
                            sections,
                        ),
                        autoKill: false,
                    },
                    onComplete: () => {
                        transitionIsRunning =
                            false;

                        scheduleGestureRelease();
                        ScrollTrigger.refresh();
                    },
                    onInterrupt: () => {
                        transitionIsRunning =
                            false;

                        scheduleGestureRelease();
                    },
                });
            };

            window.addEventListener(
                "wheel",
                handleWheel,
                {
                    passive: false,
                },
            );

            return () => {
                window.removeEventListener(
                    "wheel",
                    handleWheel,
                );

                if (
                    gestureReleaseTimer
                    !== null
                ) {
                    window.clearTimeout(
                        gestureReleaseTimer,
                    );
                }

                heroTrigger.kill();
            };
        },
    );

    return () => {
        media.revert();
    };
}

/**
 * Initialize shared public scrolling.
 */
function initPublicScrollNavigation() {
    const cleanupIdentifier =
        initScrollIdentifier();

    const cleanupMenuTransition =
        initMenuHeroTransition();

    requestAnimationFrame(() => {
        ScrollTrigger.refresh();
    });

    window.addEventListener(
        "beforeunload",
        () => {
            cleanupIdentifier();
            cleanupMenuTransition();
        },
        {
            once: true,
        },
    );
}

if (
    document.readyState
    === "loading"
) {
    document.addEventListener(
        "DOMContentLoaded",
        initPublicScrollNavigation,
        {
            once: true,
        },
    );
} else {
    initPublicScrollNavigation();
}
