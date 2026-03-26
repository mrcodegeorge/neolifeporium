<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\InitiatePaymentRequest;
use App\Models\Payment;
use App\Services\Payments\PaymentService;
use App\Support\ApiResponse;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function initiate(InitiatePaymentRequest $request)
    {
        return ApiResponse::success($this->payments->initiate([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]), 'Payment initiated.', 201);
    }

    public function verify(Payment $payment)
    {
        return ApiResponse::success($this->payments->verify($payment), 'Payment verified.');
    }

    public function webhook()
    {
        return ApiResponse::success(['received' => true], 'Webhook acknowledged.');
    }
}
