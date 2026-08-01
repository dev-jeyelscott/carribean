const baseSectionScrollTriggerConfig = {
    media: {
        desktop: "(min-width: 1024px) and (pointer: fine)",
        reducedMotion: "(prefers-reduced-motion: reduce)",
        shortViewport: "(max-height: 719px)",
    },
    wheel: {
        enabled: false,
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

/**
 * Create an isolated section-scroll profile with optional page overrides.
 */
export function createSectionScrollTriggerConfig(overrides = {}) {
    return {
        media: {
            ...baseSectionScrollTriggerConfig.media,
            ...overrides.media,
        },
        wheel: {
            ...baseSectionScrollTriggerConfig.wheel,
            ...overrides.wheel,
        },
        navigation: {
            ...baseSectionScrollTriggerConfig.navigation,
            ...overrides.navigation,
        },
        reveal: {
            ...baseSectionScrollTriggerConfig.reveal,
            ...overrides.reveal,
            trigger: {
                ...baseSectionScrollTriggerConfig.reveal.trigger,
                ...overrides.reveal?.trigger,
            },
        },
        tracking: {
            ...baseSectionScrollTriggerConfig.tracking,
            ...overrides.tracking,
            trigger: {
                ...baseSectionScrollTriggerConfig.tracking.trigger,
                ...overrides.tracking?.trigger,
            },
        },
        depth: {
            ...baseSectionScrollTriggerConfig.depth,
            ...overrides.depth,
            from: {
                ...baseSectionScrollTriggerConfig.depth.from,
                ...overrides.depth?.from,
            },
            to: {
                ...baseSectionScrollTriggerConfig.depth.to,
                ...overrides.depth?.to,
            },
            trigger: {
                ...baseSectionScrollTriggerConfig.depth.trigger,
                ...overrides.depth?.trigger,
            },
        },
    };
}

/*
 * Preserve each page's established motion while sharing the same base
 * media, navigation, reveal, tracking, and depth configuration.
 */
export const homepageSectionScrollTriggerConfig =
    createSectionScrollTriggerConfig({
        wheel: {
            enabled: true,
        },
    });

export const aboutSectionScrollTriggerConfig =
    createSectionScrollTriggerConfig({
        wheel: {
            enabled: true,
        },
        reveal: {
            distance: 42,
        },
    });
