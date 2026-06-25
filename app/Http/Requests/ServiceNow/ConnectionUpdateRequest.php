<?php

namespace App\Http\Requests\ServiceNow;

use Illuminate\Foundation\Http\FormRequest;

class ConnectionUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'instance_url' => ['required', 'url'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
        ];
    }
}
