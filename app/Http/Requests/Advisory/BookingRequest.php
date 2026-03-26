<?php

namespace App\Http\Requests\Advisory;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'agronomist_id' => ['required', 'exists:users,id'],
            'scheduled_for' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['required', 'integer', 'min:15'],
            'session_type' => ['required', 'in:chat,video'],
            'amount' => ['required', 'numeric', 'min:0'],
            'topic' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
