<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class ProfileUpdateRequest extends FormRequest
{
    /**
     * Prepare input before running validation rules.
     */
    protected function prepareForValidation(): void
    {
        $input = [];

        if ($this->has('name')) {
            $input['name'] = trim((string) $this->input('name'));
        }

        if ($this->has('email')) {
            $input['email'] = Str::lower(trim((string) $this->input('email')));
        }

        if (! empty($input)) {
            $this->merge($input);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|Rule|string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()?->id),
            ],
        ];
    }
}