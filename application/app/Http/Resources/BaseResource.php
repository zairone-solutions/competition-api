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

        // Ensure timestamp format
        if (!ctype_digit($ts)) {
            $ts = strtotime($ts);
        }

        // Calculate difference in seconds
        $diff = time() - $ts;

        if ($diff == 0) {
            $output['relative'] = 'now';
        } elseif ($diff > 0) {
            // Time in the past
            $day_diff = floor($diff / 86400);

            if ($day_diff == 0) {
                // Handle within today
                $hours = floor($diff / 3600);
                $minutes = floor(($diff % 3600) / 60);
                if ($minutes < 1) {
                    $output['relative'] = 'just now';
                } elseif ($minutes == 1) {
                    $output['relative'] = '1 min ago';
                } elseif ($minutes < 60) {
                    $output['relative'] = $minutes . ' mins ago';
                } elseif ($hours == 1) {
                    $output['relative'] = '1 hr ago';
                } elseif ($hours < 48) { // Limit "hours ago" to 2 days
                    $output['relative'] = $hours . ' hrs ago';
                } else {
                    $output['relative'] = date('Y-m-d', $ts); // Show full date if over 2 days
                }
            } elseif ($day_diff == 1) {
                $output['relative'] = 'Yesterday';
            } elseif ($day_diff < 7) {
                $output['relative'] = $day_diff . ' days ago';
            } elseif ($day_diff < 31) {
                $output['relative'] = 'last month'; // Adjust for clarity
            } else {
                $output['relative'] = date('F Y', $ts);
            }
        } else {
            // Time in the future
            $diff = abs($diff);
            $day_diff = floor($diff / 86400);

            if ($day_diff == 0) {
                // Handle within today
                $hours = floor($diff / 3600);
                $minutes = floor(($diff % 3600) / 60);
                if ($minutes < 2) { // Adjust for clarity
                    $output['relative'] = 'in a min';
                } else {
                    $output['relative'] = 'in ' . floor($diff / 60) . ' mins';
                }
            } elseif ($day_diff == 1) {
                $output['relative'] = 'Tomorrow';
            } elseif ($day_diff < 7 + (7 - date('w'))) {
                $output['relative'] = 'next week';
            } elseif (ceil($day_diff / 7) < 4) {
                $output['relative'] = 'in ' . ceil($day_diff / 7) . ' weeks';
            } else {
                $output['relative'] = date('F Y', $ts);
            }
        }

        // Include time in both cases
        $output['date'] = date(config("constants.date.format"), $ts);

        return $output;
    }
}
