<div
    x-data="{
        notification: null,
        timeout: null,

        open(detail) {
            window.clearTimeout(this.timeout);

            this.notification = {
                type: detail?.type ?? 'info',
                title: detail?.title ?? 'Notice',
                message: detail?.message ?? '',
            };

            this.schedule();

            if (
                this.notification.type === 'error'
                && !document.querySelector('dialog[open]')
            ) {
                this.$nextTick(
                    () => this.$refs.notification?.focus(),
                );
            }
        },

        schedule() {
            window.clearTimeout(this.timeout);

            const delay = this.notification?.type === 'error'
                ? 7000
                : this.notification?.type === 'info'
                    ? 5000
                    : 4000;

            this.timeout = window.setTimeout(
                () => this.close(),
                delay,
            );
        },

        pause() {
            window.clearTimeout(this.timeout);
        },

        resume() {
            if (this.notification) {
                this.schedule();
            }
        },

        close() {
            window.clearTimeout(this.timeout);
            this.notification = null;
        },
    }"
    @inquiry-notification.window="open($event.detail)"
    @cart-notification.window="open($event.detail)"
    @keydown.escape.window="close()"
    class="pointer-events-none fixed inset-x-4 top-24 z-[70]
        flex justify-center sm:inset-x-auto sm:right-6
        sm:w-full sm:max-w-md">
    <div
        x-show="notification"
        x-cloak
        x-transition:enter="transition duration-300 ease-out"
        x-transition:enter-start="translate-y-3 scale-[0.98] opacity-0"
        x-transition:enter-end="translate-y-0 scale-100 opacity-100"
        x-transition:leave="transition duration-200 ease-in"
        x-transition:leave-start="translate-y-0 scale-100 opacity-100"
        x-transition:leave-end="-translate-y-2 scale-[0.98] opacity-0"
        x-ref="notification"
        x-bind:data-type="notification?.type"
        tabindex="-1"
        @mouseenter="pause()"
        @mouseleave="resume()"
        class="public-notification pointer-events-auto w-full
            rounded-card border p-5 text-sm leading-7
            shadow-[0_20px_60px_rgb(23_25_22_/_20%)]
            backdrop-blur-xl"
        :class="{
            'border-emerald-700/20 bg-emerald-50/95 text-emerald-950':
                notification?.type === 'success',
            'border-coral/25 bg-red-50/95 text-red-950':
                notification?.type === 'error',
            'border-ocean/20 bg-sky-50/95 text-sky-950':
                notification?.type === 'info',
        }"
        :role="notification?.type === 'error'
            ? 'alert'
            : 'status'"
        :aria-live="notification?.type === 'error'
            ? 'assertive'
            : 'polite'"
        aria-atomic="true"
        data-public-notification>
        <div class="flex items-start gap-4">
            <span
                class="mt-0.5 inline-flex size-9 shrink-0 items-center
                    justify-center rounded-full"
                :class="{
                    'bg-emerald-700/10 text-emerald-800':
                        notification?.type === 'success',
                    'bg-red-700/10 text-red-800':
                        notification?.type === 'error',
                    'bg-sky-700/10 text-sky-800':
                        notification?.type === 'info',
                }"
                aria-hidden="true">
                <svg
                    x-show="notification?.type === 'success'"
                    class="size-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m5 12 4 4L19 6" />
                </svg>

                <svg
                    x-show="notification?.type === 'error'"
                    class="size-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8">
                    <path
                        stroke-linecap="round"
                        d="M12 7v6M12 17h.01" />

                    <circle cx="12" cy="12" r="9" />
                </svg>

                <svg
                    x-show="notification?.type === 'info'"
                    class="size-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8">
                    <path
                        stroke-linecap="round"
                        d="M12 11v6M12 7h.01" />

                    <circle cx="12" cy="12" r="9" />
                </svg>
            </span>

            <div class="min-w-0 flex-1">
                <p
                    class="font-semibold"
                    x-text="notification?.title">
                </p>

                <p
                    class="mt-1"
                    x-text="notification?.message">
                </p>
            </div>

            <button
                type="button"
                class="inline-flex size-9 shrink-0 items-center
                    justify-center rounded-full transition
                    hover:bg-black/5"
                aria-label="Dismiss notification"
                @click="close()">
                <svg
                    class="size-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    aria-hidden="true">
                    <path
                        stroke-linecap="round"
                        d="m6 6 12 12M18 6 6 18" />
                </svg>
            </button>
        </div>
    </div>
</div>
