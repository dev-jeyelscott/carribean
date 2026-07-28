import {
    Alpine,
    Livewire,
} from "../../vendor/livewire/livewire/dist/livewire.esm";
import contactForm from "./forms/contact-form";

window.Alpine = Alpine;

Alpine.data("contactForm", contactForm);

Livewire.start();

if (document.querySelector("[data-reveal]")) {
    import("./public-reveals")
        .then(({ initPublicReveals }) => {
            initPublicReveals();
        })
        .catch((error) => {
            console.error("Unable to initialize public reveals.", error);
        });
}

const publicMotionRoot = document.querySelector("[data-home-motion]");

if (publicMotionRoot) {
    import("./public-animations")
        .then(({ initPublicAnimations }) => {
            initPublicAnimations(publicMotionRoot);
        })
        .catch((error) => {
            console.error("Unable to initialize public animations.", error);
        });
}

if (
    publicMotionRoot?.querySelector(
        'section[aria-label="Restaurant highlights"]',
    )
) {
    import("./homepage-scroll-advance")
        .then(({ initHomepageScrollAdvance }) => {
            initHomepageScrollAdvance(publicMotionRoot);
        })
        .catch((error) => {
            console.error(
                "Unable to initialize homepage scroll advance.",
                error,
            );
        });
}
