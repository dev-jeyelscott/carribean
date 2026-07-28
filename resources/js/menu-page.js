/**
 * Return all category links rendered in desktop and mobile navigation.
 */
function categoryLinks(root) {
    return [...root.querySelectorAll("[data-menu-category-link]")];
}

/**
 * Return all category sections currently rendered by Livewire.
 */
function categorySections(root) {
    return [...root.querySelectorAll("[data-menu-section]")];
}

/**
 * Mark one category active across desktop and mobile navigation.
 */
function setActiveCategory(root, sectionId, reducedMotion) {
    const expectedHash = `#${sectionId}`;
    const links = categoryLinks(root);

    links.forEach((link) => {
        link.setAttribute(
            "aria-current",
            link.getAttribute("href") === expectedHash
                ? "true"
                : "false",
        );
    });

    const mobileLink = links.find(
        (link) =>
            link.dataset.menuNavigationPosition === "mobile"
            && link.getAttribute("href") === expectedHash,
    );

    const scroller = mobileLink?.closest(
        "[data-menu-category-scroller]",
    );

    if (
        !(mobileLink instanceof HTMLElement)
        || !(scroller instanceof HTMLElement)
    ) {
        return;
    }

    const desiredLeft =
        mobileLink.offsetLeft
        - scroller.clientWidth / 2
        + mobileLink.clientWidth / 2;

    scroller.scrollTo({
        behavior: reducedMotion ? "auto" : "smooth",
        left: Math.max(0, desiredLeft),
    });
}

/**
 * Find the category section crossing the content marker below sticky UI.
 */
function findCurrentSection(root) {
    const sections = categorySections(root);

    if (!sections.length) {
        return null;
    }

    const header = document.querySelector("header");
    const mobileCategories = root.querySelector(
        "[data-menu-mobile-categories]",
    );

    const headerHeight =
        header instanceof HTMLElement
            ? header.getBoundingClientRect().height
            : 88;

    const mobileCategoryHeight =
        mobileCategories instanceof HTMLElement
        && window.getComputedStyle(mobileCategories).display !== "none"
            ? mobileCategories.getBoundingClientRect().height
            : 0;

    const marker = headerHeight + mobileCategoryHeight + 32;

    let activeSection = sections[0];

    sections.forEach((section) => {
        const rectangle = section.getBoundingClientRect();

        if (rectangle.top <= marker) {
            activeSection = section;
        }
    });

    return activeSection;
}

/**
 * Initialize anchor navigation and active-category observation.
 */
function initializeCategoryNavigation(root, reducedMotion) {
    let observer = null;
    let refreshFrame = null;

    const updateActiveCategory = () => {
        const section = findCurrentSection(root);

        if (!(section instanceof HTMLElement)) {
            return;
        }

        setActiveCategory(root, section.id, reducedMotion);
    };

    const refreshObserver = () => {
        observer?.disconnect();

        observer = new IntersectionObserver(
            updateActiveCategory,
            {
                root: null,
                rootMargin: "-7rem 0px -65% 0px",
                threshold: [0, 0.01, 0.25],
            },
        );

        categorySections(root).forEach((section) => {
            observer.observe(section);
        });

        updateActiveCategory();
    };

    const scheduleRefresh = () => {
        if (refreshFrame !== null) {
            cancelAnimationFrame(refreshFrame);
        }

        refreshFrame = requestAnimationFrame(() => {
            refreshFrame = null;
            refreshObserver();
        });
    };

    const handleCategoryClick = (event) => {
        const link = event.target.closest(
            "[data-menu-category-link]",
        );

        if (!(link instanceof HTMLAnchorElement)) {
            return;
        }

        const targetId = link.hash.slice(1);
        const target = document.getElementById(targetId);

        if (!(target instanceof HTMLElement)) {
            return;
        }

        event.preventDefault();

        target.scrollIntoView({
            behavior: reducedMotion ? "auto" : "smooth",
            block: "start",
        });

        window.history.replaceState(
            null,
            "",
            `#${target.id}`,
        );

        setActiveCategory(root, target.id, reducedMotion);
    };

    root.addEventListener("click", handleCategoryClick);
    window.addEventListener("resize", scheduleRefresh);

    refreshObserver();

    const requestedSection = window.location.hash
        ? document.getElementById(window.location.hash.slice(1))
        : null;

    if (requestedSection instanceof HTMLElement) {
        setActiveCategory(
            root,
            requestedSection.id,
            reducedMotion,
        );
    }

    return {
        cleanup() {
            observer?.disconnect();

            if (refreshFrame !== null) {
                cancelAnimationFrame(refreshFrame);
            }

            root.removeEventListener(
                "click",
                handleCategoryClick,
            );

            window.removeEventListener(
                "resize",
                scheduleRefresh,
            );
        },

        refresh: scheduleRefresh,
    };
}

/**
 * Animate only cards appended by a Livewire load-more request.
 */
function animateAddedCards(records, reducedMotion) {
    if (reducedMotion) {
        return;
    }

    const cards = new Set();

    records.forEach((record) => {
        record.addedNodes.forEach((node) => {
            if (!(node instanceof HTMLElement)) {
                return;
            }

            if (node.matches("[data-menu-card]")) {
                cards.add(node);
            }

            node.querySelectorAll("[data-menu-card]").forEach(
                (card) => cards.add(card),
            );
        });
    });

    cards.forEach((card) => {
        card.classList.add("is-menu-card-entering");

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                card.classList.remove(
                    "is-menu-card-entering",
                );
            });
        });
    });
}

/**
 * Automatically activate each newly rendered load-more button near the
 * viewport. The manual button remains available when a request fails.
 */
function initializeAutomaticLoading(root) {
    const observedButtons = new WeakSet();

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                const button = entry.target;

                if (
                    ! entry.isIntersecting
                    || !(button instanceof HTMLButtonElement)
                    || button.disabled
                ) {
                    return;
                }

                observer.unobserve(button);
                button.click();
            });
        },
        {
            root: null,
            rootMargin: "0px 0px 32rem 0px",
            threshold: 0,
        },
    );

    const observeButtons = () => {
        root.querySelectorAll("[data-menu-load-more]").forEach(
            (button) => {
                if (
                    !(button instanceof HTMLButtonElement)
                    || observedButtons.has(button)
                ) {
                    return;
                }

                observedButtons.add(button);
                observer.observe(button);
            },
        );
    };

    observeButtons();

    return {
        cleanup() {
            observer.disconnect();
        },

        refresh: observeButtons,
    };
}

/**
 * Initialize the progressive menu-page interactions.
 */
export function initMenuPage(
    root = document.querySelector("[data-menu-page]"),
) {
    if (!(root instanceof HTMLElement)) {
        return () => {};
    }

    const reducedMotion = window.matchMedia(
        "(prefers-reduced-motion: reduce)",
    ).matches;

    const navigation = initializeCategoryNavigation(
        root,
        reducedMotion,
    );

    const automaticLoading =
        initializeAutomaticLoading(root);

    const mutationObserver = new MutationObserver(
        (records) => {
            animateAddedCards(records, reducedMotion);
            automaticLoading.refresh();
            navigation.refresh();
        },
    );

    mutationObserver.observe(root, {
        childList: true,
        subtree: true,
    });

    root.dataset.menuNavigation = "ready";

    return () => {
        mutationObserver.disconnect();
        automaticLoading.cleanup();
        navigation.cleanup();

        delete root.dataset.menuNavigation;
    };
}
