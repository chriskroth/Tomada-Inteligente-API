<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlugStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
//        return false;
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            "id" => "filled|max:80",
            "serial_number" => "filled|max:80",
            "name" => "filled|max:80",
            "power" => "filled|max:80",
            "consumption" => "filled|max:80",
        ];
    }
}
