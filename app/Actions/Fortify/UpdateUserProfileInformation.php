<?php

namespace App\Actions\Fortify;

use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

final class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    use ProfileValidationRules;

    /**
     * Validate and update the authenticated customer's profile information.
     *
     * Changing the email address clears its verification timestamp and sends a
     * new verification notification without blocking account access.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
    {
        $input['name'] = trim($input['name']);
        $input['email'] = Str::lower(trim($input['email']));
        $input['phone'] = trim($input['phone']);

        Validator::make(
            $input,
            $this->profileRules($user),
        )->validate();

        $emailChanged = $input['email'] !== $user->email;

        $attributes = [
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phone'],
        ];

        if ($emailChanged) {
            $attributes['email_verified_at'] = null;
        }

        $user->forceFill($attributes)->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }
    }
}
