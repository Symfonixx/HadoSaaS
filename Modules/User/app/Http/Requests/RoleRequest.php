<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'role_name' => 'required|string|min:2|max:100',
            'permissions' => 'required|array',
            'permissions.*' => 'string|distinct|exists:permissions,name',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('Hr Management') ?? false;
    }
}
