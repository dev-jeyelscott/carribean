const compactClass = "public-header-compact";
const compactThreshold = 24;

/**
 * Synchronize the shared public header with the current scroll position.
 */
function synchronizeHeaderState(header) {
    const isCompact = window.scrollY > compactThreshold;
    const currentState = header.dataset.publicHeaderState;

    if (
        (isCompact && currentState === "compact") ||
        (!isCompact && currentState === "expanded")
    ) {
        return;
    }

    header.classList.toggle(compactClass, isCompact);
    header.dataset.publicHeaderState = isCompact
        ? "compact"
        : "expanded";
}

/**
 * Initialize expanded and compact states for the shared public navigation.
 */
export function initPublicHeader(
    header = document.querySelector(
        "[data-public-header-shell] > header",
    ),
) {
    if (!(header instanceof HTMLElement)) {
        return () => {};
    }

    let animationFrame = null;

    /**
     * Apply the pending header state during the next browser render frame.
     */
    const updateHeader = () => {
        animationFrame = null;
        synchronizeHeaderState(header);
    };

    /**
     * Coalesce repeated scroll events into one visual update.
     */
    const scheduleUpdate = () => {
        if (animationFrame !== null) {
            return;
        }

        animationFrame = window.requestAnimationFrame(updateHeader);
    };

    synchronizeHeaderState(header);

    window.addEventListener("scroll", scheduleUpdate, {
        passive: true,
    });

    window.addEventListener("pageshow", scheduleUpdate);

    /**
     * Remove all listeners and temporary presentation state.
     */
    const cleanup = () => {
        window.removeEventListener("scroll", scheduleUpdate);
        window.removeEventListener("pageshow", scheduleUpdate);

        if (animationFrame !== null) {
            window.cancelAnimationFrame(animationFrame);
        }

        animationFrame = null;

        header.classList.remove(compactClass);
        delete header.dataset.publicHeaderState;
    };

    if (import.meta.hot) {
        import.meta.hot.dispose(cleanup);
    }

    return cleanup;
}
