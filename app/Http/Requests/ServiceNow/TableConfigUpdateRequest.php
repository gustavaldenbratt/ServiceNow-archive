<?php

namespace App\Http\Requests\ServiceNow;

use Illuminate\Foundation\Http\FormRequest;

class TableConfigUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'enabled' => ['required', 'boolean'],
            'fetch_since' => ['required', 'date'],
        ];
    }
}
