<?php

namespace App\Livewire\Checkout;

use App\Actions\Orders\PlaceOrder;
use App\Enums\FulfillmentMethod;
use App\Enums\PaymentMethod;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\Cart\SessionCart;
use App\Support\Orders\OrderTotalsCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

final class CheckoutPage extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $paymentMethod = '';

    public string $recipientName = '';

    public string $streetAddress = '';

    public string $apartmentOrUnit = '';

    public string $deliveryCity = '';

    public string $deliveryState = 'CA';

    public string $deliveryPostalCode = '';

    public string $deliveryPhone = '';

    public string $deliveryInstructions = '';

    public string $customerNote = '';

    public string $checkoutToken = '';

    /**
     * Prefill customer, delivery, payment, and idempotency state.
     */
    public function mount(): void
    {
        $sessionCart = app(SessionCart::class);
        $user = Auth::user();

        if ($user instanceof User) {
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';
        }

        $this->recipientName = $this->name;
        $this->deliveryPhone = $this->phone;

        $this->deliveryPostalCode =
            $sessionCart->deliveryZip() ?? '';

        $paymentOptions =
            $this->availablePaymentOptions(
                $sessionCart
                    ->fulfillmentMethod(),
            );

        $firstPaymentMethod =
            array_key_first($paymentOptions);

        $this->paymentMethod =
            is_string($firstPaymentMethod)
            ? $firstPaymentMethod
            : '';

        $storedToken = session()->get(
            'checkout.idempotency_token',
        );

        $this->checkoutToken =
            is_string($storedToken)
                && Str::isUuid($storedToken)
            ? $storedToken
            : (string) Str::uuid();

        session()->put(
            'checkout.idempotency_token',
            $this->checkoutToken,
        );
    }

    /**
     * Validate checkout input and place the order.
     */
    public function placeOrder(
        PlaceOrder $placeOrder,
    ): void {
        $sessionCart = app(SessionCart::class);

        $fulfillmentMethod =
            $sessionCart->fulfillmentMethod();

        $validated = $this->validate(
            $this->rulesFor(
                $fulfillmentMethod,
            ),
        );

        $paymentMethod =
            PaymentMethod::from(
                $validated['paymentMethod'],
            );

        $paymentOptions =
            $this->availablePaymentOptions(
                $fulfillmentMethod,
            );

        if (
            ! array_key_exists(
                $paymentMethod->value,
                $paymentOptions,
            )
        ) {
            throw ValidationException::withMessages([
                'paymentMethod' => 'The selected payment method is not currently available.',
            ]);
        }

        $deliveryAddress =
            $fulfillmentMethod
                === FulfillmentMethod::Delivery
            ? [
                'recipient_name' => $validated['recipientName'],

                'street_address' => $validated['streetAddress'],

                'apartment_or_unit' => $validated[
                        'apartmentOrUnit'
                    ] ?? null,

                'city' => $validated['deliveryCity'],

                'state' => $validated['deliveryState'],

                'postal_code' => $validated[
                        'deliveryPostalCode'
                    ],

                'phone' => $validated['deliveryPhone'],

                'delivery_instructions' => $validated[
                        'deliveryInstructions'
                    ] ?? null,
            ]
            : null;

        $user = Auth::user();

        $order = $placeOrder->execute(
            $sessionCart,
            $user instanceof User
                ? $user
                : null,
            [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ],
            $fulfillmentMethod,
            $paymentMethod,
            $deliveryAddress,
            $validated['customerNote']
                ?? null,
            $this->checkoutToken,
        );

        session()->forget(
            'checkout.idempotency_token',
        );

        $successUrl =
            URL::temporarySignedRoute(
                'checkout.success',
                now()->addDay(),
                [
                    'order' => $order,
                ],
            );

        $this->redirect($successUrl);
    }

    /**
     * Render current totals and available payment methods.
     */
    public function render(): View
    {
        $sessionCart = app(SessionCart::class);

        $fulfillmentMethod =
            $sessionCart->fulfillmentMethod();

        return view(
            'livewire.checkout.checkout-page',
            [
                'cart' => $this->calculateCart(),

                'fulfillmentMethod' => $fulfillmentMethod,

                'paymentOptions' => $this->availablePaymentOptions(
                    $fulfillmentMethod,
                ),
            ],
        );
    }

    /**
     * Return dynamic checkout rules for pickup or delivery.
     *
     * @return array<string, array<int, mixed>>
     */
    private function rulesFor(
        FulfillmentMethod $fulfillmentMethod,
    ): array {
        $requiresDelivery =
            $fulfillmentMethod
                === FulfillmentMethod::Delivery;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
            ],

            'phone' => [
                'required',
                'string',
                'max:30',
            ],

            'paymentMethod' => [
                'required',
                Rule::enum(
                    PaymentMethod::class,
                ),
            ],

            'recipientName' => [
                Rule::requiredIf(
                    $requiresDelivery,
                ),
                'nullable',
                'string',
                'max:255',
            ],

            'streetAddress' => [
                Rule::requiredIf(
                    $requiresDelivery,
                ),
                'nullable',
                'string',
                'max:255',
            ],

            'apartmentOrUnit' => [
                'nullable',
                'string',
                'max:100',
            ],

            'deliveryCity' => [
                Rule::requiredIf(
                    $requiresDelivery,
                ),
                'nullable',
                'string',
                'max:100',
            ],

            'deliveryState' => [
                Rule::requiredIf(
                    $requiresDelivery,
                ),
                'nullable',
                'string',
                'size:2',
            ],

            'deliveryPostalCode' => [
                Rule::requiredIf(
                    $requiresDelivery,
                ),
                'nullable',
                'regex:/^\d{5}$/',
            ],

            'deliveryPhone' => [
                Rule::requiredIf(
                    $requiresDelivery,
                ),
                'nullable',
                'string',
                'max:30',
            ],

            'deliveryInstructions' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'customerNote' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'checkoutToken' => [
                'required',
                'uuid',
            ],
        ];
    }

    /**
     * Return currently enabled cash methods for the fulfillment type.
     *
     * Stripe is intentionally added in Phase 7.
     *
     * @return array<string, string>
     */
    private function availablePaymentOptions(
        FulfillmentMethod $fulfillmentMethod,
    ): array {
        if (
            $fulfillmentMethod
                === FulfillmentMethod::Pickup
            && SiteSetting::cashAtPickupEnabled()
        ) {
            return [
                PaymentMethod::CashAtPickup->value => PaymentMethod::CashAtPickup->label(),
            ];
        }

        if (
            $fulfillmentMethod
                === FulfillmentMethod::Delivery
            && SiteSetting::cashOnDeliveryEnabled()
        ) {
            return [
                PaymentMethod::CashOnDelivery->value => PaymentMethod::CashOnDelivery->label(),
            ];
        }

        return [];
    }

    /**
     * Calculate current server-authoritative checkout totals.
     *
     * @return array<string, mixed>
     */
    private function calculateCart(): array
    {
        $sessionCart = app(SessionCart::class);

        return app(
            OrderTotalsCalculator::class,
        )->calculate(
            $sessionCart->items(),
            $sessionCart->couponCode(),
            $sessionCart
                ->fulfillmentMethod(),
            $sessionCart->deliveryZip(),
        );
    }
}
