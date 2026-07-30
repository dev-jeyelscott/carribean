import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

/**
 * Animate the immersive hero without delaying access to its content.
 */
function animateHero(root, isDesktop) {
    const hero = root.querySelector("[data-public-fullscreen-hero]");

    if (!(hero instanceof HTMLElement)) {
        return;
    }

    const copyItems = hero.querySelectorAll(
        "[data-public-hero-copy] > *",
    );

    if (copyItems.length > 0) {
        gsap.from(copyItems, {
            autoAlpha: 0,
            duration: 0.9,
            ease: "power3.out",
            stagger: 0.09,
            y: isDesktop ? 38 : 24,
        });
    }

    const heroMedia = hero.querySelector(
        "[data-public-hero-media]",
    );

    if (heroMedia instanceof HTMLElement) {
        gsap.from(heroMedia, {
            autoAlpha: 0,
            duration: 1,
            ease: "power3.out",
            scale: 0.97,
            x: isDesktop ? 36 : 0,
            y: isDesktop ? 0 : 24,
        });
    }
}

/**
 * Reveal grouped page content as it enters the viewport.
 */
function animateRevealGroups(root, isDesktop) {
    root.querySelectorAll("[data-public-reveal-group]")
        .forEach((group) => {
            if (!(group instanceof HTMLElement)) {
                return;
            }

            const items = group.querySelectorAll(
                "[data-public-reveal-item]",
            );

            if (items.length === 0) {
                return;
            }

            gsap.from(items, {
                autoAlpha: 0,
                duration: 0.8,
                ease: "power3.out",
                stagger: 0.08,
                y: isDesktop ? 34 : 22,
                scrollTrigger: {
                    trigger: group,
                    start: "top 84%",
                    once: true,
                },
            });
        });
}

/**
 * Animate visual order progress from the beginning to its stored value.
 */
function animateOrderProgress(root) {
    root.querySelectorAll("[data-public-progress]")
        .forEach((progress) => {
            if (!(progress instanceof HTMLElement)) {
                return;
            }

            const rawProgress = Number(
                progress.dataset.progress ?? "0",
            );

            const normalizedProgress = Math.min(
                100,
                Math.max(0, rawProgress),
            );

            gsap.fromTo(
                progress,
                {
                    scaleX: 0,
                },
                {
                    duration: 1,
                    ease: "power3.out",
                    scaleX: normalizedProgress / 100,
                    scrollTrigger: {
                        trigger: progress,
                        start: "top 92%",
                        once: true,
                    },
                },
            );
        });
}

/**
 * Add restrained desktop-only depth to designated hero artwork.
 */
function animateParallax(root) {
    root.querySelectorAll("[data-public-parallax]")
        .forEach((element) => {
            if (!(element instanceof HTMLElement)) {
                return;
            }

            const hero = element.closest(
                "[data-public-fullscreen-hero]",
            );

            if (!(hero instanceof HTMLElement)) {
                return;
            }

            gsap.to(element, {
                ease: "none",
                yPercent: 8,
                scrollTrigger: {
                    trigger: hero,
                    start: "top top",
                    end: "bottom top",
                    scrub: 0.7,
                },
            });
        });
}

/**
 * Initialize the page-scoped public editorial and order animations.
 */
export function initPublicPageExperience(
    root = document.querySelector(
        "[data-public-page-motion]",
    ),
) {
    if (!(root instanceof HTMLElement)) {
        return () => {};
    }

    const media = gsap.matchMedia(root);

    media.add(
        {
            isDesktop: "(min-width: 1024px)",
            reduceMotion: "(prefers-reduced-motion: reduce)",
        },
        (context) => {
            const {
                isDesktop,
                reduceMotion,
            } = context.conditions;

            if (reduceMotion) {
                gsap.set(
                    root.querySelectorAll(
                        [
                            "[data-public-hero-copy] > *",
                            "[data-public-hero-media]",
                            "[data-public-reveal-item]",
                            "[data-public-parallax]",
                        ].join(","),
                    ),
                    {
                        clearProps: "all",
                    },
                );

                return;
            }

            animateHero(root, isDesktop);
            animateRevealGroups(root, isDesktop);
            animateOrderProgress(root);

            if (isDesktop) {
                animateParallax(root);
            }
        },
    );

    /**
     * Recalculate positions after responsive images and web fonts settle.
     */
    const refreshScrollTriggers = () => {
        ScrollTrigger.refresh();
    };

    if (document.readyState === "complete") {
        window.requestAnimationFrame(refreshScrollTriggers);
    } else {
        window.addEventListener(
            "load",
            refreshScrollTriggers,
            {
                once: true,
            },
        );
    }

    /**
     * Remove the page's animations and ScrollTriggers during hot reload.
     */
    const cleanup = () => {
        window.removeEventListener(
            "load",
            refreshScrollTriggers,
        );

        media.revert();
    };

    if (import.meta.hot) {
        import.meta.hot.dispose(cleanup);
    }

    return cleanup;
}
