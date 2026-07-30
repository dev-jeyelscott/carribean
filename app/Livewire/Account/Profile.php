<?php

namespace App\Livewire\Account;

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

final class Profile extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public bool $emailVerified = false;

    /**
     * Initialize the form from the currently authenticated customer.
     */
    public function mount(): void
    {
        $this->fillFromAuthenticatedUser();
    }

    /**
     * Validate and persist the authenticated customer's profile.
     */
    public function save(
        UpdateUserProfileInformation $updateProfile,
    ): void {
        $user = $this->authenticatedUser();

        $updateProfile->update($user, [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);

        $this->fillFromAuthenticatedUser();
        $this->resetValidation();

        session()->flash(
            'profile_status',
            'Your profile has been updated.',
        );
    }

    /**
     * Discard unsaved form changes and reload the persisted profile.
     */
    public function resetForm(): void
    {
        $this->fillFromAuthenticatedUser();
        $this->resetValidation();
    }

    /**
     * Render the customer profile form.
     */
    public function render(): View
    {
        return view('livewire.account.profile');
    }

    /**
     * Synchronize public form state from the authenticated database record.
     */
    private function fillFromAuthenticatedUser(): void
    {
        $user = $this->authenticatedUser()->fresh();

        if (! $user instanceof User) {
            abort(403);
        }

        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->emailVerified = $user->hasVerifiedEmail();
    }

    /**
     * Return the authenticated customer or deny access.
     */
    private function authenticatedUser(): User
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403);
        }

        return $user;
    }
}
