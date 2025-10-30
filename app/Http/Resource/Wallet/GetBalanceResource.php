<?php

namespace App\Http\Resources\Wallet;

use Illuminate\Http\Resources\Json\JsonResource;

class GetBalanceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [

            /**
             *  ID
             */
            'user_id' => $this->user_id,

            /**
             * Имя пользователя
             */
            'name'    => optional($this->user)->name,

            /**
             * Баланс
             */
            'balance' => (string)$this->amount,
        ];
    }
}
