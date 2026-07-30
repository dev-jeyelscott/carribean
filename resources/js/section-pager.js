import gsap from "gsap";
import { ScrollToPlugin } from "gsap/ScrollToPlugin";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollToPlugin, ScrollTrigger);

const reducedMotionQuery = "(prefers-reduced-motion: reduce)";

/**
 * Return the compact public-header offset for the current viewport.
 */
function getHeaderOffset() {
    return window.matchMedia("(max-width: 1023px)").matches ? 76 : 80;
}

/**
 * Safely extract one section identifier from a same-page hash link.
 */
function getSectionId(href) {
    if (typeof href !== "string" || !href.startsWith("#")) {
        return null;
    }

    try {
        return decodeURIComponent(href.slice(1));
    } catch {
        return href.slice(1);
    }
}

/**
 * Resolve the pager links and their corresponding document sections.
 */
function getEntries(root) {
    return [...root.querySelectorAll("[data-section-pager-link]")]
        .map((link, index) => {
            if (!(link instanceof HTMLAnchorElement)) {
                return null;
            }

            const sectionId = getSectionId(link.getAttribute("href"));
            const section = sectionId
                ? document.getElementById(sectionId)
                : null;

            if (!(section instanceof HTMLElement)) {
                return null;
            }

            return {
                index,
                link,
                section,
            };
        })
        .filter((entry) => entry !== null);
}

/**
 * Set the active pager link and expose the active section for diagnostics.
 */
function setActiveEntry(root, entries, activeEntry) {
    entries.forEach((entry) => {
        entry.link.setAttribute(
            "aria-current",
            entry === activeEntry ? "location" : "false",
        );
    });

    root.dataset.sectionPagerActive = activeEntry.section.id;
}

/**
 * Calculate an exact document destination beneath the public header.
 */
function getSectionScrollPosition(entry) {
    const sectionTop =
        window.scrollY + entry.section.getBoundingClientRect().top;

    const offset = entry.index === 0 ? 0 : getHeaderOffset();

    return Math.max(0, Math.round(sectionTop - offset));
}

/**
 * Initialize accessible section navigation and section-level ScrollTriggers.
 */
export function initSectionPager(
    root = document.querySelector(
        '[data-section-pager][data-section-pager-enhancer="shared"]',
    ),
) {
    if (!(root instanceof HTMLElement)) {
        return () => {};
    }

    const entries = getEntries(root);

    if (entries.length === 0) {
        root.dataset.sectionPagerState = "unavailable";

        return () => {};
    }

    const context = root.dataset.sectionPagerContext ?? "page";
    const showMarkers = new URLSearchParams(window.location.search).has(
        "debug-scroll",
    );

    let activeTween = null;
    const media = gsap.matchMedia();

    root.dataset.sectionPagerState = "initializing";

    media.add(
        {
            reducedMotion: reducedMotionQuery,
        },
        (mediaContext) => {
            const { reducedMotion = false } =
                mediaContext.conditions ?? {};

            const cleanupCallbacks = [];
            let disposed = false;

            /**
             * Navigate to one section while keeping hash, active state, and
             * ScrollTrigger position synchronized.
             */
            const navigateToEntry = (
                entry,
                {
                    updateHistory = false,
                } = {},
            ) => {
                activeTween?.kill();
                activeTween = null;

                setActiveEntry(root, entries, entry);

                if (updateHistory) {
                    const nextHash = `#${entry.section.id}`;

                    if (window.location.hash !== nextHash) {
                        window.history.pushState(null, "", nextHash);
                    }
                }

                const destination = getSectionScrollPosition(entry);

                if (reducedMotion) {
                    window.scrollTo({
                        behavior: "auto",
                        left: 0,
                        top: destination,
                    });

                    ScrollTrigger.update();

                    return;
                }

                root.dataset.sectionPagerState = "navigating";

                activeTween = gsap.to(window, {
                    duration: 0.55,
                    ease: "power3.out",
                    overwrite: "auto",
                    scrollTo: {
                        autoKill: true,
                        y: destination,
                    },
                    onComplete: () => {
                        activeTween = null;
                        root.dataset.sectionPagerState = "ready";
                        ScrollTrigger.update();
                    },
                    onInterrupt: () => {
                        activeTween = null;
                        root.dataset.sectionPagerState = "ready";
                    },
                });
            };

            entries.forEach((entry) => {
                /**
                 * Handle deliberate pager activation without intercepting
                 * normal wheel, keyboard, or touch scrolling.
                 */
                const handleClick = (event) => {
                    event.preventDefault();

                    navigateToEntry(entry, {
                        updateHistory: true,
                    });
                };

                entry.link.addEventListener("click", handleClick);

                cleanupCallbacks.push(() => {
                    entry.link.removeEventListener("click", handleClick);
                });
            });

            /*
             * Create one ScrollTrigger for every complete page section. These
             * triggers update only the pager state; visual section animations
             * remain owned by the Gallery experience module.
             */
            const sectionTriggers = entries.map((entry) =>
                ScrollTrigger.create({
                    id: `section-pager-${context}-${entry.section.id}`,
                    trigger: entry.section,
                    start:
                        entry.index === 0
                            ? "top top"
                            : "top 55%",
                    end:
                        entry.index === entries.length - 1
                            ? "bottom bottom"
                            : "bottom 45%",
                    invalidateOnRefresh: true,
                    markers: showMarkers,
                    onEnter: () => {
                        setActiveEntry(root, entries, entry);
                    },
                    onEnterBack: () => {
                        setActiveEntry(root, entries, entry);
                    },
                }),
            );

            root.dataset.sectionPagerTriggerCount = String(
                sectionTriggers.length,
            );

            cleanupCallbacks.push(() => {
                sectionTriggers.forEach((trigger) => trigger.kill());
            });

            /**
             * Restore the correct section when browser history changes.
             */
            const handlePopState = () => {
                const requestedId = getSectionId(window.location.hash);
                const requestedEntry = entries.find(
                    (entry) => entry.section.id === requestedId,
                );

                if (requestedEntry) {
                    navigateToEntry(requestedEntry);
                }
            };

            window.addEventListener("popstate", handlePopState);

            cleanupCallbacks.push(() => {
                window.removeEventListener("popstate", handlePopState);
            });

            const requestedId = getSectionId(window.location.hash);

            const initialEntry =
                entries.find(
                    (entry) => entry.section.id === requestedId,
                )
                ?? entries.find(
                    (entry) =>
                        entry.link.getAttribute("aria-current")
                        === "location",
                )
                ?? entries[0];

            setActiveEntry(root, entries, initialEntry);

            /**
             * Recalculate every section boundary after layout-affecting
             * resources finish loading.
             */
            const refreshTriggers = () => {
                if (!disposed) {
                    ScrollTrigger.refresh();
                }
            };

            const refreshFrame =
                window.requestAnimationFrame(refreshTriggers);

            window.addEventListener("load", refreshTriggers, {
                once: true,
            });

            document.fonts?.ready
                ?.then(refreshTriggers)
                .catch(() => {});

            cleanupCallbacks.push(() => {
                disposed = true;

                window.cancelAnimationFrame(refreshFrame);
                window.removeEventListener(
                    "load",
                    refreshTriggers,
                );
            });

            root.dataset.sectionPagerState = reducedMotion
                ? "reduced"
                : "ready";

            return () => {
                cleanupCallbacks
                    .reverse()
                    .forEach((callback) => callback());

                activeTween?.kill();
                activeTween = null;
            };
        },
    );

    /**
     * Remove all responsive contexts, triggers, and state attributes.
     */
    const cleanup = () => {
        media.revert();

        delete root.dataset.sectionPagerActive;
        delete root.dataset.sectionPagerTriggerCount;
        delete root.dataset.sectionPagerState;
    };

    if (import.meta.hot) {
        import.meta.hot.dispose(cleanup);
    }

    return cleanup;
}
