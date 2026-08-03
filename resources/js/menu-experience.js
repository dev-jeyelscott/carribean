import gsap from "gsap";

const reducedMotionQuery = "(prefers-reduced-motion: reduce)";

/**
 * Return the current public-header height used by anchor navigation.
 */
function headerOffset() {
    const header = document.querySelector(
        "[data-public-header-shell] > header",
    );

    return header instanceof HTMLElement
        ? Math.ceil(header.getBoundingClientRect().height)
        : 88;
}

/**
 * Return every rendered menu category section.
 */
function menuSections(root) {
    return [...root.querySelectorAll("[data-menu-section]")].filter(
        (section) => section instanceof HTMLElement && section.id,
    );
}

/**
 * Return every desktop and mobile category link.
 */
function categoryLinks(root) {
    return [...root.querySelectorAll("[data-menu-category-link]")].filter(
        (link) => link instanceof HTMLAnchorElement,
    );
}

/**
 * Keep the active mobile category chip visible inside its scroller.
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

    categoryLinks(root).forEach((link) => {
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
 * Scroll to one category using the browser's native scrolling behavior.
 */
function scrollToCategory(section, reducedMotion) {
    const top =
        section.getBoundingClientRect().top +
        window.scrollY -
        headerOffset() -
        16;

    window.scrollTo({
        behavior: reducedMotion ? "auto" : "smooth",
        left: 0,
        top: Math.max(0, Math.round(top)),
    });
}

/**
 * Track the visible category with IntersectionObserver and native anchors.
 */
function initializeCategoryNavigation(root, reducedMotion) {
    const sections = menuSections(root);

    if (sections.length === 0) {
        return () => {};
    }

    const handleClick = (event) => {
        const target =
            event.target instanceof Element
                ? event.target.closest("[data-menu-category-link]")
                : null;

        if (!(target instanceof HTMLAnchorElement)) {
            return;
        }

        const section = document.getElementById(target.hash.slice(1));

        if (!(section instanceof HTMLElement)) {
            return;
        }

        event.preventDefault();
        setActiveCategory(root, section, reducedMotion);
        window.history.replaceState(null, "", `#${section.id}`);
        scrollToCategory(section, reducedMotion);
    };

    root.addEventListener("click", handleClick);

    const observer = new IntersectionObserver(
        (entries) => {
            const visibleEntry = entries
                .filter((entry) => entry.isIntersecting)
                .sort(
                    (left, right) =>
                        right.intersectionRatio - left.intersectionRatio,
                )[0];

            if (visibleEntry?.target instanceof HTMLElement) {
                setActiveCategory(root, visibleEntry.target, reducedMotion);
            }
        },
        {
            root: null,
            rootMargin: "-38% 0px -52% 0px",
            threshold: [0, 0.01, 0.25, 0.5],
        },
    );

    sections.forEach((section) => observer.observe(section));

    const requestedSection = window.location.hash
        ? document.getElementById(window.location.hash.slice(1))
        : null;

    setActiveCategory(
        root,
        requestedSection instanceof HTMLElement
            ? requestedSection
            : sections[0],
        reducedMotion,
    );

    root.dataset.menuCategoryNavigation = "native";

    return () => {
        observer.disconnect();
        root.removeEventListener("click", handleClick);
        delete root.dataset.menuCategoryNavigation;
        delete root.dataset.menuActiveCategory;
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
 * Return the number of complete cards visible in the carousel viewport.
 */
function visibleCarouselCount(track, items) {
    const step = carouselStep(track, items);

    return step <= 0
        ? 1
        : Math.max(1, Math.round(track.clientWidth / step));
}

/**
 * Initialize one native horizontal menu carousel.
 */
function initializeCarousel(carousel, reducedMotion) {
    const track = carousel.querySelector("[data-menu-carousel-track]");

    if (!(track instanceof HTMLElement)) {
        return () => {};
    }

    const section = carousel.closest("[data-menu-section]");
    const previousButton = section?.querySelector(
        "[data-menu-carousel-previous]",
    );
    const nextButton = section?.querySelector("[data-menu-carousel-next]");
    const status = section?.querySelector("[data-menu-carousel-status]");
    const progress = carousel.querySelector("[data-menu-carousel-progress]");
    const items = [
        ...track.querySelectorAll("[data-menu-carousel-item]"),
    ].filter((item) => item instanceof HTMLElement);

    if (items.length === 0) {
        return () => {};
    }

    let updateFrame = null;

    /**
     * Return the card index nearest the current native scroll position.
     */
    const currentIndex = () => {
        const step = carouselStep(track, items);

        return step <= 0
            ? 0
            : Math.max(
                  0,
                  Math.min(
                      items.length - 1,
                      Math.round(track.scrollLeft / step),
                  ),
              );
    };

    /**
     * Synchronize navigation buttons, status text, and progress.
     */
    const updateState = () => {
        const index = currentIndex();
        const visibleCount = visibleCarouselCount(track, items);
        const maximumScroll = track.scrollWidth - track.clientWidth;

        if (previousButton instanceof HTMLButtonElement) {
            previousButton.disabled = track.scrollLeft <= 2;
        }

        if (nextButton instanceof HTMLButtonElement) {
            nextButton.disabled = track.scrollLeft >= maximumScroll - 2;
        }

        if (status instanceof HTMLElement) {
            status.textContent = `${index + 1}–${Math.min(
                items.length,
                index + visibleCount,
            )} of ${items.length}`;
        }

        if (progress instanceof HTMLElement) {
            const progressValue =
                items.length <= visibleCount
                    ? 1
                    : index / Math.max(1, items.length - visibleCount);

            progress.style.transform = `scaleX(${Math.max(
                0.08,
                progressValue,
            )})`;
        }
    };

    /**
     * Schedule at most one carousel state update per animation frame.
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
     * Move to one responsive carousel page with native scrolling.
     */
    const movePage = (direction) => {
        const visibleCount = visibleCarouselCount(track, items);
        const currentPage = Math.round(currentIndex() / visibleCount);
        const maximumPage = Math.max(
            0,
            Math.ceil(items.length / visibleCount) - 1,
        );
        const targetPage = Math.max(
            0,
            Math.min(maximumPage, currentPage + direction),
        );
        const target = items[targetPage * visibleCount];

        if (!(target instanceof HTMLElement)) {
            return;
        }

        track.scrollTo({
            behavior: reducedMotion ? "auto" : "smooth",
            left: target.offsetLeft,
        });
    };

    const handlePrevious = () => movePage(-1);
    const handleNext = () => movePage(1);

    const handleKeydown = (event) => {
        if (event.key === "ArrowLeft") {
            event.preventDefault();
            handlePrevious();
        }

        if (event.key === "ArrowRight") {
            event.preventDefault();
            handleNext();
        }

        if (event.key === "Home") {
            event.preventDefault();
            track.scrollTo({
                behavior: reducedMotion ? "auto" : "smooth",
                left: 0,
            });
        }

        if (event.key === "End") {
            event.preventDefault();
            track.scrollTo({
                behavior: reducedMotion ? "auto" : "smooth",
                left: track.scrollWidth,
            });
        }
    };

    previousButton?.addEventListener("click", handlePrevious);
    nextButton?.addEventListener("click", handleNext);
    track.addEventListener("keydown", handleKeydown);
    track.addEventListener("scroll", scheduleUpdate, { passive: true });

    const resizeObserver = new ResizeObserver(scheduleUpdate);
    resizeObserver.observe(track);

    updateState();
    carousel.dataset.menuCarouselReady = "true";

    return () => {
        previousButton?.removeEventListener("click", handlePrevious);
        nextButton?.removeEventListener("click", handleNext);
        track.removeEventListener("keydown", handleKeydown);
        track.removeEventListener("scroll", scheduleUpdate);
        resizeObserver.disconnect();

        if (updateFrame !== null) {
            cancelAnimationFrame(updateFrame);
        }

        delete carousel.dataset.menuCarouselReady;
    };
}

/**
 * Initialize every native horizontal menu carousel.
 */
function initializeCarousels(root, reducedMotion) {
    const cleanups = [];

    root.querySelectorAll("[data-menu-carousel]").forEach((carousel) => {
        cleanups.push(initializeCarousel(carousel, reducedMotion));
    });

    return () => {
        cleanups.reverse().forEach((cleanup) => cleanup());
    };
}

/**
 * Animate and manage the reusable product dialog with GSAP Core only.
 */
function initializeProductModal(root, reducedMotion) {
    let opener = null;
    let activeTween = null;
    let isClosing = false;

    /**
     * Return the latest dialog after a Livewire DOM morph.
     */
    const currentDialog = () => root.querySelector("[data-product-modal]");

    /**
     * Trigger the Livewire close action for the current dialog.
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
     * Bind native cancel and backdrop behavior once per dialog instance.
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
     * Open the native dialog and animate its panel without scroll coupling.
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
        const closeAction = dialog.querySelector(
            "[data-product-modal-close-action]",
        );

        activeTween?.kill();

        if (panel instanceof HTMLElement) {
            if (reducedMotion) {
                gsap.set(panel, {
                    autoAlpha: 1,
                    clearProps: "transform",
                });
            } else {
                activeTween = gsap.fromTo(
                    panel,
                    {
                        autoAlpha: 0,
                        scale: 0.97,
                        y: 18,
                    },
                    {
                        autoAlpha: 1,
                        duration: 0.42,
                        ease: "power3.out",
                        scale: 1,
                        y: 0,
                    },
                );
            }
        }

        requestAnimationFrame(() => {
            if (closeAction instanceof HTMLElement) {
                closeAction.focus({ preventScroll: true });
            }
        });
    };

    /**
     * Animate the dialog panel out, then close the native dialog.
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
                opener.focus({ preventScroll: true });
            }

            opener = null;
        };

        activeTween?.kill();

        if (reducedMotion || !(panel instanceof HTMLElement)) {
            finishClose();

            return;
        }

        activeTween = gsap.to(panel, {
            autoAlpha: 0,
            duration: 0.22,
            ease: "power2.in",
            scale: 0.985,
            y: 10,
            onComplete: finishClose,
        });
    };

    /**
     * Shake the product panel after a validation error.
     */
    const handleError = () => {
        if (reducedMotion) {
            return;
        }

        const panel = currentDialog()?.querySelector(
            "[data-product-modal-panel]",
        );

        if (panel instanceof HTMLElement) {
            gsap.fromTo(
                panel,
                { x: -6 },
                {
                    duration: 0.08,
                    ease: "power1.inOut",
                    repeat: 3,
                    x: 0,
                    yoyo: true,
                },
            );
        }
    };

    window.addEventListener("product-modal-open", handleOpen);
    window.addEventListener("product-modal-close", handleClose);
    window.addEventListener("product-modal-error", handleError);

    return () => {
        activeTween?.kill();
        window.removeEventListener("product-modal-open", handleOpen);
        window.removeEventListener("product-modal-close", handleClose);
        window.removeEventListener("product-modal-error", handleError);
        document.documentElement.classList.remove("menu-modal-open");
    };
}

/**
 * Initialize all non-scroll-triggered Menu interactions.
 */
export function initMenuExperience(
    root = document.querySelector("[data-menu-page]"),
) {
    if (!(root instanceof HTMLElement)) {
        return () => {};
    }

    let activeCleanup = () => {};
    const mediaQuery = window.matchMedia(reducedMotionQuery);

    /**
     * Rebuild interaction behavior when the motion preference changes.
     */
    const initialize = () => {
        activeCleanup();

        const reducedMotion = mediaQuery.matches;
        const cleanups = [
            initializeCategoryNavigation(root, reducedMotion),
            initializeCarousels(root, reducedMotion),
            initializeProductModal(root, reducedMotion),
        ];

        root.dataset.menuExperience = "ready";

        activeCleanup = () => {
            cleanups.reverse().forEach((cleanup) => cleanup());
        };
    };

    const handleMotionPreferenceChange = () => initialize();

    mediaQuery.addEventListener("change", handleMotionPreferenceChange);
    initialize();

    const cleanup = () => {
        mediaQuery.removeEventListener("change", handleMotionPreferenceChange);
        activeCleanup();
        delete root.dataset.menuExperience;
    };

    if (import.meta.hot) {
        import.meta.hot.dispose(cleanup);
    }

    return cleanup;
}
