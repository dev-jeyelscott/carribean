/**
 * Reveal ordinary public-page sections once as they enter the viewport.
 *
 * Content remains visible when JavaScript or IntersectionObserver is
 * unavailable because the hidden state is applied only after initialization.
 */
export function initPublicReveals(root = document) {
    const elements = [...root.querySelectorAll("[data-reveal]")];

    if (elements.length === 0) {
        return () => {};
    }

    const reducedMotion = window.matchMedia(
        "(prefers-reduced-motion: reduce)",
    ).matches;

    if (reducedMotion || !("IntersectionObserver" in window)) {
        elements.forEach((element) => {
            element.classList.add("is-visible");
        });

        return () => {};
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add("is-visible");
                observer.unobserve(entry.target);
            });
        },
        {
            rootMargin: "0px 0px -10% 0px",
            threshold: 0.1,
        },
    );

    elements.forEach((element) => {
        element.classList.add("is-reveal-ready");
        observer.observe(element);
    });

    return () => {
        observer.disconnect();
    };
}
