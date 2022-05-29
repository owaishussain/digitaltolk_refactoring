<?php

namespace App\Http\Requests;

class BookingStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'from_language_id' => 'required',
            'duration' => 'required',
            'due_date' => 'required_if:immediate,==,no',
            'due_time' => 'required_if:immediate,==,no',
            'customer_phone_type' => 'required_if:immediate,==,no',
            'customer_physical_type' => 'required_if:immediate,==,no',
        ];
    }
}
