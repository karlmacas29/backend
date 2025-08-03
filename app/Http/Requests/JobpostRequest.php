<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobpostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'Office' => 'required|string',
            'Office2' => 'nullable|string',
            'Group' => 'nullable|string',
            'Division' => 'nullable|string',
            'Section' => 'nullable|string',
            'Unit' => 'nullable|string',
            'Position' => 'required|string',
            'PositionID' => 'required|integer',
            'isOpen' => 'required|boolean',
            'post_date' => 'required|date',
            'end_date' => 'required|date',
            'PageNo' => 'nullable|string',
            'ItemNo' => 'required|string',
            'SalaryGrade' => 'required|string',
            'salaryMin' => 'nullable|string',
            'salaryMax' => 'nullable|string',
            'level' => 'required|string', // Changed to string
        ];
    }
}
