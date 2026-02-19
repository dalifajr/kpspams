<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $areaId = $this->route('area')?->id ?? 0;

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('areas', 'name')->ignore($areaId)],
            'customer_count' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:300'],
        ];
    }
}
