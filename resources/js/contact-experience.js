import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

const desktopQuery = "(min-width: 1024px)";
const reducedMotionQuery = "(prefers-reduced-motion: reduce)";

const heroRevealDuration = 0.95;
const heroRevealStagger = 0.11;
const panelRevealDuration = 0.9;
const panelRevealStagger = 0.09;

/**
 * Return every valid Contact section in document order.
 */
function getSections(root) {
    return [...root.querySelectorAll('[data-gsap="section"]')].filter(
        (section) => section instanceof HTMLElement && section.id,
    );
}

/**
 * Return coordinated reveal targets without animating nested content twice.
 *
 * Standalone reveal elements animate individually. Elements inside a managed
 * panel move with their parent panel so nested transforms cannot make the
 * Contact page feel slower than the About page.
 */
function getSectionTargets(section) {
    const panels = [
        ...section.querySelectorAll('[data-gsap="panel"]'),
    ];

    const standaloneReveals = [
        ...section.querySelectorAll("[data-gsap-reveal]"),
    ].filter(
        (target) => !target.closest('[data-gsap="panel"]'),
    );

    return [
        ...standaloneReveals,
        ...panels,
    ];
}

/**
 * Count Contact-specific ScrollTriggers for browser diagnostics.
 */
function updateTriggerCount(root) {
    const triggerCount = ScrollTrigger.getAll().filter((trigger) => {
        const id = trigger.vars.id;

        return typeof id === "string" && id.startsWith("contact-");
    }).length;

    root.dataset.contactTriggerCount = String(triggerCount);
}

/**
 * Animate the Contact hero with the same pacing used by the About hero.
 */
function initializeHero(root, desktop) {
    const hero = root.querySelector("#contact-hero");

    if (!(hero instanceof HTMLElement)) {
        return () => {};
    }

    const imageFrame = hero.querySelector(
        '[data-gsap="hero-image"]',
    );

    const image = imageFrame?.querySelector("img");

    const revealTargets = [
        ...hero.querySelectorAll("[data-gsap-reveal]"),
    ];

    hero.dataset.contactAnimationState = "active";

    gsap.set(revealTargets, {
        autoAlpha: 0,
        y: 34,
    });

    const timeline = gsap.timeline({
        defaults: {
            ease: "power4.out",
        },
        onComplete: () => {
            hero.dataset.contactAnimationState = "complete";
        },
    });

    if (image instanceof HTMLImageElement) {
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

    if (revealTargets.length > 0) {
        timeline.to(
            revealTargets,
            {
                autoAlpha: 1,
                duration: heroRevealDuration,
                stagger: heroRevealStagger,
                y: 0,
            },
            0.12,
        );
    }

    let parallaxTween = null;

    /*
     * About uses restrained hero depth only on larger layouts. Apply the same
     * behavior without introducing scroll smoothing or page-level hijacking.
     */
    if (desktop && image instanceof HTMLImageElement) {
        parallaxTween = gsap.fromTo(
            image,
            {
                yPercent: 0,
            },
            {
                ease: "none",
                yPercent: 5,
                scrollTrigger: {
                    id: "contact-parallax-contact-hero",
                    trigger: hero,
                    start: "top top",
                    end: "bottom top",
                    scrub: true,
                    invalidateOnRefresh: true,
                },
            },
        );
    }

    /**
     * Remove the hero timeline and its optional parallax controller.
     */
    return () => {
        timeline.kill();

        if (parallaxTween) {
            parallaxTween.scrollTrigger?.kill();
            parallaxTween.kill();
        }
    };
}

/**
 * Create one coordinated, one-time reveal timeline for a Contact section.
 */
function createSectionReveal(section, showMarkers) {
    const targets = getSectionTargets(section);

    if (targets.length === 0) {
        section.dataset.contactAnimationState = "complete";

        return {
            kill: () => {},
            play: () => {},
        };
    }

    gsap.set(targets, {
        autoAlpha: 0,
        y: 42,
    });

    section.dataset.contactAnimationState = "pending";

    const timeline = gsap.timeline({
        paused: true,
        defaults: {
            ease: "power3.out",
        },
        onStart: () => {
            section.dataset.contactAnimationState = "active";
        },
        onComplete: () => {
            section.dataset.contactAnimationState = "complete";
        },
    });

    timeline.to(targets, {
        autoAlpha: 1,
        duration: panelRevealDuration,
        stagger: panelRevealStagger,
        y: 0,
    });

    let hasPlayed = false;

    /**
     * Play the section once and keep its content visible afterward.
     */
    const play = () => {
        if (hasPlayed) {
            return;
        }

        hasPlayed = true;
        timeline.play(0);
    };

    const trigger = ScrollTrigger.create({
        id: `contact-animation-${section.id}`,
        trigger: section,
        start: "top 76%",
        end: "bottom 24%",
        invalidateOnRefresh: true,
        markers: showMarkers,
        onEnter: play,
        onEnterBack: play,
    });

    return {
        play,

        /**
         * Remove the reveal timeline and its ScrollTrigger.
         */
        kill: () => {
            trigger.kill();
            timeline.kill();
        },
    };
}

/**
 * Restore immediately readable final states when reduced motion is enabled.
 */
function setReducedMotionState(root, sections) {
    gsap.set(
        root.querySelectorAll(
            [
                "[data-gsap-reveal]",
                '[data-gsap="panel"]',
                '[data-gsap="hero-image"] img',
            ].join(", "),
        ),
        {
            autoAlpha: 1,
            clearProps: "opacity,transform,visibility,willChange",
        },
    );

    sections.forEach((section) => {
        section.dataset.contactAnimationState = "complete";
    });
}

/**
 * Initialize Contact-specific animation while the shared pager continues to
 * own exact section navigation and wheel-gesture snapping.
 */
export function initContactExperience(
    root = document.querySelector("[data-contact-page]"),
) {
    if (!(root instanceof HTMLElement)) {
        return () => {};
    }

    const sections = getSections(root);

    if (sections.length === 0) {
        return () => {};
    }

    const showMarkers = new URLSearchParams(
        window.location.search,
    ).has("debug-scroll");

    root.dataset.contactMotion = "initializing";

    const media = gsap.matchMedia();

    media.add(
        {
            all: "all",
            desktop: desktopQuery,
            reducedMotion: reducedMotionQuery,
        },
        (context) => {
            const {
                desktop = false,
                reducedMotion = false,
            } = context.conditions ?? {};

            const cleanupCallbacks = [];
            const revealControllers = new Map();

            let disposed = false;

            if (reducedMotion) {
                setReducedMotionState(root, sections);
                root.dataset.contactMotion = "reduced";

                return () => {};
            }

            cleanupCallbacks.push(
                initializeHero(root, desktop),
            );

            sections.slice(1).forEach((section) => {
                const controller = createSectionReveal(
                    section,
                    showMarkers,
                );

                revealControllers.set(section.id, controller);
                cleanupCallbacks.push(controller.kill);
            });

            /**
             * Start the destination section reveal as soon as the shared pager
             * activates it, instead of waiting for an unrelated second timeline.
             */
            const handleSectionActivation = (event) => {
                if (!(event instanceof CustomEvent)) {
                    return;
                }

                if (event.detail?.context !== "contact") {
                    return;
                }

                const sectionId = event.detail?.sectionId;

                if (typeof sectionId !== "string") {
                    return;
                }

                revealControllers.get(sectionId)?.play();
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
             * Recalculate trigger positions after fonts and responsive images
             * have had an opportunity to affect section geometry.
             */
            const refreshLayout = () => {
                if (disposed) {
                    return;
                }

                ScrollTrigger.refresh();
                updateTriggerCount(root);
            };

            const refreshFrame = window.requestAnimationFrame(
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

            const fontsReady = document.fonts?.ready;

            fontsReady
                ?.then(() => {
                    refreshLayout();
                })
                .catch(() => {
                    updateTriggerCount(root);
                });

            cleanupCallbacks.push(() => {
                disposed = true;

                window.cancelAnimationFrame(refreshFrame);

                window.removeEventListener(
                    "load",
                    handleWindowLoad,
                );
            });

            root.dataset.contactMotion = "ready";
            updateTriggerCount(root);

            return () => {
                cleanupCallbacks
                    .reverse()
                    .forEach((callback) => callback());
            };
        },
        root,
    );

    /**
     * Remove Contact animation state and all responsive GSAP controllers.
     */
    const cleanup = () => {
        media.revert();

        delete root.dataset.contactMotion;
        delete root.dataset.contactTriggerCount;

        sections.forEach((section) => {
            section.removeAttribute(
                "data-contact-animation-state",
            );
        });
    };

    if (import.meta.hot) {
        import.meta.hot.dispose(cleanup);
    }

    return cleanup;
}
