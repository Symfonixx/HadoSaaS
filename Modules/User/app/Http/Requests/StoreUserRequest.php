<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'img' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1048',
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|numeric|min_digits:11|unique:users,mobile',
            'password' => 'required|min:6',
            'role_id' => $this->routeIs('admin.staffs.store')
                ? ['required', 'integer', Rule::exists('roles', 'id')->where(fn ($query) => $query->where('name', '!=', 'Admin'))]
                : ['prohibited'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
