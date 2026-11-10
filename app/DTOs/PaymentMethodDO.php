<?php

namespace App\DTOs;

class PaymentMethodDO
{
    public function __construct(
        public string $name,
        public readonly ?int $created_by,
        public string $code,
    ){

    }

    public static function fromArray(array $data):self{
        return new self(
            name : $data['name'],
            code : $data['code'],
            created_by : $data['created_by'],
        );
    }

    public function toArray():array{
        return[
            'name' => $this->name,
            'code' => $this->code,
            'created_by' => $this->created_by
        ];
    }
}
