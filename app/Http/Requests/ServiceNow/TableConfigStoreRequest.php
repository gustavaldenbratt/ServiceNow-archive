<?php

namespace App\Http\Requests\ServiceNow;

use Illuminate\Foundation\Http\FormRequest;

class TableConfigStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'table_name' => ['required', 'string', 'max:255', 'unique:sn_table_configs,table_name'],
            'fetch_since' => ['required', 'date'],
        ];
    }
}
