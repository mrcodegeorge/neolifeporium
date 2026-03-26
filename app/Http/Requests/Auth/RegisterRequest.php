<?php

namespace App\Http\Requests\Auth;

use App\Enums\RoleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleValues = array_merge(RoleType::values(), ['expert']);

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['nullable', Rule::in($roleValues)],
            'roles' => ['nullable', 'array', 'min:1'],
            'roles.*' => ['required', Rule::in($roleValues)],
            'preferred_channel' => ['nullable', 'in:email,sms,in_app'],
            'region' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'farm_size_hectares' => ['nullable', 'numeric'],
            'crop_types' => ['nullable', 'array'],
            'crop_types.*' => ['string', 'max:80'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'business_type' => ['nullable', 'string', 'max:255'],
            'product_category' => ['nullable', 'string', 'max:255'],
            'vendor_description' => ['nullable', 'string', 'max:1000'],
            'vendor_region' => ['nullable', 'string', 'max:255'],
            'vendor_district' => ['nullable', 'string', 'max:255'],
            'vendor_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'expert_bio' => ['nullable', 'string', 'max:2000'],
            'regions_served' => ['nullable', 'array'],
            'regions_served.*' => ['string', 'max:255'],
            'certification_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $roles = $this->input('roles', []);
        if ($roles === [] && $this->filled('role')) {
            $roles = [$this->input('role')];
        }

        $cropTypes = $this->input('crop_types', []);
        if ($this->filled('crop_types_text')) {
            $cropTypes = collect(explode(',', (string) $this->input('crop_types_text')))
                ->map(fn (string $crop): string => trim($crop))
                ->filter()
                ->values()
                ->all();
        }

        $regionsServed = $this->input('regions_served', []);
        if ($this->filled('regions_served_text')) {
            $regionsServed = collect(explode(',', (string) $this->input('regions_served_text')))
                ->map(fn (string $region): string => trim($region))
                ->filter()
                ->values()
                ->all();
        }

        $this->merge([
            'roles' => $roles,
            'crop_types' => $cropTypes,
            'regions_served' => $regionsServed,
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $roles = collect($this->input('roles', []));
            if ($roles->isEmpty() && $this->filled('role')) {
                $roles = collect([$this->input('role')]);
            }

            if ($roles->isEmpty()) {
                $validator->errors()->add('roles', 'Select at least one role.');
                return;
            }

            if (! $this->filled('email') && ! $this->filled('phone')) {
                $validator->errors()->add('email', 'Provide at least email or phone number.');
            }

            if ($roles->contains('farmer')) {
                if (! $this->filled('region')) {
                    $validator->errors()->add('region', 'Region is required for farmer onboarding.');
                }
            }

            if ($roles->contains('vendor')) {
                foreach (['business_name', 'business_type', 'product_category'] as $field) {
                    if (! $this->filled($field)) {
                        $validator->errors()->add($field, 'This field is required for vendor onboarding.');
                    }
                }
            }

            if ($roles->contains('agronomist') || $roles->contains('expert')) {
                if (! $this->filled('specialty')) {
                    $validator->errors()->add('specialty', 'Specialization is required for expert onboarding.');
                }
            }
        });
    }
}
