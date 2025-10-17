<?php

use Carbon\Carbon;

if (!function_exists('first_day_of_month')) {
    function first_day_of_month(string $date): string
    {
        return Carbon::parse($date)->startOfMonth()->toDateString();
    }
}
