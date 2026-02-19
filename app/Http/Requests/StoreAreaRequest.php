<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120', 'unique:areas,name'],
            'customer_count' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:300'],
        ];
    }
}
