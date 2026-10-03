<?php

declare(strict_types=1);

namespace App\Http\Requests\Company;

use App\Models\CompanyProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreInterestRequest extends FormRequest
{
    /**
     * Determine if the authenticated company is verified to express interest.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = $this->user();

        if ($user === null || ! $user->isCompany()) {
            return false;
        }

        // Enforce verified corporate status
        return $user->companyProfile?->status === CompanyProfile::STATUS_VERIFIED;
    }

    /**
     * Prepare inputs for validation (Sanitization).
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('message')) {
            $this->merge([
                'message' => trim(strip_tags((string) $this->input('message'))),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, \Illuminate\Validation\Rules\In|string>>
     */
    public function rules(): array
    {
        return [
            'proposed_scope' => [
                'required',
                'string',
                Rule::in(['licensing', 'joint_r_and_d', 'scouting_acquisition', 'student_internship']),
            ],
            'message' => ['required', 'string', 'min:20', 'max:1000'],
        ];
    }
}