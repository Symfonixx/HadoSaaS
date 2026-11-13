<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleUsersRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'role_id' => 'required|integer|exists:roles,id',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|integer|distinct|exists:users,id',
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
