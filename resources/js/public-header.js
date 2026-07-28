import "../css/public-header.css";

const compactClass = "public-header-compact";
const compactThreshold = 24;

/**
 * Synchronize the homepage header appearance with the current scroll position.
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
 * Initialize the compact homepage header when the viewport leaves the top.
 */
export function initPublicHeader(
    header = document.querySelector('body[data-page="home"] header'),
) {
    if (!(header instanceof HTMLElement)) {
        return () => {};
    }

    let animationFrame = null;

    /**
     * Apply one scroll-state update during the browser's next render frame.
     */
    const updateHeader = () => {
        animationFrame = null;
        synchronizeHeaderState(header);
    };

    /**
     * Coalesce repeated scroll events into one pending visual update.
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
     * Remove listeners and temporary header state.
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
