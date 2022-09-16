<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource
{

    public function number_format_short($n, $precision = 1)
    {
        if ($n < 900) {
            // 0 - 900
            $n_format = number_format($n, $precision);
            $suffix = '';
        } else if ($n < 900000) {
            // 0.9k-850k
            $n_format = number_format($n / 1000, $precision);
            $suffix = 'K';
        } else if ($n < 900000000) {
            // 0.9m-850m
            $n_format = number_format($n / 1000000, $precision);
            $suffix = 'M';
        } else if ($n < 900000000000) {
            // 0.9b-850b
            $n_format = number_format($n / 1000000000, $precision);
            $suffix = 'B';
        } else {
            // 0.9t+
            $n_format = number_format($n / 1000000000000, $precision);
            $suffix = 'T';
        }

        // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
        // Intentionally does not affect partials, eg "1.50" -> "1.50"
        if ($precision > 0) {
            $dotzero = '.' . str_repeat('0', $precision);
            $n_format = str_replace($dotzero, '', $n_format);
        }

        return $n_format . $suffix;
    }
    public function time2str($ts)
    {
        if (!ctype_digit($ts))
            $ts = strtotime($ts);

        $diff = time() - $ts;
        if ($diff == 0)
            return 'now';
        elseif ($diff > 0) {
            $day_diff = floor($diff / 86400);
            if ($day_diff == 0) {
                if ($diff < 60) return 'just now';
                if ($diff < 120) return '1 min';
                if ($diff < 3600) return floor($diff / 60) . ' mins';
                if ($diff < 7200) return '1 hr';
                if ($diff < 86400) return floor($diff / 3600) . ' hrs';
            }
            if ($day_diff == 1) return 'Yesterday';
            if ($day_diff < 7) return $day_diff . ' days';
            if ($day_diff < 31) return ceil($day_diff / 7) . ' weeks';
            if ($day_diff < 60) return 'last month';
            return date('F Y', $ts);
        } else {
            $diff = abs($diff);
            $day_diff = floor($diff / 86400);
            if ($day_diff == 0) {
                if ($diff < 120) return 'in a min';
                if ($diff < 3600) return 'in ' . floor($diff / 60) . ' mins';
                if ($diff < 7200) return 'in an hr';
                if ($diff < 86400) return 'in ' . floor($diff / 3600) . ' hrs';
            }
            if ($day_diff == 1) return 'Tomorrow';
            if ($day_diff < 4) return date('l', $ts);
            if ($day_diff < 7 + (7 - date('w'))) return 'next week';
            if (ceil($day_diff / 7) < 4) return 'in ' . ceil($day_diff / 7) . ' weeks';
            if (date('n', $ts) == date('n') + 1) return 'next month';
            return date('F Y', $ts);
        }
    }
}
