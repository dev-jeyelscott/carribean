import gsap from "gsap";
import { ScrollToPlugin } from "gsap/ScrollToPlugin";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollToPlugin, ScrollTrigger);

/**
 * Return the current shared-header height used for anchor offsets.
 */
function headerOffset() {
    const header = document.querySelector("header");

    return header instanceof HTMLElement
        ? header.getBoundingClientRect().height
        : 88;
}

/**
 * Return all menu sections currently rendered inside the page.
 */
function menuSections(root) {
    return [...root.querySelectorAll("[data-menu-section]")].filter(
        (section) => section instanceof HTMLElement,
    );
}

/**
 * Return all category links from desktop and mobile navigation.
 */
function categoryLinks(root) {
    return [...root.querySelectorAll("[data-menu-category-link]")].filter(
        (link) => link instanceof HTMLAnchorElement,
    );
}

/**
 * Center the active mobile category chip inside its horizontal scroller.
 */
function centerMobileCategoryLink(link, reducedMotion) {
    const scroller = link.closest("[data-menu-category-scroller]");

    if (!(scroller instanceof HTMLElement)) {
        return;
    }

    const desiredLeft =
        link.offsetLeft - scroller.clientWidth / 2 + link.clientWidth / 2;

    scroller.scrollTo({
        behavior: reducedMotion ? "auto" : "smooth",
        left: Math.max(0, desiredLeft),
    });
}

/**
 * Mark one category active across desktop and mobile navigation.
 */
function setActiveCategory(root, section, reducedMotion) {
    const expectedHash = `#${section.id}`;
    const links = categoryLinks(root);

    links.forEach((link) => {
        const isActive = link.getAttribute("href") === expectedHash;

        link.setAttribute("aria-current", isActive ? "true" : "false");

        if (isActive && link.dataset.menuNavigationPosition === "mobile") {
            centerMobileCategoryLink(link, reducedMotion);
        }
    });

    root.dataset.menuActiveCategory =
        section.dataset.menuCategory ?? section.id;
}

/**
 * Initialize category links and active-section ScrollTriggers.
 */
function initializeCategoryNavigation(root, reducedMotion) {
    const sections = menuSections(root);
    const cleanup = [];

    const handleCategoryClick = (event) => {
        const link = event.target.closest("[data-menu-category-link]");

        if (!(link instanceof HTMLAnchorElement)) {
            return;
        }

        const target = document.getElementById(link.hash.slice(1));

        if (!(target instanceof HTMLElement)) {
            return;
        }

        event.preventDefault();

        setActiveCategory(root, target, reducedMotion);

        window.history.replaceState(null, "", `#${target.id}`);

        if (reducedMotion) {
            window.scrollTo({
                behavior: "auto",
                top:
                    target.getBoundingClientRect().top +
                    window.scrollY -
                    headerOffset() -
                    16,
            });

            return;
        }

        gsap.to(window, {
            duration: 0.65,
            ease: "power3.out",
            overwrite: "auto",
            scrollTo: {
                autoKill: true,
                offsetY: headerOffset() + 16,
                y: target,
            },
        });
    };

    root.addEventListener("click", handleCategoryClick);

    cleanup.push(() => {
        root.removeEventListener("click", handleCategoryClick);
    });

    sections.forEach((section) => {
        const trigger = ScrollTrigger.create({
            trigger: section,
            start: "top 46%",
            end: "bottom 46%",
            onEnter: () => {
                setActiveCategory(root, section, reducedMotion);
            },
            onEnterBack: () => {
                setActiveCategory(root, section, reducedMotion);
            },
        });

        cleanup.push(() => trigger.kill());
    });

    const requestedSection = window.location.hash
        ? document.getElementById(window.location.hash.slice(1))
        : null;

    if (requestedSection instanceof HTMLElement) {
        setActiveCategory(root, requestedSection, reducedMotion);
    } else if (sections[0]) {
        setActiveCategory(root, sections[0], reducedMotion);
    }

    return () => {
        cleanup.forEach((callback) => callback());
    };
}

/**
 * Return the horizontal distance between neighboring carousel cards.
 */
function carouselStep(track, items) {
    if (items.length < 2) {
        return items[0]?.getBoundingClientRect().width ?? track.clientWidth;
    }

    return Math.abs(items[1].offsetLeft - items[0].offsetLeft);
}

/**
 * Calculate how many complete cards fit inside one carousel viewport.
 */
function visibleCarouselCount(track, items) {
    const step = carouselStep(track, items);

    if (step <= 0) {
        return 1;
    }

    return Math.max(1, Math.round(track.clientWidth / step));
}

/**
 * Initialize controls, keyboard input, status, and progress for one carousel.
 */
function initializeCarousel(carousel, reducedMotion) {
    const track = carousel.querySelector("[data-menu-carousel-track]");

    const previousButton = carousel
        .closest("[data-menu-section]")
        ?.querySelector("[data-menu-carousel-previous]");

    const nextButton = carousel
        .closest("[data-menu-section]")
        ?.querySelector("[data-menu-carousel-next]");

    const status = carousel
        .closest("[data-menu-section]")
        ?.querySelector("[data-menu-carousel-status]");

    const progress = carousel.querySelector("[data-menu-carousel-progress]");

    if (!(track instanceof HTMLElement)) {
        return () => {};
    }

    const items = [
        ...track.querySelectorAll("[data-menu-carousel-item]"),
    ].filter((item) => item instanceof HTMLElement);

    if (items.length === 0) {
        return () => {};
    }

    let updateFrame = null;

    /**
     * Return the card index closest to the current scroll position.
     */
    const currentIndex = () => {
        const step = carouselStep(track, items);

        if (step <= 0) {
            return 0;
        }

        return Math.max(
            0,
            Math.min(items.length - 1, Math.round(track.scrollLeft / step)),
        );
    };

    /**
     * Return the current responsive carousel page.
     *
     * One page equals the number of complete cards visible in the current
     * carousel viewport.
     */
    const currentPage = () => {
        const visibleCount = visibleCarouselCount(track, items);

        return Math.round(currentIndex() / visibleCount);
    };

    /**
     * Synchronize controls, readable status, and progress with scroll position.
     */
    const updateState = () => {
        const index = currentIndex();
        const visibleCount = visibleCarouselCount(track, items);

        const lastVisible = Math.min(items.length, index + visibleCount);

        const maximumScroll = track.scrollWidth - track.clientWidth;

        if (previousButton instanceof HTMLButtonElement) {
            previousButton.disabled = track.scrollLeft <= 2;
        }

        if (nextButton instanceof HTMLButtonElement) {
            nextButton.disabled = track.scrollLeft >= maximumScroll - 2;
        }

        if (status instanceof HTMLElement) {
            status.textContent = `${index + 1}–${lastVisible} of ${items.length}`;
        }

        if (progress instanceof HTMLElement) {
            const progressValue =
                items.length <= visibleCount
                    ? 1
                    : index / Math.max(1, items.length - visibleCount);

            progress.style.transform = `scaleX(${Math.max(0.08, progressValue)})`;
        }
    };

    /**
     * Schedule one state update per animation frame.
     */
    const scheduleUpdate = () => {
        if (updateFrame !== null) {
            cancelAnimationFrame(updateFrame);
        }

        updateFrame = requestAnimationFrame(() => {
            updateFrame = null;
            updateState();
        });
    };

    /**
     * Animate the track to one card index.
     */
    const scrollToIndex = (index) => {
        const visibleCount = visibleCarouselCount(track, items);

        const maximumStartIndex = Math.max(0, items.length - visibleCount);

        const targetIndex = Math.max(0, Math.min(maximumStartIndex, index));

        const target = items[targetIndex];

        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (reducedMotion) {
            track.scrollTo({
                behavior: "auto",
                left: target.offsetLeft,
            });

            scheduleUpdate();

            return;
        }

        gsap.to(track, {
            duration: 0.55,
            ease: "power3.out",
            overwrite: "auto",
            scrollTo: {
                x: target.offsetLeft,
            },
            onUpdate: scheduleUpdate,
            onComplete: scheduleUpdate,
        });
    };

    /**
     * Move to one responsive carousel page.
     *
     * Desktop advances four cards, while narrower layouts advance by however
     * many complete cards currently fit inside the carousel.
     */
    const scrollToPage = (page) => {
        const visibleCount = visibleCarouselCount(track, items);

        const maximumPage = Math.max(
            0,
            Math.ceil(items.length / visibleCount) - 1,
        );

        const targetPage = Math.max(0, Math.min(maximumPage, page));

        scrollToIndex(targetPage * visibleCount);
    };

    const handlePrevious = () => {
        scrollToPage(currentPage() - 1);
    };

    const handleNext = () => {
        scrollToPage(currentPage() + 1);
    };

    const handleKeydown = (event) => {
        if (event.key === "ArrowLeft") {
            event.preventDefault();
            handlePrevious();

            return;
        }

        if (event.key === "ArrowRight") {
            event.preventDefault();
            handleNext();

            return;
        }

        if (event.key === "Home") {
            event.preventDefault();
            scrollToIndex(0);

            return;
        }

        if (event.key === "End") {
            event.preventDefault();
            scrollToPage(Number.MAX_SAFE_INTEGER);
        }
    };

    /**
     * Preserve normal vertical page scrolling while supporting horizontal
     * trackpads and Shift + mouse-wheel navigation.
     */
    const handleWheel = (event) => {
        const usesHorizontalTrackpad =
            Math.abs(event.deltaX) > Math.abs(event.deltaY);

        const horizontalDelta = usesHorizontalTrackpad
            ? event.deltaX
            : event.shiftKey
              ? event.deltaY
              : 0;

        if (horizontalDelta === 0) {
            return;
        }

        const maximumScroll = track.scrollWidth - track.clientWidth;

        const canMoveBackward = horizontalDelta < 0 && track.scrollLeft > 0;

        const canMoveForward =
            horizontalDelta > 0 && track.scrollLeft < maximumScroll;

        if (!canMoveBackward && !canMoveForward) {
            return;
        }

        event.preventDefault();

        track.scrollLeft += horizontalDelta;

        scheduleUpdate();
    };

    previousButton?.addEventListener("click", handlePrevious);

    nextButton?.addEventListener("click", handleNext);

    track.addEventListener("keydown", handleKeydown);

    track.addEventListener("scroll", scheduleUpdate, {
        passive: true,
    });

    track.addEventListener("wheel", handleWheel, {
        passive: false,
    });

    const resizeObserver = new ResizeObserver(scheduleUpdate);

    resizeObserver.observe(track);

    updateState();

    carousel.dataset.menuCarouselReady = "true";

    return () => {
        previousButton?.removeEventListener("click", handlePrevious);

        nextButton?.removeEventListener("click", handleNext);

        track.removeEventListener("keydown", handleKeydown);

        track.removeEventListener("scroll", scheduleUpdate);

        track.removeEventListener("wheel", handleWheel);

        resizeObserver.disconnect();

        if (updateFrame !== null) {
            cancelAnimationFrame(updateFrame);
        }

        delete carousel.dataset.menuCarouselReady;
    };
}

/**
 * Initialize every native horizontal carousel.
 */
function initializeCarousels(root, reducedMotion) {
    const cleanup = [];

    root.querySelectorAll("[data-menu-carousel]").forEach((carousel) => {
        cleanup.push(initializeCarousel(carousel, reducedMotion));
    });

    return () => {
        cleanup.forEach((callback) => callback());
    };
}

/**
 * Reveal the hero and category content only after the GSAP module succeeds.
 */
function initializeMenuMotion(root, reducedMotion) {
    if (reducedMotion) {
        gsap.set(
            root.querySelectorAll(
                "[data-menu-hero-item], [data-menu-hero-depth], [data-menu-section-heading], [data-menu-section-rule], [data-menu-carousel-item], [data-menu-carousel-controls]",
            ),
            {
                autoAlpha: 1,
                clearProps: "transform",
            },
        );

        return () => {};
    }

    const cleanup = [];
    const hero = root.querySelector("[data-menu-hero]");

    const heroItems = root.querySelectorAll("[data-menu-hero-item]");

    const heroDepthLayers = root.querySelectorAll("[data-menu-hero-depth]");

    if (hero instanceof HTMLElement && heroItems.length > 0) {
        /*
         * Reveal the hero once when it enters the viewport.
         *
         * The page normally starts inside the hero, but attaching the entrance
         * timeline to ScrollTrigger also handles restored scroll positions,
         * browser back navigation, and direct page reloads consistently.
         */
        gsap.set(heroItems, {
            autoAlpha: 0,
            y: 24,
        });

        const heroEntranceTimeline = gsap.timeline({
            defaults: {
                ease: "power4.out",
            },
            scrollTrigger: {
                trigger: hero,
                start: "top 88%",
                once: true,
            },
        });

        heroEntranceTimeline.to(heroItems, {
            autoAlpha: 1,
            duration: 1,
            stagger: 0.11,
            y: 0,
        });

        /*
         * Apply restrained scroll-linked depth while the visitor leaves the hero.
         *
         * The hero remains in normal document flow. Only transform and opacity are
         * animated, preventing layout shifts and avoiding scroll hijacking.
         */
        const heroScrollTimeline = gsap.timeline({
            scrollTrigger: {
                trigger: hero,
                start: "top top",
                end: "bottom top",
                scrub: 0.65,
                invalidateOnRefresh: true,
            },
        });

        heroScrollTimeline
            .to(
                heroItems,
                {
                    autoAlpha: 0.4,
                    ease: "none",
                    stagger: 0.015,
                    yPercent: -10,
                },
                0,
            )
            .to(
                heroDepthLayers,
                {
                    ease: "none",
                    scale: 1.06,
                    yPercent: 18,
                },
                0,
            );

        hero.dataset.menuHeroScrollTrigger = "ready";

        cleanup.push(() => {
            heroEntranceTimeline.scrollTrigger?.kill();
            heroEntranceTimeline.kill();

            heroScrollTimeline.scrollTrigger?.kill();
            heroScrollTimeline.kill();

            delete hero.dataset.menuHeroScrollTrigger;
        });
    }

    menuSections(root).forEach((section) => {
        const heading = section.querySelector("[data-menu-section-heading]");

        const rule = section.querySelector("[data-menu-section-rule]");

        const controls = section.querySelector("[data-menu-carousel-controls]");

        const cards = [
            ...section.querySelectorAll("[data-menu-carousel-item]"),
        ].slice(0, 6);

        const headingElements = heading
            ? [...heading.querySelectorAll("p, h2")].slice(0, 3)
            : [];

        gsap.set(headingElements, {
            autoAlpha: 0,
            y: 20,
        });

        gsap.set(cards, {
            autoAlpha: 0,
            y: 28,
        });

        if (controls) {
            gsap.set(controls, {
                autoAlpha: 0,
                y: 14,
            });
        }

        if (rule) {
            gsap.set(rule, {
                scaleX: 0,
                transformOrigin: "left center",
            });
        }

        const timeline = gsap.timeline({
            scrollTrigger: {
                trigger: section,
                start: "top 72%",
                once: true,
            },
        });

        timeline
            .to(headingElements, {
                autoAlpha: 1,
                duration: 0.7,
                ease: "power3.out",
                stagger: 0.08,
                y: 0,
            })
            .to(
                rule,
                {
                    duration: 0.5,
                    ease: "power3.out",
                    scaleX: 1,
                },
                "<0.08",
            )
            .to(
                controls,
                {
                    autoAlpha: 1,
                    duration: 0.45,
                    ease: "power3.out",
                    y: 0,
                },
                "<0.1",
            )
            .to(
                cards,
                {
                    autoAlpha: 1,
                    duration: 0.65,
                    ease: "power3.out",
                    stagger: 0.07,
                    y: 0,
                },
                "<0.05",
            );

        cleanup.push(() => {
            timeline.scrollTrigger?.kill();
            timeline.kill();
        });
    });

    return () => {
        cleanup.forEach((callback) => callback());
    };
}

/**
 * Animate and manage the reusable native product dialog.
 */
function initializeProductModal(root, reducedMotion) {
    let opener = null;
    let activeTimeline = null;
    let isClosing = false;

    /**
     * Return the latest dialog after Livewire morphing.
     */
    const currentDialog = () => root.querySelector("[data-product-modal]");

    /**
     * Ask the Livewire component to close and reset validation state.
     */
    const requestClose = (dialog) => {
        const closeAction = dialog.querySelector(
            "[data-product-modal-close-action]",
        );

        if (closeAction instanceof HTMLButtonElement) {
            closeAction.click();
        }
    };

    /**
     * Bind native cancel and backdrop behavior to the current dialog.
     */
    const bindDialogEvents = (dialog) => {
        if (dialog.dataset.productModalBound === "true") {
            return;
        }

        dialog.dataset.productModalBound = "true";

        dialog.addEventListener("cancel", (event) => {
            event.preventDefault();
            requestClose(dialog);
        });

        dialog.addEventListener("click", (event) => {
            if (event.target === dialog) {
                requestClose(dialog);
            }
        });
    };

    /**
     * Open the dialog and reveal its layered content.
     */
    const handleOpen = () => {
        const dialog = currentDialog();

        if (!(dialog instanceof HTMLDialogElement)) {
            return;
        }

        bindDialogEvents(dialog);

        opener = document.activeElement;
        isClosing = false;

        if (!dialog.open) {
            dialog.showModal();
        }

        document.documentElement.classList.add("menu-modal-open");

        const panel = dialog.querySelector("[data-product-modal-panel]");

        const image = dialog.querySelector("[data-product-modal-image]");

        const copy = dialog.querySelector("[data-product-modal-copy]");

        const options = dialog.querySelector("[data-product-modal-options]");

        const footer = dialog.querySelector("[data-product-modal-footer]");

        const closeAction = dialog.querySelector(
            "[data-product-modal-close-action]",
        );

        activeTimeline?.kill();

        if (reducedMotion) {
            gsap.set([panel, image, copy, options, footer], {
                autoAlpha: 1,
                clearProps: "transform",
            });
        } else {
            gsap.set(panel, {
                autoAlpha: 0,
                scale: 0.96,
                y: 18,
            });

            gsap.set([image, copy, options, footer], {
                autoAlpha: 0,
                y: 18,
            });

            activeTimeline = gsap.timeline({
                defaults: {
                    ease: "power3.out",
                },
            });

            activeTimeline
                .to(panel, {
                    autoAlpha: 1,
                    duration: 0.4,
                    scale: 1,
                    y: 0,
                })
                .to(
                    image,
                    {
                        autoAlpha: 1,
                        duration: 0.55,
                        y: 0,
                    },
                    "<0.05",
                )
                .to(
                    copy,
                    {
                        autoAlpha: 1,
                        duration: 0.45,
                        y: 0,
                    },
                    "<0.08",
                )
                .to(
                    options,
                    {
                        autoAlpha: 1,
                        duration: 0.45,
                        y: 0,
                    },
                    "<0.08",
                )
                .to(
                    footer,
                    {
                        autoAlpha: 1,
                        duration: 0.4,
                        y: 0,
                    },
                    "<0.08",
                );
        }

        requestAnimationFrame(() => {
            if (closeAction instanceof HTMLElement) {
                closeAction.focus({
                    preventScroll: true,
                });
            }
        });
    };

    /**
     * Animate the current modal out before closing the native dialog.
     */
    const handleClose = () => {
        const dialog = currentDialog();

        if (
            !(dialog instanceof HTMLDialogElement) ||
            !dialog.open ||
            isClosing
        ) {
            return;
        }

        isClosing = true;

        const panel = dialog.querySelector("[data-product-modal-panel]");

        const finishClose = () => {
            if (dialog.open) {
                dialog.close();
            }

            document.documentElement.classList.remove("menu-modal-open");

            isClosing = false;

            if (opener instanceof HTMLElement) {
                opener.focus({
                    preventScroll: true,
                });
            }

            opener = null;
        };

        activeTimeline?.kill();

        if (reducedMotion || !panel) {
            finishClose();

            return;
        }

        activeTimeline = gsap.timeline({
            onComplete: finishClose,
        });

        activeTimeline.to(panel, {
            autoAlpha: 0,
            duration: 0.22,
            ease: "power2.in",
            scale: 0.985,
            y: 10,
        });
    };

    /**
     * Apply a restrained error shake without moving the page backdrop.
     */
    const handleError = () => {
        if (reducedMotion) {
            return;
        }

        const panel = currentDialog()?.querySelector(
            "[data-product-modal-panel]",
        );

        if (!(panel instanceof HTMLElement)) {
            return;
        }

        gsap.fromTo(
            panel,
            {
                x: -6,
            },
            {
                duration: 0.08,
                ease: "power1.inOut",
                repeat: 3,
                x: 0,
                yoyo: true,
            },
        );
    };

    window.addEventListener("product-modal-open", handleOpen);

    window.addEventListener("product-modal-close", handleClose);

    window.addEventListener("product-modal-error", handleError);

    return () => {
        activeTimeline?.kill();

        window.removeEventListener("product-modal-open", handleOpen);

        window.removeEventListener("product-modal-close", handleClose);

        window.removeEventListener("product-modal-error", handleError);

        document.documentElement.classList.remove("menu-modal-open");
    };
}

/**
 * Initialize the complete progressive menu experience.
 */
export function initMenuExperience(
    root = document.querySelector("[data-menu-page]"),
) {
    if (!(root instanceof HTMLElement)) {
        return () => {};
    }

    root.dataset.menuExperience = "loading";

    const media = gsap.matchMedia();

    let refreshFrame = null;

    /**
     * Recalculate ScrollTrigger positions after layout-affecting resources
     * finish loading or the active motion preference changes.
     */
    const refresh = () => {
        if (refreshFrame !== null) {
            window.cancelAnimationFrame(refreshFrame);
        }

        refreshFrame = window.requestAnimationFrame(() => {
            refreshFrame = null;

            ScrollTrigger.refresh(true);
        });
    };

    media.add(
        {
            motionAllowed: "(prefers-reduced-motion: no-preference)",
            reducedMotion: "(prefers-reduced-motion: reduce)",
        },
        (context) => {
            const reducedMotion = context.conditions?.reducedMotion === true;

            const cleanup = [
                initializeCategoryNavigation(root, reducedMotion),
                initializeCarousels(root, reducedMotion),
                initializeMenuMotion(root, reducedMotion),
                initializeProductModal(root, reducedMotion),
            ];

            root.dataset.menuExperience = "ready";

            refresh();

            return () => {
                cleanup.reverse().forEach((callback) => callback());

                root.dataset.menuExperience = "loading";
            };
        },
    );

    if (document.readyState === "complete") {
        refresh();
    } else {
        window.addEventListener("load", refresh, {
            once: true,
        });
    }

    if (document.fonts) {
        document.fonts.ready.then(refresh).catch(() => {});
    }

    /**
     * Remove menu-owned listeners, animations, and ScrollTriggers.
     */
    const cleanup = () => {
        window.removeEventListener("load", refresh);

        if (refreshFrame !== null) {
            window.cancelAnimationFrame(refreshFrame);
            refreshFrame = null;
        }

        media.revert();

        ScrollTrigger.getAll().forEach((trigger) => {
            if (root.contains(trigger.trigger) || trigger.trigger === root) {
                trigger.kill();
            }
        });

        delete root.dataset.menuExperience;
    };

    if (import.meta.hot) {
        import.meta.hot.dispose(cleanup);
    }

    return cleanup;
}
