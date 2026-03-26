<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'session_type' => $this->session_type,
            'scheduled_for' => $this->scheduled_for?->toDateTimeString(),
            'duration_minutes' => $this->duration_minutes,
            'amount' => $this->amount,
            'topic' => $this->topic,
            'farmer' => $this->farmer?->name,
            'agronomist' => $this->agronomist?->name,
        ];
    }
}
