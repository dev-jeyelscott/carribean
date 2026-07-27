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

if (document.querySelector("[data-home-motion]")) {
    import("./public-animations")
        .then(({ initPublicAnimations }) => {
            initPublicAnimations();
        })
        .catch((error) => {
            console.error("Unable to initialize public animations.", error);
        });
}
