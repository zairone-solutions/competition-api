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
        $output = array();
        if (!ctype_digit($ts))
            $ts = strtotime($ts);

        $diff = time() - $ts;
        if ($diff == 0)
            $output['relative'] = 'now';
        elseif ($diff > 0) {
            $day_diff = floor($diff / 86400);
            if ($day_diff == 0) {
                if ($diff < 60) $output['relative'] = 'just now';
                if ($diff < 120) $output['relative'] = '1 min';
                if ($diff < 3600) $output['relative'] = floor($diff / 60) . ' mins';
                if ($diff < 7200) $output['relative'] = '1 hr';
                if ($diff < 86400) $output['relative'] = floor($diff / 3600) . ' hrs';
            }
            if ($day_diff == 1) $output['relative'] = 'Yesterday';
            if ($day_diff < 7) $output['relative'] = $day_diff . ' days';
            if ($day_diff < 31) $output['relative'] = ceil($day_diff / 7) . ' weeks';
            if ($day_diff < 60) $output['relative'] = 'last month';
            $output['relative'] = date('F Y', $ts);
        } else {
            $diff = abs($diff);
            $day_diff = floor($diff / 86400);
            if ($day_diff == 0) {
                if ($diff < 120) $output['relative'] = 'in a min';
                if ($diff < 3600) $output['relative'] = 'in ' . floor($diff / 60) . ' mins';
                if ($diff < 7200) $output['relative'] = 'in an hr';
                if ($diff < 86400) $output['relative'] = 'in ' . floor($diff / 3600) . ' hrs';
            }
            if ($day_diff == 1) $output['relative'] = 'Tomorrow';
            if ($day_diff < 4) $output['relative'] = date('l', $ts);
            if ($day_diff < 7 + (7 - date('w'))) $output['relative'] = 'next week';
            if (ceil($day_diff / 7) < 4) $output['relative'] = 'in ' . ceil($day_diff / 7) . ' weeks';
            if (date('n', $ts) == date('n') + 1) $output['relative'] = 'next month';
            $output['relative'] = date('F Y', $ts);
        }
        $output['date'] = date("M d, Y", $ts);
        return $output;
    }
}
