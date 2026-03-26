<?php

namespace App\Services\Payments;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentService
{
    public function initiate(array $payload): Payment
    {
        return Payment::create([
            'order_id' => $payload['order_id'] ?? null,
            'booking_id' => $payload['booking_id'] ?? null,
            'user_id' => $payload['user_id'],
            'provider' => $payload['provider'],
            'provider_reference' => Str::uuid()->toString(),
            'amount' => $payload['amount'],
            'currency' => $payload['currency'] ?? 'GHS',
            'status' => PaymentStatus::Initiated->value,
            'channel' => $payload['channel'] ?? null,
            'payload' => [
                'callback_url' => $payload['callback_url'] ?? null,
            ],
        ]);
    }

    public function verify(Payment $payment): Payment
    {
        if ($payment->provider === 'paystack' && config('services.paystack.secret_key')) {
            Http::withToken(config('services.paystack.secret_key'))
                ->get(config('services.paystack.base_url').'/transaction/verify/'.$payment->provider_reference);
        }

        $payment->update([
            'status' => PaymentStatus::Verified->value,
            'verified_at' => now(),
        ]);

        return $payment->refresh();
    }
}
