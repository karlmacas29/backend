<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserAdminRegisterRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users|max:255',
            'password' => 'required|string|min:3',
            'position' => 'required|string|max:255',
            'active' => 'required|boolean',

            // Optional permission flags
            'permissions.isFunded' => 'boolean',
            'permissions.isUserM' => 'boolean',
            'permissions.isRaterM' => 'boolean',
            'permissions.isCriteria' => 'boolean',
            'permissions.isDashboardStat' => 'boolean',

            'permissions.isJobCreate' => 'boolean',
            'permissions.isJobEdit' => 'boolean',
            'permissions.isJobView' => 'boolean',
            'permissions.isJobDelete' => 'boolean',

        ];
    }
}
