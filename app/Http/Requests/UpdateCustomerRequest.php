<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user?->isAdmin() || $user?->isPetugas();
    }

    public function rules(): array
    {
        $customer = $this->route('customer');
        $customerId = $customer?->id ?? 0;

        return [
            'customer_code' => ['required', 'string', 'max:20', Rule::unique('customers', 'customer_code')->ignore($customerId)],
            'name' => ['required', 'string', 'max:120'],
            'address_short' => ['required', 'string', 'max:160'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'family_members' => ['required', 'integer', 'min:0', 'max:999'],
            'area_id' => ['required', 'integer', 'exists:areas,id'],
            'golongan_id' => ['required', 'integer', 'exists:golongans,id'],
        ];
    }
}
