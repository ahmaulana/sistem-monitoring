<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;

class KeywordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'keyword' => 'required|unique:keywords|max:32|regex:/^[\pL\s]+$/u'
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            //
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'keyword.required' => 'Keyword tidak boleh kosong!',
            'keyword.unique' => 'Keyword sudah ada!',
            'keyword.max' => 'panjang keyword minimal 1 maksimal 32 karakter!',
            'keyword.regex' => 'Keyword hanya boleh berupa huruf dan angka!',
        ];
    }
}
