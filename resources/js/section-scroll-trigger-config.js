/**
 * Homepage-only ScrollTrigger configuration.
 *
 * Non-home routes must use native scrolling, IntersectionObserver, and CSS
 * transitions instead of importing the ScrollTrigger plugin.
 */
export const homepageSectionScrollTriggerConfig = {
    media: {
        desktop: "(min-width: 1024px) and (pointer: fine)",
        reducedMotion: "(prefers-reduced-motion: reduce)",
        shortViewport: "(max-height: 719px)",
    },
    wheel: {
        enabled: true,
        activationThreshold: 8,
        gestureReleaseDelay: 140,
    },
    navigation: {
        duration: 0.48,
        ease: "power3.out",
    },
    reveal: {
        distance: 34,
        duration: 0.9,
        ease: "power3.out",
        stagger: 0.09,
        trigger: {
            start: "top 76%",
            end: "bottom 24%",
            invalidateOnRefresh: true,
        },
    },
    tracking: {
        trigger: {
            start: "top 52%",
            end: "bottom 48%",
            invalidateOnRefresh: true,
        },
    },
    depth: {
        from: {
            scale: 1.06,
            yPercent: -2,
        },
        to: {
            ease: "none",
            scale: 1.02,
            yPercent: 2,
        },
        trigger: {
            start: "top bottom",
            end: "bottom top",
            scrub: true,
            invalidateOnRefresh: true,
        },
    },
};
