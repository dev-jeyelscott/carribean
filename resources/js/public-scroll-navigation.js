import gsap from "gsap";
import { ScrollToPlugin } from "gsap/ScrollToPlugin";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollToPlugin, ScrollTrigger);

const reducedMotionQuery = "(prefers-reduced-motion: reduce)";
const desktopPointerQuery =
    "(min-width: 1024px) and (pointer: fine) and (min-height: 720px)";

/**
 * Return the fixed public-header height used when aligning normal sections.
 */
function getHeaderOffset() {
    return window.matchMedia("(max-width: 1023px)").matches
        ? 76
        : 80;
}

/**
 * Return all meaningful top-level sections inside the public main content.
 */
function getPageSections() {
    const main = document.getElementById("main-content");

    if (!(main instanceof HTMLElement)) {
        return [];
    }

    const pageRoot =
        main.querySelector(
            [
                "[data-menu-page]",
                "[data-about-page]",
                "[data-gallery-page]",
                "[data-public-editorial-page]",
                "[data-contact-page]",
            ].join(","),
        ) ?? main;

    return [...pageRoot.querySelectorAll(":scope > section, :scope > * > section")]
        .filter((section) => section instanceof HTMLElement);
}

/**
 * Return the section occupying the largest visible area in the viewport.
 */
function getCurrentSection(sections) {
    let currentSection = sections[0] ?? null;
    let greatestVisibleHeight = 0;

    sections.forEach((section) => {
        const rect = section.getBoundingClientRect();

        const visibleHeight = Math.max(
            0,
            Math.min(rect.bottom, window.innerHeight)
                - Math.max(rect.top, 0),
        );

        if (visibleHeight <= greatestVisibleHeight) {
            return;
        }

        greatestVisibleHeight = visibleHeight;
        currentSection = section;
    });

    return currentSection;
}

/**
 * Return the next document section relative to the currently visible section.
 */
function getNextSection(sections) {
    const currentSection = getCurrentSection(sections);

    if (!(currentSection instanceof HTMLElement)) {
        return null;
    }

    const currentIndex = sections.indexOf(currentSection);

    return sections[currentIndex + 1] ?? null;
}

/**
 * Return the absolute scroll destination for one page section.
 */
function getDestination(section) {
    const sectionTop =
        window.scrollY
        + section.getBoundingClientRect().top;

    const isFirstSection =
        getPageSections()[0] === section;

    return Math.max(
        0,
        Math.round(
            sectionTop
            - (isFirstSection ? 0 : getHeaderOffset()),
        ),
    );
}

/**
 * Scroll to one section while respecting reduced-motion preferences.
 */
function scrollToSection(section, duration = 0.7) {
    if (!(section instanceof HTMLElement)) {
        return;
    }

    const destination = getDestination(section);
    const reduceMotion =
        window.matchMedia(reducedMotionQuery).matches;

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
 * Initialize the reusable fixed scroll identifier.
 */
function initScrollIdentifier() {
    const indicator = document.querySelector(
        "[data-public-scroll-identifier]",
    );

    if (!(indicator instanceof HTMLButtonElement)) {
        return;
    }

    const sections = getPageSections();

    if (sections.length < 2) {
        indicator.hidden = true;

        return;
    }

    const dot = indicator.querySelector(
        ".public-scroll-identifier__dot",
    );

    let dotTween = null;

    if (
        dot instanceof HTMLElement
        && !window.matchMedia(reducedMotionQuery).matches
    ) {
        dotTween = gsap.to(dot, {
            y: 22,
            duration: 1.15,
            ease: "power1.inOut",
            repeat: -1,
            yoyo: true,
        });
    }

    /**
     * Update whether another section remains below the viewport.
     */
    const updateState = () => {
        const nextSection = getNextSection(sections);

        indicator.dataset.scrollState =
            nextSection instanceof HTMLElement
                ? "ready"
                : "complete";
    };

    /**
     * Navigate to the next visible page section.
     */
    const handleClick = () => {
        scrollToSection(getNextSection(sections));
    };

    indicator.addEventListener("click", handleClick);

    const triggers = sections.map((section) =>
        ScrollTrigger.create({
            trigger: section,
            start: "top 55%",
            end: "bottom 45%",
            invalidateOnRefresh: true,
            onEnter: updateState,
            onEnterBack: updateState,
        }),
    );

    updateState();

    window.addEventListener("load", () => {
        ScrollTrigger.refresh();
        updateState();
    }, {
        once: true,
    });

    window.addEventListener("beforeunload", () => {
        indicator.removeEventListener(
            "click",
            handleClick,
        );

        dotTween?.kill();

        triggers.forEach((trigger) => {
            trigger.kill();
        });
    }, {
        once: true,
    });
}

/**
 * Initialize one controlled Menu-hero wheel transition.
 *
 * Only the first downward desktop wheel gesture while the Menu hero is active
 * moves to the catalogue. All later scrolling remains controlled by the
 * existing Menu category navigation.
 */
function initMenuHeroTransition() {
    const menuPage = document.querySelector(
        "[data-menu-page]",
    );

    const hero = document.getElementById("menu-hero");
    const catalogue = document.getElementById("menu-catalog");

    if (
        !(menuPage instanceof HTMLElement)
        || !(hero instanceof HTMLElement)
        || !(catalogue instanceof HTMLElement)
    ) {
        return;
    }

    const media = gsap.matchMedia();

    media.add(
        {
            desktop: desktopPointerQuery,
            reducedMotion: reducedMotionQuery,
        },
        (context) => {
            const {
                desktop = false,
                reducedMotion = false,
            } = context.conditions ?? {};

            if (!desktop || reducedMotion) {
                return () => {};
            }

            let heroIsActive = false;
            let transitionIsRunning = false;
            let gestureLocked = false;
            let gestureReleaseTimer = null;

            const heroTrigger = ScrollTrigger.create({
                id: "menu-hero-to-catalog",
                trigger: hero,
                start: "top top",
                end: "bottom top+=120",
                invalidateOnRefresh: true,
                onToggle: (self) => {
                    heroIsActive = self.isActive;
                },
            });

            /**
             * Release the gesture lock after trackpad momentum has settled.
             */
            const scheduleGestureRelease = () => {
                if (gestureReleaseTimer !== null) {
                    window.clearTimeout(
                        gestureReleaseTimer,
                    );
                }

                gestureReleaseTimer = window.setTimeout(
                    () => {
                        gestureLocked = false;
                        gestureReleaseTimer = null;
                    },
                    180,
                );
            };

            /**
             * Convert the first meaningful downward wheel input into the
             * Menu hero-to-catalogue transition.
             */
            const handleWheel = (event) => {
                if (
                    !heroIsActive
                    || transitionIsRunning
                    || gestureLocked
                    || event.ctrlKey
                    || event.deltaY <= 8
                    || Math.abs(event.deltaX)
                        > Math.abs(event.deltaY)
                ) {
                    return;
                }

                event.preventDefault();

                gestureLocked = true;
                transitionIsRunning = true;

                gsap.to(window, {
                    duration: 0.58,
                    ease: "power3.inOut",
                    overwrite: "auto",
                    scrollTo: {
                        y: getDestination(catalogue),
                        autoKill: false,
                    },
                    onComplete: () => {
                        transitionIsRunning = false;
                        scheduleGestureRelease();
                        ScrollTrigger.refresh();
                    },
                    onInterrupt: () => {
                        transitionIsRunning = false;
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

                if (gestureReleaseTimer !== null) {
                    window.clearTimeout(
                        gestureReleaseTimer,
                    );
                }

                heroTrigger.kill();
            };
        },
    );

    window.addEventListener("beforeunload", () => {
        media.revert();
    }, {
        once: true,
    });
}

/**
 * Initialize shared public scrolling after the parsed DOM is available.
 */
function initPublicScrollNavigation() {
    initScrollIdentifier();
    initMenuHeroTransition();

    requestAnimationFrame(() => {
        ScrollTrigger.refresh();
    });
}

if (document.readyState === "loading") {
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
