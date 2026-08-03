const swipeThreshold = 52;

/**
 * Return every currently rendered Gallery card.
 */
function galleryCards(root) {
    return [...root.querySelectorAll("[data-gallery-item]")].filter(
        (card) => card instanceof HTMLElement,
    );
}

/**
 * Return every current fullscreen Gallery opener.
 */
function galleryOpeners(root) {
    return [...root.querySelectorAll("[data-gallery-open]")].filter(
        (opener) => opener instanceof HTMLAnchorElement,
    );
}

/**
 * Track thumbnail image loading without hiding server-rendered content.
 */
function initializeThumbnailState(cards) {
    const cleanups = [];

    cards.forEach((card) => {
        const image = card.querySelector(".gallery-collage-card__image");

        if (!(image instanceof HTMLImageElement)) {
            card.dataset.galleryImageState = "loaded";

            return;
        }

        const markLoaded = () => {
            card.dataset.galleryImageState = "loaded";
        };

        const markError = () => {
            card.dataset.galleryImageState = "error";
        };

        if (image.complete) {
            image.naturalWidth > 0 ? markLoaded() : markError();

            return;
        }

        image.addEventListener("load", markLoaded, { once: true });
        image.addEventListener("error", markError, { once: true });

        cleanups.push(() => {
            image.removeEventListener("load", markLoaded);
            image.removeEventListener("error", markError);
        });
    });

    return () => {
        cleanups.forEach((cleanup) => cleanup());
    };
}

/**
 * Validate the JSON payload returned by the paginated Gallery endpoint.
 */
function parseGalleryPayload(payload) {
    if (
        payload === null ||
        typeof payload !== "object" ||
        typeof payload.html !== "string"
    ) {
        throw new TypeError(
            "The Gallery response did not contain valid HTML.",
        );
    }

    if (
        payload.next_page_url !== null &&
        typeof payload.next_page_url !== "string"
    ) {
        throw new TypeError(
            "The Gallery response contained an invalid next-page URL.",
        );
    }

    return {
        html: payload.html,
        nextPageUrl: payload.next_page_url,
    };
}

/**
 * Load and append the next server-rendered Gallery page.
 */
function initializeLoadMore(root, onCardsAppended) {
    const grid = root.querySelector("[data-gallery-grid]");

    if (!(grid instanceof HTMLElement)) {
        return () => {};
    }

    let activeController = null;

    const handleClick = async (event) => {
        const button =
            event.target instanceof Element
                ? event.target.closest("[data-gallery-load-more]")
                : null;

        if (!(button instanceof HTMLAnchorElement)) {
            return;
        }

        event.preventDefault();

        if (button.dataset.galleryBusy === "true") {
            return;
        }

        const shell = button.closest("[data-gallery-load-more-shell]");
        const label = button.querySelector("[data-gallery-load-more-label]");
        const status = shell?.querySelector(
            "[data-gallery-load-more-status]",
        );

        activeController?.abort();
        activeController = new AbortController();

        button.dataset.galleryBusy = "true";
        button.setAttribute("aria-disabled", "true");

        if (label) {
            label.textContent = "Loading moments";
        }

        if (status) {
            status.textContent = "Loading more Gallery images.";
        }

        try {
            const response = await fetch(button.href, {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                signal: activeController.signal,
            });

            if (!response.ok) {
                throw new Error(
                    `Gallery request failed with status ${response.status}.`,
                );
            }

            const payload = parseGalleryPayload(await response.json());
            const template = document.createElement("template");

            template.innerHTML = payload.html.trim();

            const newCards = [
                ...template.content.querySelectorAll("[data-gallery-item]"),
            ].filter((card) => card instanceof HTMLElement);

            grid.append(template.content);
            onCardsAppended(newCards);

            if (payload.nextPageUrl) {
                button.href = payload.nextPageUrl;
                button.dataset.galleryBusy = "false";
                button.removeAttribute("aria-disabled");

                if (label) {
                    label.textContent = "Load more moments";
                }

                if (status) {
                    status.textContent =
                        `${newCards.length} more moments are now in view.`;
                }
            } else {
                button.remove();

                if (status) {
                    status.textContent =
                        "All Gallery moments are now in view.";
                }
            }
        } catch (error) {
            if (error instanceof DOMException && error.name === "AbortError") {
                return;
            }

            console.error("Unable to load additional Gallery images.", error);

            button.dataset.galleryBusy = "false";
            button.removeAttribute("aria-disabled");

            if (label) {
                label.textContent = "Try again";
            }

            if (status) {
                status.textContent =
                    "The additional images could not be loaded. Please try again.";
            }
        }
    };

    root.addEventListener("click", handleClick);

    return () => {
        activeController?.abort();
        root.removeEventListener("click", handleClick);
    };
}

/**
 * Copy one Gallery opener's metadata into the fullscreen dialog.
 */
function setDialogContent(dialog, opener, position, total) {
    const image = dialog.querySelector("[data-gallery-dialog-image]");
    const title = dialog.querySelector("[data-gallery-dialog-title]");
    const category = dialog.querySelector("[data-gallery-dialog-category]");
    const counter = dialog.querySelector("[data-gallery-dialog-counter]");
    const status = dialog.querySelector("[data-gallery-dialog-status]");

    if (!(image instanceof HTMLImageElement)) {
        return;
    }

    const displayTitle =
        opener.dataset.galleryTitle ?? "Coast & Cay moment";

    dialog.dataset.galleryImageState = "loading";
    image.removeAttribute("src");
    image.removeAttribute("srcset");
    image.alt = opener.dataset.galleryAlt ?? displayTitle;
    image.sizes = "min(92vw, 1800px)";

    if (opener.dataset.gallerySrcset) {
        image.srcset = opener.dataset.gallerySrcset;
    }

    image.src = opener.dataset.gallerySrc ?? opener.href;

    if (title) {
        title.textContent = displayTitle;
    }

    if (category) {
        category.textContent =
            opener.dataset.galleryCategory ?? "Coast & Cay";
    }

    if (counter) {
        counter.textContent =
            `${String(position).padStart(2, "0")} / ${String(total).padStart(
                2,
                "0",
            )}`;
    }

    if (status) {
        status.textContent =
            `Loading image ${position} of ${total}: ${displayTitle}.`;
    }
}

/**
 * Initialize the native fullscreen Gallery dialog.
 */
function initializeDialog(root) {
    if (typeof HTMLDialogElement === "undefined") {
        return () => {};
    }

    const dialog = root.querySelector("[data-gallery-dialog]");

    if (!(dialog instanceof HTMLDialogElement)) {
        return () => {};
    }

    const image = dialog.querySelector("[data-gallery-dialog-image]");
    const previousButton = dialog.querySelector("[data-gallery-previous]");
    const nextButton = dialog.querySelector("[data-gallery-next]");
    const closeButton = dialog.querySelector("[data-gallery-close]");

    if (!(image instanceof HTMLImageElement)) {
        return () => {};
    }

    let activeIndex = 0;
    let activeOpener = null;
    let pointerStart = null;

    /**
     * Enable or disable navigation based on the current Gallery size.
     */
    const updateNavigationState = () => {
        const disabled = galleryOpeners(root).length < 2;

        if (previousButton instanceof HTMLButtonElement) {
            previousButton.disabled = disabled;
        }

        if (nextButton instanceof HTMLButtonElement) {
            nextButton.disabled = disabled;
        }
    };

    /**
     * Display one opener by index and announce its final load state.
     */
    const showIndex = (index) => {
        const openers = galleryOpeners(root);

        if (openers.length === 0) {
            return;
        }

        activeIndex = (index + openers.length) % openers.length;
        activeOpener = openers[activeIndex];

        setDialogContent(
            dialog,
            activeOpener,
            activeIndex + 1,
            openers.length,
        );

        updateNavigationState();
    };

    /**
     * Open the dialog for the selected Gallery card.
     */
    const openDialog = (opener) => {
        const openers = galleryOpeners(root);
        const index = openers.indexOf(opener);

        if (index < 0) {
            return;
        }

        showIndex(index);

        if (!dialog.open) {
            dialog.showModal();
        }

        document.documentElement.classList.add("gallery-lightbox-open");
        root.dataset.galleryDialogState = "open";

        requestAnimationFrame(() => {
            if (closeButton instanceof HTMLElement) {
                closeButton.focus({ preventScroll: true });
            }
        });
    };

    /**
     * Close the dialog and restore focus to its opener.
     */
    const closeDialog = () => {
        if (dialog.open) {
            dialog.close();
        }

        document.documentElement.classList.remove("gallery-lightbox-open");
        root.dataset.galleryDialogState = "closed";

        if (activeOpener instanceof HTMLElement) {
            activeOpener.focus({ preventScroll: true });
        }
    };

    const handleRootClick = (event) => {
        const opener =
            event.target instanceof Element
                ? event.target.closest("[data-gallery-open]")
                : null;

        if (!(opener instanceof HTMLAnchorElement)) {
            return;
        }

        event.preventDefault();
        openDialog(opener);
    };

    const handleDialogClick = (event) => {
        if (event.target === dialog) {
            closeDialog();
        }
    };

    const handleCancel = (event) => {
        event.preventDefault();
        closeDialog();
    };

    const handleKeydown = (event) => {
        if (!dialog.open) {
            return;
        }

        if (event.key === "ArrowLeft") {
            event.preventDefault();
            showIndex(activeIndex - 1);
        }

        if (event.key === "ArrowRight") {
            event.preventDefault();
            showIndex(activeIndex + 1);
        }
    };

    const handleImageLoad = () => {
        const status = dialog.querySelector("[data-gallery-dialog-status]");
        const title = activeOpener?.dataset.galleryTitle ?? "Gallery image";

        dialog.dataset.galleryImageState = "loaded";

        if (status) {
            status.textContent = `${title} is ready.`;
        }
    };

    const handleImageError = () => {
        const status = dialog.querySelector("[data-gallery-dialog-status]");

        dialog.dataset.galleryImageState = "error";

        if (status) {
            status.textContent =
                "The selected Gallery image could not be loaded.";
        }
    };

    const handlePointerDown = (event) => {
        pointerStart = {
            x: event.clientX,
            y: event.clientY,
        };
    };

    const handlePointerUp = (event) => {
        if (!pointerStart) {
            return;
        }

        const deltaX = event.clientX - pointerStart.x;
        const deltaY = event.clientY - pointerStart.y;

        pointerStart = null;

        if (
            Math.abs(deltaX) < swipeThreshold ||
            Math.abs(deltaX) <= Math.abs(deltaY)
        ) {
            return;
        }

        showIndex(activeIndex + (deltaX < 0 ? 1 : -1));
    };

    const handlePrevious = () => showIndex(activeIndex - 1);
    const handleNext = () => showIndex(activeIndex + 1);

    root.addEventListener("click", handleRootClick);
    dialog.addEventListener("click", handleDialogClick);
    dialog.addEventListener("cancel", handleCancel);
    document.addEventListener("keydown", handleKeydown);
    image.addEventListener("load", handleImageLoad);
    image.addEventListener("error", handleImageError);
    image.addEventListener("pointerdown", handlePointerDown);
    image.addEventListener("pointerup", handlePointerUp);
    closeButton?.addEventListener("click", closeDialog);
    previousButton?.addEventListener("click", handlePrevious);
    nextButton?.addEventListener("click", handleNext);

    return () => {
        root.removeEventListener("click", handleRootClick);
        dialog.removeEventListener("click", handleDialogClick);
        dialog.removeEventListener("cancel", handleCancel);
        document.removeEventListener("keydown", handleKeydown);
        image.removeEventListener("load", handleImageLoad);
        image.removeEventListener("error", handleImageError);
        image.removeEventListener("pointerdown", handlePointerDown);
        image.removeEventListener("pointerup", handlePointerUp);
        closeButton?.removeEventListener("click", closeDialog);
        previousButton?.removeEventListener("click", handlePrevious);
        nextButton?.removeEventListener("click", handleNext);
        document.documentElement.classList.remove("gallery-lightbox-open");
    };
}

/**
 * Initialize Gallery loading and lightbox interactions without GSAP.
 */
export function initGalleryInteractions(
    root = document.querySelector("[data-gallery-page]"),
) {
    if (!(root instanceof HTMLElement)) {
        return () => {};
    }

    const thumbnailCleanups = [initializeThumbnailState(galleryCards(root))];

    const loadMoreCleanup = initializeLoadMore(root, (cards) => {
        thumbnailCleanups.push(initializeThumbnailState(cards));
    });

    const dialogCleanup = initializeDialog(root);

    root.dataset.galleryMotionState = "native";

    const cleanup = () => {
        loadMoreCleanup();
        dialogCleanup();
        thumbnailCleanups.reverse().forEach((callback) => callback());
        delete root.dataset.galleryMotionState;
        delete root.dataset.galleryDialogState;
    };

    if (import.meta.hot) {
        import.meta.hot.dispose(cleanup);
    }

    return cleanup;
}
