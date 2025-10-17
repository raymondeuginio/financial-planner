<?php

use Illuminate\Support\Number;

if (!function_exists('format_idr')) {
    function format_idr(float|int|string|null $value): string
    {
        $amount = is_null($value) ? 0 : (float) $value;

        return 'Rp' . Number::format($amount, locale: 'id_ID');
    }
}

if (!function_exists('first_day_of_month')) {
    function first_day_of_month(string|\DateTimeInterface $date): string
    {
        $date = \Illuminate\Support\Carbon::parse($date);

        return $date->startOfMonth()->toDateString();
    }
}
