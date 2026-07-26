<?php

namespace App\Http\Controllers;

use App\Actions\Payments\ProcessStripeWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

final class StripeWebhookController
{
    /**
     * Verify and process one incoming Stripe webhook request.
     */
    public function __invoke(
        Request $request,
        ProcessStripeWebhook $processWebhook,
    ): JsonResponse {
        $webhookSecret = config(
            'services.stripe.webhook_secret',
        );

        if (
            ! is_string($webhookSecret)
            || trim($webhookSecret) === ''
        ) {
            return response()->json(
                [
                    'message' => 'Stripe webhook is not configured.',
                ],
                503,
            );
        }

        $signature = $request->header(
            'Stripe-Signature',
        );

        if (
            ! is_string($signature)
            || $signature === ''
        ) {
            return response()->json(
                [
                    'message' => 'Stripe signature is missing.',
                ],
                400,
            );
        }

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $signature,
                $webhookSecret,
            );
        } catch (
            UnexpectedValueException
            |SignatureVerificationException
        ) {
            return response()->json(
                [
                    'message' => 'Invalid Stripe webhook.',
                ],
                400,
            );
        }

        $processWebhook->execute(
            $event,
        );

        return response()->json([
            'received' => true,
        ]);
    }
}
