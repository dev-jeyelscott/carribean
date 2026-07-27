import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

const revealDistance = 28;

/**
 * Reveal child elements that opt into the shared public motion contract.
 */
function revealTimeline(target, options = {}) {
    const elements = target.querySelectorAll("[data-gsap-reveal]");

    if (!elements.length) {
        return;
    }

    gsap.set(elements, { autoAlpha: 0, y: revealDistance });

    gsap.timeline({
        scrollTrigger: {
            trigger: target,
            start: options.start ?? "top 82%",
            once: true,
        },
    }).to(elements, {
        autoAlpha: 1,
        duration: options.duration ?? 0.85,
        ease: "power3.out",
        stagger: options.stagger ?? 0.1,
        y: 0,
    });
}

/**
 * Reveal one managed image without blocking native rendering.
 */
function revealImage(target) {
    const image = target.querySelector("img");

    if (!image) {
        return;
    }

    gsap.set(target, { clipPath: "inset(0 0 100% 0)" });
    gsap.set(image, { scale: 1.04 });

    gsap.timeline({
        scrollTrigger: {
            trigger: target,
            start: "top 82%",
            once: true,
        },
    })
        .to(target, {
            clipPath: "inset(0 0 0% 0)",
            duration: 1,
            ease: "power4.out",
        })
        .to(
            image,
            {
                scale: 1,
                duration: 1.2,
                ease: "power3.out",
            },
            "<",
        );
}

/**
 * Add restrained image depth on scroll-capable devices.
 */
function addParallax(target, amount = 5) {
    const image = target.querySelector("img");

    if (!image) {
        return;
    }

    gsap.fromTo(
        image,
        { yPercent: -amount / 2 },
        {
            yPercent: amount / 2,
            ease: "none",
            scrollTrigger: {
                trigger: target,
                start: "top bottom",
                end: "bottom top",
                scrub: true,
            },
        },
    );
}

/**
 * Reveal decorative frames used by editorial sections.
 */
function revealFrame(target) {
    gsap.fromTo(
        target,
        { autoAlpha: 0, scale: 0.98 },
        {
            autoAlpha: 1,
            duration: 0.9,
            ease: "power3.out",
            scale: 1,
            scrollTrigger: {
                trigger: target.parentElement ?? target,
                start: "top 78%",
                end: "bottom top",
                once: true,
            },
        },
    );
}

/**
 * Animate the shared public hero and its content.
 */
function initializeHeroMotion(root, { desktop }) {
    const heroImage = root.querySelector('[data-gsap="hero-image"]');

    if (heroImage?.querySelector("img")) {
        gsap.fromTo(
            heroImage.querySelector("img"),
            { scale: 1.06 },
            {
                scale: 1,
                duration: 1.6,
                ease: "power4.out",
            },
        );

        addParallax(heroImage, desktop ? 3 : 1.5);
    }

    const heroContent = root.querySelector('[data-gsap="hero-content"]');

    if (!heroContent) {
        return;
    }

    const heroItems = heroContent.querySelectorAll("[data-gsap-reveal]");

    gsap.set(heroItems, { autoAlpha: 0, y: 24 });

    gsap.timeline({
        defaults: { ease: "power4.out" },
    }).to(heroItems, {
        autoAlpha: 1,
        duration: 1.1,
        stagger: 0.12,
        y: 0,
    });
}

/**
 * Restore final menu states when reduced motion is requested.
 */
function setMenuFinalStates(root) {
    gsap.set(
        root.querySelectorAll(
            '[data-menu-motion="hero-image"], [data-menu-motion="card-image"]',
        ),
        {
            clipPath: "inset(0 0 0% 0)",
            scale: 1,
        },
    );

    gsap.set(
        root.querySelectorAll(
            '[data-menu-motion="hero-item"], [data-menu-motion="full-heading"], [data-menu-motion="course-number"], [data-menu-motion="course-title"], [data-menu-motion="course-description"], [data-menu-motion="course-rule"], [data-menu-motion="card"], [data-menu-motion="card-copy"], [data-menu-motion="closing-item"], [data-menu-motion="closing-actions"]',
        ),
        {
            autoAlpha: 1,
            clipPath: "inset(0 0 0% 0)",
            scaleX: 1,
            x: 0,
            y: 0,
        },
    );
}

/**
 * Keep the menu category indicator aligned with the active course.
 */
function initializeMenuNavigation(root) {
    const categoryNav = root.querySelector(
        '[data-menu-motion="category-nav"]',
    );

    if (!categoryNav) {
        return () => {};
    }

    const links = [
        ...categoryNav.querySelectorAll("[data-menu-category-link]"),
    ];
    const courses = [
        ...root.querySelectorAll('[data-menu-motion="course"]'),
    ];
    const indicator = categoryNav.querySelector(
        "[data-menu-category-indicator]",
    );
    const cleanup = [];

    let activeLink =
        links.find(
            (link) => link.getAttribute("href") === window.location.hash,
        )
        ?? links.find(
            (link) => link.getAttribute("aria-current") === "true",
        )
        ?? links[0];

    const positionIndicator = () => {
        if (!indicator || !activeLink) {
            return;
        }

        gsap.to(indicator, {
            duration: 0.35,
            ease: "power3.out",
            width: activeLink.offsetWidth,
            x: activeLink.offsetLeft,
        });
    };

    const setActiveLink = (link) => {
        if (!link || link === activeLink) {
            return;
        }

        links.forEach((candidate) => {
            candidate.setAttribute(
                "aria-current",
                candidate === link ? "true" : "false",
            );
        });

        activeLink = link;
        positionIndicator();
    };

    links.forEach((link) => {
        const handleClick = () => setActiveLink(link);

        link.addEventListener("click", handleClick);
        cleanup.push(() => {
            link.removeEventListener("click", handleClick);
        });
    });

    courses.forEach((course, index) => {
        const trigger = ScrollTrigger.create({
            trigger: course,
            start: "top 42%",
            end: "bottom 42%",
            onEnter: () => setActiveLink(links[index]),
            onEnterBack: () => setActiveLink(links[index]),
        });

        cleanup.push(() => trigger.kill());
    });

    positionIndicator();
    ScrollTrigger.addEventListener("refresh", positionIndicator);
    cleanup.push(() => {
        ScrollTrigger.removeEventListener("refresh", positionIndicator);
    });

    return () => {
        cleanup.forEach((callback) => callback());
    };
}

/**
 * Initialize the menu page's restrained progressive motion.
 */
function initializeMenuMotion(root, { desktop, reducedMotion }) {
    if (reducedMotion) {
        setMenuFinalStates(root);

        return () => {};
    }

    initializeHeroMotion(root, { desktop });

    const navigationCleanup = initializeMenuNavigation(root);

    root.querySelectorAll('[data-menu-motion="course"]').forEach((course) => {
        const heading = course.querySelectorAll(
            '[data-menu-motion="course-number"], [data-menu-motion="course-title"], [data-menu-motion="course-description"]',
        );
        const rule = course.querySelector(
            '[data-menu-motion="course-rule"]',
        );
        const cards = course.querySelectorAll(
            '[data-menu-motion="card"]',
        );

        gsap.set(heading, { autoAlpha: 0, y: 20 });
        gsap.set(cards, { autoAlpha: 0, y: revealDistance });

        if (rule) {
            gsap.set(rule, {
                autoAlpha: 1,
                scaleX: 0,
                transformOrigin: "left center",
            });
        }

        const timeline = gsap.timeline({
            scrollTrigger: {
                trigger: course,
                start: "top 78%",
                once: true,
            },
        });

        timeline
            .to(heading, {
                autoAlpha: 1,
                duration: 0.7,
                ease: "power3.out",
                stagger: 0.08,
                y: 0,
            })
            .to(
                rule,
                {
                    scaleX: 1,
                    duration: 0.5,
                    ease: "power3.out",
                },
                "<0.1",
            )
            .to(
                cards,
                {
                    autoAlpha: 1,
                    duration: 0.75,
                    ease: "power3.out",
                    stagger: 0.08,
                    y: 0,
                },
                "<0.1",
            );
    });

    const closingCta = root.querySelector(
        '[data-menu-motion="closing-cta"]',
    );

    if (closingCta) {
        revealTimeline(closingCta);
    }

    return navigationCleanup;
}

/**
 * Initialize shared homepage, gallery, contact, and content-page motion.
 */
function initializeGeneralMotion(root, { desktop, reducedMotion }) {
    if (reducedMotion) {
        gsap.set(
            root.querySelectorAll(
                "[data-gsap-reveal], [data-gsap=card], [data-gsap=panel]",
            ),
            {
                autoAlpha: 1,
                x: 0,
                y: 0,
            },
        );

        return () => {};
    }

    initializeHeroMotion(root, { desktop });

    root.querySelectorAll('[data-gsap="section"]').forEach((section) => {
        revealTimeline(section);
    });

    root.querySelectorAll('[data-gsap="image"]').forEach((image) => {
        revealImage(image);
    });

    root.querySelectorAll('[data-gsap="parallax"]').forEach((image) => {
        addParallax(image, desktop ? 4 : 2);
    });

    root.querySelectorAll('[data-gsap="frame"]').forEach((frame) => {
        revealFrame(frame);
    });

    const menu = root.querySelector('[data-gsap="menu"]');

    if (menu) {
        const cards = menu.querySelectorAll('[data-gsap="card"]');

        gsap.set(cards, { autoAlpha: 0, y: revealDistance });

        gsap.timeline({
            scrollTrigger: {
                trigger: menu,
                start: "top 78%",
                once: true,
            },
        }).to(cards, {
            autoAlpha: 1,
            duration: 0.8,
            ease: "power3.out",
            stagger: 0.12,
            y: 0,
        });
    }

    root.querySelectorAll('[data-gsap="panel"]').forEach((panel, index) => {
        const offset = desktop
            ? { x: index % 2 === 0 ? -32 : 32, y: 0 }
            : { x: 0, y: revealDistance };

        gsap.fromTo(
            panel,
            {
                autoAlpha: 0,
                ...offset,
            },
            {
                autoAlpha: 1,
                duration: 0.9,
                ease: "power3.out",
                scrollTrigger: {
                    trigger: panel.parentElement ?? panel,
                    start: "top 78%",
                    once: true,
                },
                x: 0,
                y: 0,
            },
        );
    });

    return () => {};
}

/**
 * Initialize progressive public animations and return their cleanup callback.
 */
export function initPublicAnimations(
    root = document.querySelector("[data-home-motion]"),
) {
    if (!root) {
        return () => {};
    }

    const media = gsap.matchMedia();

    media.add(
        {
            reducedMotion: "(prefers-reduced-motion: reduce)",
            desktop: "(min-width: 1024px)",
        },
        (context) => {
            const {
                desktop = false,
                reducedMotion = false,
            } = context.conditions;

            if (root.dataset.publicMotion === "menu") {
                return initializeMenuMotion(root, {
                    desktop,
                    reducedMotion,
                });
            }

            return initializeGeneralMotion(root, {
                desktop,
                reducedMotion,
            });
        },
    );

    const cleanup = () => media.revert();

    if (import.meta.hot) {
        import.meta.hot.dispose(cleanup);
    }

    return cleanup;
}
