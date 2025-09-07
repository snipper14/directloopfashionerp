<?php

namespace App\Http\Requests\PrintLabels;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class PrintLabelDimensionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $method = $this->method();
        if (null !== $this->get('_method', null)) {
            $method = $this->get('_method');
        }
        $this->offsetUnset('_method');
        switch ($method) {

            case 'POST':
                $this->rules = [

                    'name' => [
                        'required',
                          Rule::unique('print_label_dimensions')->whereNull('deleted_at'),
                    ],
                    'bar_font_size' => "numeric|required|gte:1",
                    'bar_height' => "numeric|required|gt:0",
                    'bar_width' => "numeric|required|gt:0",
                    'wrapper_height' => "numeric|required|gte:1",
                    'wrapper_width' => "numeric|required|gte:1",
                    'item_description_fontsize' => "numeric|required|gte:1",

                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id'=>'required',
                    'name' => 'required',
                    'bar_font_size' => "numeric|required|gte:1",
                    'bar_height' => "numeric|required|gt:0",
                    'bar_width' => "numeric|required|gt:0",
                    'wrapper_height' => "numeric|required|gte:1",
                    'wrapper_width' => "numeric|required|gte:1",
                    'item_description_fontsize' => "numeric|required|gte:1",
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }

    public function messages()
    {
        return [];
    }
}
