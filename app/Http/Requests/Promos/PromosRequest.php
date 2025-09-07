<?php

namespace App\Http\Requests\Promos;

use App\Promo\Promo;
use Illuminate\Foundation\Http\FormRequest;

class PromosRequest extends FormRequest
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
        
        $rules = [
            'lower_limit' => 'numeric|required|gt:0',
            'upper_limit' => 'numeric|required|gt:lower_limit', // Ensure upper_limit is greater than lower_limit
            'discount' => 'numeric|required|gt:0',
            'status' => 'required|in:active,inactive',
        ];

        // Custom validation for overlapping ranges
        $rules['lower_limit'] = function ($attribute, $value, $fail) {
            $upper_limit = $this->upper_limit;

            // Query to check for overlapping ranges
            $overlapQuery = Promo::where(function ($query) use ($value, $upper_limit) {
                $query->whereBetween('lower_limit', [$value, $upper_limit])
                      ->orWhereBetween('upper_limit', [$value, $upper_limit])
                      ->orWhere(function ($query) use ($value, $upper_limit) {
                          $query->where('lower_limit', '<', $value)
                                ->where('upper_limit', '>', $upper_limit);
                      });
            });

            // Exclude soft-deleted records
            $overlapQuery->whereNull('deleted_at');

            // Exclude the current record for updates
            if ($this->method() === 'PUT' && $this->id) {
                $overlapQuery->where('id', '<>', $this->id);
            }

            if ($overlapQuery->exists()) {
                $fail("The range [{$value}, {$upper_limit}] overlaps with an existing promo.");
            }
        };

        // Add `id` field requirement for PUT method
        if ($method === 'PUT') {
            $rules['id'] = 'required|exists:promos,id';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'lower_limit.required' => 'The lower limit is required.',
            'lower_limit.numeric' => 'The lower limit must be a number.',
            'lower_limit.gt' => 'The lower limit must be greater than 0.',
            'upper_limit.required' => 'The upper limit is required.',
            'upper_limit.numeric' => 'The upper limit must be a number.',
            'upper_limit.gt' => 'The upper limit must be greater than the lower limit.',
            'discount.required' => 'The discount is required.',
            'discount.numeric' => 'The discount must be a number.',
            'discount.gt' => 'The discount must be greater than 0.',
            'status.required' => 'The status is required.',
            'status.in' => 'The status must be either active or inactive.',
        ];
    }}