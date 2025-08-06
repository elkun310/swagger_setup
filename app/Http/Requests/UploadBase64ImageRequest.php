<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadBase64ImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'regex:/^data:image\/(jpeg|jpg|png|gif);base64,/', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Trường ảnh là bắt buộc.',
            'image.regex' => 'Định dạng ảnh không hợp lệ.',
        ];
    }
}
