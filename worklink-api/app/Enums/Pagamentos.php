<?php

namespace App\Enums;

enum Pagamentos: Int
{
    case Pix = 1;
    case Boleto = 2;
    case CartaoCredito = 3;
    case CartaoDebito = 4;

    public static function fromValue($value)
    {
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case->name;
            }
        }
    }
}
