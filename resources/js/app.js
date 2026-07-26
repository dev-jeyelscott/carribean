import {
    Alpine,
    Livewire,
} from "../../vendor/livewire/livewire/dist/livewire.esm";
import inquiryForm from "./forms/inquiry-form";

window.Alpine = Alpine;

Alpine.data("inquiryForm", inquiryForm);

Livewire.start();

const homeMotionRoot = document.querySelector("[data-home-motion]");

if (homeMotionRoot) {
    Promise.all([import("./public-animations")])
        .then(([{ initPublicAnimations }]) => {
            const motionCleanup =
                initPublicAnimations(homeMotionRoot);

            if (import.meta.hot) {
                import.meta.hot.dispose(motionCleanup);
            }
        })
        .catch((error) => {
            console.error(
                "Unable to initialize public page interactions.",
                error,
            );
        });
}
