<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use App\Authenticate\Licence;
use Illuminate\Foundation\Http\FormRequest;

use function PHPUnit\Framework\isEmpty;

class LicenceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $currentDate = Carbon::now();

        $expiredItems = Licence::where('expiry_date', '<', $currentDate)->get();
        if ($expiredItems->isEmpty()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'bail|required_without:username',
            'username' => 'bail|required_without:email',
            'password' => 'bail|required',
        ];
    }
}
