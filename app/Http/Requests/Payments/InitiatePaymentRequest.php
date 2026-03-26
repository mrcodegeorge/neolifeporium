<?php

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;

class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['nullable', 'exists:orders,id'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'provider' => ['required', 'in:paystack,mtn_momo'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'channel' => ['nullable', 'string', 'max:50'],
            'callback_url' => ['nullable', 'url'],
        ];
    }
}
