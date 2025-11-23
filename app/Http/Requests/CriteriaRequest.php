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
            'job_batches_rsp_id' => 'required|integer', // not array
            // 'job_batches_rsp_id' => 'required|array',
            'job_batches_rsp_id.*' => 'exists:job_batches_rsp,id',



            'education' => 'required|array',
            'education.*.weight' => 'required|string',
            'education.*.description' => 'required|string',
            'education.*.percentage' => 'required|integer',



            'experience' => 'required|array',
            'experience.*.weight' => 'required|string',
            'experience.*.description' => 'required|string',
            'experience.*.percentage' => 'required|integer',


            'training' => 'required|array',
            'training.*.weight' => 'required|string',
            'training.*.description' => 'required|string',
            'training.*.percentage' => 'required|integer',


            'performance' => 'required|array',
            'performance.*.weight' => 'required|string',
            'performance.*.description' => 'required|string',
            'performance.*.percentage' => 'required|integer',


            'behavioral' => 'required|array',
            'behavioral.*.weight' => 'required|string',
            'behavioral.*.description' => 'required|string',
            'behavioral.*.percentage' => 'required|integer',



        ];
    }
}
