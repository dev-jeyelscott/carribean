import gsap from "gsap";
import { ScrollToPlugin } from "gsap/ScrollToPlugin";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollToPlugin, ScrollTrigger);

const desktopQuery = "(min-width: 1024px) and (pointer: fine)";
const reducedMotionQuery = "(prefers-reduced-motion: reduce)";
const shortViewportQuery = "(max-height: 719px)";

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
                duration: 0.8,
                ease: "power3.inOut",
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
 * - A small down-scroll snaps toward the next panel.
 * - A small up-scroll snaps toward the previous panel.
 * - Native scrolling remains available and can leave the final panel.
 *
 * Mobile, short viewports, and reduced-motion environments:
 * - No forced snapping.
 * - Content remains in normal document flow.
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

            const activeTriggers = panels.map((panel, index) =>
                ScrollTrigger.create({
                    trigger: panel,
                    start: "top 52%",
                    end: "bottom 48%",
                    onEnter: () => setActivePanel(root, panels, index),
                    onEnterBack: () => setActivePanel(root, panels, index),
                }),
            );

            cleanup.push(() => {
                activeTriggers.forEach((trigger) => trigger.kill());
            });

            setActivePanel(root, panels, 0);

            if (
                desktop
                && !reducedMotion
                && !shortViewport
                && panels.length > 1
            ) {
                document.documentElement.classList.add(
                    "home-section-snap-active",
                );

                /*
                 * Every desktop panel has one viewport of scroll distance.
                 * Directional snapping moves toward the section matching the
                 * user's latest scroll direction, even after a slight gesture.
                 */
                const snapIncrement = 1 / (panels.length - 1);

                const snapTrigger = ScrollTrigger.create({
                    trigger: root,
                    start: "top top",
                    end: "bottom bottom",
                    invalidateOnRefresh: true,
                    snap: {
                        snapTo: snapIncrement,
                        directional: true,
                        inertia: false,
                        delay: 0.06,
                        duration: {
                            min: 0.35,
                            max: 0.75,
                        },
                        ease: "power2.inOut",
                    },
                });

                cleanup.push(() => {
                    snapTrigger.kill();
                    document.documentElement.classList.remove(
                        "home-section-snap-active",
                    );
                });
            }

            const refresh = () => ScrollTrigger.refresh();

            window.addEventListener("load", refresh, {
                once: true,
            });

            cleanup.push(() => {
                window.removeEventListener("load", refresh);
            });

            document.fonts?.ready
                .then(refresh)
                .catch(() => {
                    // Font loading must never prevent homepage interaction.
                });

            return () => {
                cleanup.reverse().forEach((callback) => callback());
            };
        },
    );

    const cleanup = () => {
        media.revert();
        document.documentElement.classList.remove(
            "home-section-snap-active",
        );
    };

    if (import.meta.hot) {
        import.meta.hot.dispose(cleanup);
    }

    return cleanup;
}
