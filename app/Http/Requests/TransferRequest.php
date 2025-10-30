<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest {
    public function rules(): array {
        return [
            'from_user_id' => ['required','integer','different:to_user_id','exists:users,id'],
            'to_user_id'   => ['required','integer','exists:users,id'],
            'amount'       => ['required','numeric','min:0.01'],
            'comment'      => ['nullable','string','max:255'],
        ];
    }
}

