<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class RegisterCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('email')) {
            $this->merge([
                'email' => strtolower(trim((string) $this->input('email'))),
            ]);
        }

        if ($this->filled('company_name')) {
            $this->merge([
                'company_name' => trim(strip_tags((string) $this->input('company_name'))),
            ]);
        }

        if ($this->filled('registration_number')) {
            $this->merge([
                'registration_number' => strtoupper(trim((string) $this->input('registration_number'))),
            ]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'company_name'        => ['required', 'string', 'max:255'],
            'registration_number' => ['required', 'string', 'max:100', Rule::unique('company_profiles', 'registration_number')],
            'industry_sector'     => ['required', 'string', 'max:150'],
            'contact_person_name' => ['required', 'string', 'max:255'],
            'phone_number'        => ['required', 'string', 'max:30'],
            'address'             => ['required', 'string', 'max:500'],
            'placement_capacity'  => ['required', 'integer', 'min:1', 'max:500'],
            'email'               => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password'            => ['required', 'confirmed', Password::defaults()],
        ];
    }
}