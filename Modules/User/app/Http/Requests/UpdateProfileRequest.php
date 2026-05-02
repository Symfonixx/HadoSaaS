<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $user = $this->user();

        return [
            'img' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1048',
            'img_media_path' => 'nullable|string',
            'name' => 'required|string|min:3|max:50',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'password' => 'nullable|string|min:6|confirmed',
        ];
    }
}
