import gsap from "gsap";
import { ScrollToPlugin } from "gsap/ScrollToPlugin";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollToPlugin, ScrollTrigger);

const gestureTolerance = 18;
const heroActivationDistance = 72;
const headerAlignmentTolerance = 8;

/**
 * Determine whether a wheel event represents browser zoom rather than
 * intentional vertical page navigation.
 */
function isModifiedZoomGesture(observer) {
    return observer.event instanceof WheelEvent
        && observer.event.ctrlKey;
}

/**
 * Determine whether the homepage hero is still positioned at the beginning
 * of the public page beneath the sticky header.
 */
function isHeroAtEntryPosition(hero, header) {
    const headerHeight = header instanceof HTMLElement
        ? header.getBoundingClientRect().height
        : 0;

    const heroTop = hero.getBoundingClientRect().top;

    return heroTop <= headerHeight + headerAlignmentTolerance
        && heroTop >= headerHeight - heroActivationDistance;
}

/**
 * Smoothly align the target section beneath the current sticky-header height
 * while allowing manual user scrolling to interrupt the transition.
 */
function createSectionScrollTween(target, header, onSettled) {
    const offsetY = header instanceof HTMLElement
        ? header.getBoundingClientRect().height
        : 0;

    return gsap.to(window, {
        duration: 0.8,
        ease: "power3.inOut",
        overwrite: "auto",
        scrollTo: {
            y: target,
            offsetY,
            autoKill: true,
            onAutoKill: onSettled,
        },
        onComplete: onSettled,
        onInterrupt: onSettled,
    });
}

/**
 * Advance from the homepage hero to the restaurant-highlight section after a
 * small desktop wheel or trackpad gesture.
 *
 * Mobile, reduced-motion, interactive-element, and non-hero scrolling remain
 * under the browser's native behavior.
 */
export function initHomepageScrollAdvance(
    root = document.querySelector("[data-home-motion]"),
) {
    if (!root) {
        return () => {};
    }

    const hero = root.querySelector("[data-public-hero]");
    const target = root.querySelector(
        'section[aria-label="Restaurant highlights"]',
    );
    const header = document.querySelector("header");

    if (!(hero instanceof HTMLElement) || !(target instanceof HTMLElement)) {
        return () => {};
    }

    const media = gsap.matchMedia();
    let activeTween = null;

    media.add(
        {
            desktop: "(min-width: 1024px)",
            reducedMotion: "(prefers-reduced-motion: reduce)",
        },
        (context) => {
            const {
                desktop = false,
                reducedMotion = false,
            } = context.conditions;

            if (!desktop || reducedMotion) {
                return () => {};
            }

            let isTransitioning = false;

            /**
             * Release the transition lock after the animation completes,
             * is interrupted, or is automatically cancelled by user input.
             */
            const settleTransition = () => {
                isTransitioning = false;
                activeTween = null;
            };

            const observer = ScrollTrigger.observe({
                target: hero,
                type: "wheel",
                tolerance: gestureTolerance,
                wheelSpeed: 0.8,
                ignore: [
                    "a",
                    "button",
                    "input",
                    "select",
                    "textarea",
                    "[role='dialog']",
                    "[data-scroll-advance-ignore]",
                ].join(", "),
                onDown: (self) => {
                    if (
                        isTransitioning
                        || isModifiedZoomGesture(self)
                        || !isHeroAtEntryPosition(hero, header)
                    ) {
                        return;
                    }

                    isTransitioning = true;

                    activeTween?.kill();

                    activeTween = createSectionScrollTween(
                        target,
                        header,
                        settleTransition,
                    );
                },
            });

            /**
             * Remove desktop observer state when the media query changes or
             * the module is disposed by Vite hot-module replacement.
             */
            return () => {
                activeTween?.kill();
                activeTween = null;
                observer.kill();
                isTransitioning = false;
            };
        },
    );

    /**
     * Remove all media-query and animation state created by this module.
     */
    const cleanup = () => {
        activeTween?.kill();
        activeTween = null;
        media.revert();
    };

    if (import.meta.hot) {
        import.meta.hot.dispose(cleanup);
    }

    return cleanup;
}
