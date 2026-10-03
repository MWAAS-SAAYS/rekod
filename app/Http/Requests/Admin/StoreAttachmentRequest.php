<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

final class StoreAttachmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('manage-attachments');
    }

    /**
     * Prepare inputs for validation (Sanitization).
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('name')) {
            $this->merge([
                'name' => trim(strip_tags((string) $this->input('name'))),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, \Illuminate\Validation\Rules\Unique|string>>
     */
    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255', Rule::unique('attachments', 'name')],
            'start_date' => ['required', 'date', 'date_format:Y-m-d'],
            'end_date'   => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ];
    }
}