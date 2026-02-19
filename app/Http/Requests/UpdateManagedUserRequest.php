<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateManagedUserRequest extends FormRequest
{
    protected $errorBag = 'editUser';

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $managedUser = $this->route('managedUser');
        $managedUserId = $managedUser?->id ?? 0;

        return [
            'edit_user_id' => ['required', 'integer', Rule::in([$managedUserId])],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($managedUserId)],
            'phone_number' => ['required', 'string', 'max:30', Rule::unique('users', 'phone_number')->ignore($managedUserId)],
            'area_id' => ['required', 'integer', 'exists:areas,id'],
            'address_short' => ['nullable', 'string', 'max:160'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
