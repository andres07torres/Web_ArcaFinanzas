<?php

namespace App\Enum;

enum TransactionTypeEnum: string
{
    case INCOME = 'income';
    case EXPENSE = 'expense';

    public function getLabel(): string
    {
        return match ($this) {
            self::INCOME => 'Ingreso',
            self::EXPENSE => 'Egreso',
        };
    }
}
