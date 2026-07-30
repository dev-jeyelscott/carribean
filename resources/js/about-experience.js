import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

const desktopQuery = "(min-width: 1024px) and (pointer: fine)";
const reducedMotionQuery = "(prefers-reduced-motion: reduce)";

/**
 * Mark one About section and its navigation link as active.
 */
function setActiveSection(root, sectionId) {
    root.dataset.aboutActiveSection = sectionId;

    root.querySelectorAll("[data-about-section-link]").forEach((link) => {
        const isActive = link.getAttribute("href") === `#${sectionId}`;

        link.setAttribute(
            "aria-current",
            isActive ? "location" : "false",
        );
    });
}

/**
 * Create ScrollTriggers that keep the section navigation in sync.
 */
function initializeSectionNavigation(root) {
    const panels = [
        ...root.querySelectorAll("[data-about-panel]"),
    ];

    panels.forEach((panel) => {
        if (!(panel instanceof HTMLElement) || !panel.id) {
            return;
        }

        ScrollTrigger.create({
            trigger: panel,
            start: "top 55%",
            end: "bottom 45%",
            onEnter: () => {
                setActiveSection(root, panel.id);
            },
            onEnterBack: () => {
                setActiveSection(root, panel.id);
            },
        });
    });
}

/**
 * Animate the About hero image and content after JavaScript initializes.
 */
function initializeHero(root) {
    const heroImage = root.querySelector(
        "[data-about-hero-image] img",
    );

    if (heroImage) {
        gsap.fromTo(
            heroImage,
            {
                scale: 1.06,
            },
            {
                duration: 1.6,
                ease: "power4.out",
                scale: 1,
            },
        );
    }

    const heroContent = root.querySelector(
        "[data-about-hero-content]",
    );

    if (!heroContent) {
        return;
    }

    const targets = heroContent.querySelectorAll(
        "[data-about-reveal]",
    );

    gsap.set(targets, {
        autoAlpha: 0,
        y: 26,
    });

    gsap.timeline({
        defaults: {
            ease: "power4.out",
        },
    }).to(targets, {
        autoAlpha: 1,
        duration: 1,
        stagger: 0.11,
        y: 0,
    });

    const scrollControl = root.querySelector(
        "#about-hero > [data-about-reveal]",
    );

    if (scrollControl) {
        gsap.fromTo(
            scrollControl,
            {
                autoAlpha: 0,
                y: 12,
            },
            {
                autoAlpha: 1,
                delay: 0.75,
                duration: 0.7,
                ease: "power3.out",
                y: 0,
            },
        );
    }
}

/**
 * Reveal each section's opted-in content once it enters the viewport.
 */
function initializeSectionReveals(root) {
    root.querySelectorAll("[data-about-panel]").forEach((panel, index) => {
        if (index === 0) {
            return;
        }

        const targets = panel.querySelectorAll(
            "[data-about-reveal]",
        );

        if (!targets.length) {
            return;
        }

        gsap.set(targets, {
            autoAlpha: 0,
            y: 30,
        });

        gsap.to(targets, {
            autoAlpha: 1,
            duration: 0.85,
            ease: "power3.out",
            stagger: 0.09,
            y: 0,
            scrollTrigger: {
                trigger: panel,
                start: "top 72%",
                once: true,
            },
        });
    });
}

/**
 * Reveal editorial images with a restrained clipping transition.
 */
function initializeImageReveals(root) {
    root.querySelectorAll("[data-about-image-reveal]").forEach((frame) => {
        const image = frame.querySelector("img");

        if (!image) {
            return;
        }

        gsap.set(frame, {
            clipPath: "inset(0 0 100% 0)",
        });

        gsap.set(image, {
            scale: 1.05,
        });

        gsap.timeline({
            scrollTrigger: {
                trigger: frame,
                start: "top 82%",
                once: true,
            },
        })
            .to(frame, {
                clipPath: "inset(0 0 0% 0)",
                duration: 0.95,
                ease: "power4.out",
            })
            .to(
                image,
                {
                    duration: 1.15,
                    ease: "power3.out",
                    scale: 1,
                },
                "<",
            );
    });
}

/**
 * Add subtle image depth on desktop without changing document scrolling.
 */
function initializeParallax(root) {
    root.querySelectorAll("[data-about-parallax]").forEach((frame) => {
        const image = frame.querySelector("img");

        if (!image) {
            return;
        }

        gsap.fromTo(
            image,
            {
                yPercent: -3,
            },
            {
                ease: "none",
                yPercent: 3,
                scrollTrigger: {
                    trigger: frame,
                    start: "top bottom",
                    end: "bottom top",
                    scrub: true,
                },
            },
        );
    });
}

/**
 * Restore every animated element to its readable final state.
 */
function setReducedMotionState(root) {
    gsap.set(
        root.querySelectorAll(
            [
                "[data-about-reveal]",
                "[data-about-image-reveal]",
                "[data-about-hero-image] img",
                "[data-about-parallax] img",
            ].join(", "),
        ),
        {
            autoAlpha: 1,
            clearProps: "clipPath,transform",
        },
    );
}

/**
 * Initialize the complete About-page animation experience.
 */
export function initAboutExperience(
    root = document.querySelector("[data-about-page]"),
) {
    if (!(root instanceof HTMLElement)) {
        return () => {};
    }

    const media = gsap.matchMedia();

    media.add(
        {
            desktop: desktopQuery,
            reducedMotion: reducedMotionQuery,
        },
        (mediaContext) => {
            const {
                desktop = false,
                reducedMotion = false,
            } = mediaContext.conditions;

            const animationContext = gsap.context(() => {
                initializeSectionNavigation(root);

                if (reducedMotion) {
                    setReducedMotionState(root);
                    root.dataset.aboutMotion = "reduced";

                    return;
                }

                initializeHero(root);
                initializeSectionReveals(root);
                initializeImageReveals(root);

                if (desktop) {
                    initializeParallax(root);
                }

                root.dataset.aboutMotion = "ready";
            }, root);

            const refreshFrame = window.requestAnimationFrame(() => {
                ScrollTrigger.refresh();
            });

            return () => {
                window.cancelAnimationFrame(refreshFrame);
                animationContext.revert();
            };
        },
    );

    const cleanup = () => {
        media.revert();
    };

    if (import.meta.hot) {
        import.meta.hot.dispose(cleanup);
    }

    return cleanup;
}
