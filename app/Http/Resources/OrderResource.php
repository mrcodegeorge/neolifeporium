<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'subtotal' => $this->subtotal,
            'total_amount' => $this->total_amount,
            'currency' => $this->currency,
            'vendor' => $this->vendor?->vendorProfile?->business_name ?? $this->vendor?->name,
            'items' => $this->items->map(fn ($item) => [
                'name' => $item->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
            ]),
            'payments' => $this->payments->map(fn ($payment) => [
                'provider' => $payment->provider,
                'status' => $payment->status,
                'amount' => $payment->amount,
            ]),
        ];
    }
}
