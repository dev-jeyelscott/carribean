import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import { bunny } from "laravel-vite-plugin/fonts";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/css/public.css",
                "resources/css/public-header.css",
                "resources/css/filament/admin/app.css",
                "resources/js/app.js",

                /*
                 * Gallery owns a dedicated entry so its GSAP and ScrollTrigger
                 * runtime loads only on the Gallery route.
                 */
                "resources/js/gallery-page.js",
            ],
            refresh: true,
            fonts: [
                bunny("Instrument Sans", {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        watch: {
            ignored: [
                "**/storage/framework/views/**",
            ],
        },
        allowedHosts: [
            "unsworn-stock-naturist.ngrok-free.dev",
        ],
    },
});
