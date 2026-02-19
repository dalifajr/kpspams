<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreGolonganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:10', 'unique:golongans,code'],
            'name' => ['required', 'string', 'max:120'],
            'tariffs' => ['required', 'array', 'min:1'],
            'tariffs.*.meter_start' => ['required', 'numeric', 'min:0'],
            'tariffs.*.meter_end' => ['nullable', 'numeric', 'min:0'],
            'tariffs.*.price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach ($this->input('tariffs', []) as $index => $tariff) {
                if (! is_array($tariff)) {
                    $validator->errors()->add("tariffs.$index.meter_start", 'Format tarif tidak valid.');
                    continue;
                }

                if ($tariff['meter_end'] === null || $tariff['meter_end'] === '' || ! isset($tariff['meter_start'])) {
                    continue;
                }

                if ((float) $tariff['meter_end'] <= (float) $tariff['meter_start']) {
                    $validator->errors()->add("tariffs.$index.meter_end", 'Posisi meter akhir harus lebih besar dari awal.');
                }
            }
        });
    }
}
