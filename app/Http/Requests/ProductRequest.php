<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function rules()
    {
        return [
            'slug' => ['required'],
            'title' => ['required'],
            'brand' => ['nullable'],
            'description' => ['nullable'],
            'attributes' => ['nullable'],
            'status' => ['nullable'],
        ];
    }

    public function authorize()
    {
        return true;
    }
}
