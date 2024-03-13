<?php

namespace App\Helpers;

use App\Models\Setting;

class RuleHelper
{
    public static function rules($key)
    {
        $rules = [];
        $competition = Setting::where("key", $key)->first();
        $competition_setting = $competition->children()->get();
        foreach ($competition_setting as $rule) {
            if ($key && $key == $rule->key) return $rule->value;
            $rules[$rule->key] = $rule->value;
        }
        return $rules;
    }
}
