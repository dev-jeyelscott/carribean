<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Get the shared validation rules for registration and profile updates.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function profileRules(?User $user = null): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($user),
            'phone' => $this->phoneRules(),
        ];
    }

    /**
     * Get the validation rules used to validate customer names.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return [
            'required',
            'string',
            'max:255',
        ];
    }

    /**
     * Get the validation rules used to validate customer email addresses.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function emailRules(?User $user = null): array
    {
        $uniqueEmail = Rule::unique(User::class);

        if ($user instanceof User) {
            $uniqueEmail->ignore($user);
        }

        return [
            'required',
            'string',
            'email',
            'max:255',
            $uniqueEmail,
        ];
    }

    /**
     * Get the validation rules used to validate customer phone numbers.
     *
     * The application accepts flexible international formatting and stores the
     * number exactly as entered after trimming surrounding whitespace.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function phoneRules(): array
    {
        return [
            'required',
            'string',
            'max:30',
        ];
    }
}
