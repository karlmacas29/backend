<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CriteriaRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'job_batches_rsp_id' => 'required|array',
            'job_batches_rsp_id.*' => 'exists:job_batches_rsp,id',

            'education.Rate' => 'required|string',
            'education.description' => 'required|array',
            'education.description.*' => 'required|string',

            'experience.Rate' => 'required|string',
            'experience.description' => 'required|array',
            'experience.description.*' => 'required|string',

            'training.Rate' => 'required|string',
            'training.description' => 'required|array',
            'training.description.*' => 'required|string',

            'performance.Rate' => 'required|string',
            'performance.description' => 'required|array',
            'performance.description.*' => 'required|string',

            'behavioral.Rate' => 'required|string',
            'behavioral.description' => 'required|array',
            'behavioral.description.*' => 'required|string',
        ];
    }
}
