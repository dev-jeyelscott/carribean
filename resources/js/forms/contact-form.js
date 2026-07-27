const defaultSuccessTitle = "Message received";

function fieldErrorId(field) {
    return `${field.id || field.name}-error`;
}

function serverErrorMessage(payload) {
    if (typeof payload?.message === "string" && payload.message.trim() !== "") {
        return payload.message;
    }

    return "We could not send your message. Please review the form and try again.";
}

/**
 * Return the Alpine state used by the public contact form.
 */
export default function contactForm() {
    return {
        submitting: false,
        errors: {},
        successMessage: "",
        successTitle: defaultSuccessTitle,

        /**
         * Attach accessible validation behavior after Alpine initializes.
         */
        init() {
            const form = this.$root;

            this.successTitle =
                form.dataset.successTitle || defaultSuccessTitle;

            form.querySelectorAll("input, select, textarea").forEach((field) => {
                field.addEventListener("blur", () => {
                    this.validateField(field);
                });

                field.addEventListener("input", () => {
                    if (this.errors[field.name]) {
                        this.validateField(field);
                    }
                });

                field.addEventListener("change", () => {
                    if (this.errors[field.name]) {
                        this.validateField(field);
                    }
                });
            });
        },

        /**
         * Return a user-facing browser validation message for one field.
         */
        validationMessage(field) {
            if (field.validity.valueMissing) {
                return "This field is required.";
            }

            if (field.validity.typeMismatch) {
                return "Enter a valid value.";
            }

            if (field.validity.tooLong) {
                return `Use no more than ${field.maxLength} characters.`;
            }

            return field.validationMessage || "Check this field.";
        },

        /**
         * Validate one field and synchronize its accessible error state.
         */
        validateField(field) {
            if (!field.name || field.disabled) {
                return true;
            }

            if (field.checkValidity()) {
                this.clearFieldError(field);

                return true;
            }

            this.setFieldError(field, this.validationMessage(field));

            return false;
        },

        /**
         * Validate every active field before submitting the contact message.
         */
        validateForm() {
            let valid = true;

            this.$root
                .querySelectorAll("input, select, textarea")
                .forEach((field) => {
                    if (!this.validateField(field)) {
                        valid = false;
                    }
                });

            if (!valid) {
                this.focusFirstError();
            }

            return valid;
        },

        /**
         * Add one accessible field error to Alpine and the DOM.
         */
        setFieldError(field, message) {
            this.errors = {
                ...this.errors,
                [field.name]: message,
            };

            field.setAttribute("aria-invalid", "true");

            const errorId = fieldErrorId(field);
            const describedBy = new Set(
                (field.getAttribute("aria-describedby") || "")
                    .split(/\s+/)
                    .filter(Boolean),
            );

            describedBy.add(errorId);
            field.setAttribute(
                "aria-describedby",
                [...describedBy].join(" "),
            );

            let error = this.$root.querySelector(`#${CSS.escape(errorId)}`);

            if (!error) {
                error = document.createElement("p");
                error.id = errorId;
                error.className = "public-field-error";
                field.insertAdjacentElement("afterend", error);
            }

            error.textContent = message;
        },

        /**
         * Remove one field error and restore its accessible attributes.
         */
        clearFieldError(field) {
            const nextErrors = { ...this.errors };

            delete nextErrors[field.name];
            this.errors = nextErrors;

            field.removeAttribute("aria-invalid");

            const errorId = fieldErrorId(field);
            const describedBy = (field.getAttribute("aria-describedby") || "")
                .split(/\s+/)
                .filter((id) => id && id !== errorId);

            if (describedBy.length > 0) {
                field.setAttribute(
                    "aria-describedby",
                    describedBy.join(" "),
                );
            } else {
                field.removeAttribute("aria-describedby");
            }

            this.$root
                .querySelector(`#${CSS.escape(errorId)}`)
                ?.remove();
        },

        /**
         * Clear all client and server validation errors.
         */
        clearErrors() {
            this.errors = {};

            this.$root
                .querySelectorAll('[aria-invalid="true"]')
                .forEach((field) => {
                    field.removeAttribute("aria-invalid");
                });

            this.$root
                .querySelectorAll(".public-field-error")
                .forEach((error) => error.remove());
        },

        /**
         * Focus the first invalid field after validation fails.
         */
        focusFirstError() {
            this.$nextTick(() => {
                this.$root
                    .querySelector('[aria-invalid="true"]')
                    ?.focus();
            });
        },

        /**
         * Submit the contact form with JSON and preserve non-JavaScript fallback.
         */
        async submit() {
            this.successMessage = "";

            if (!this.validateForm()) {
                return;
            }

            this.submitting = true;

            try {
                const response = await fetch(this.$root.action, {
                    method: this.$root.method || "POST",
                    body: new FormData(this.$root),
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                });

                const payload = await response.json().catch(() => ({}));

                if (response.status === 422 && payload.errors) {
                    this.applyServerErrors(payload.errors);
                    this.focusFirstError();

                    return;
                }

                if (!response.ok) {
                    throw new Error(serverErrorMessage(payload));
                }

                this.clearErrors();
                this.$root.reset();
                this.successMessage =
                    payload.message || "Your message has been received.";
            } catch (error) {
                this.successMessage = "";
                this.errors = {
                    form:
                        error instanceof Error
                            ? error.message
                            : serverErrorMessage(),
                };
            } finally {
                this.submitting = false;
            }
        },

        /**
         * Apply Laravel validation errors to their corresponding form fields.
         */
        applyServerErrors(errors) {
            this.clearErrors();

            Object.entries(errors).forEach(([name, messages]) => {
                const field = this.$root.elements.namedItem(name);
                const message = Array.isArray(messages)
                    ? messages[0]
                    : messages;

                if (
                    field instanceof HTMLInputElement
                    || field instanceof HTMLSelectElement
                    || field instanceof HTMLTextAreaElement
                ) {
                    this.setFieldError(field, String(message));
                }
            });
        },
    };
}
