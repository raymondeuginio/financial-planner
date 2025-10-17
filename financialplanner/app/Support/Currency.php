<?php

namespace App\Support;

use NumberFormatter;

class Currency
{
    public static function format(float|int|string|null $value): string
    {
        $number = (float) ($value ?? 0);

        if (class_exists(NumberFormatter::class)) {
            $formatter = new NumberFormatter('id_ID', NumberFormatter::CURRENCY);
            return $formatter->formatCurrency($number, 'IDR');
        }

        return 'Rp'.number_format($number, 0, ',', '.');
    }
}
