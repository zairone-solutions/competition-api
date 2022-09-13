<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $setting = Setting::create(['key' => "competition", 'title' => "Competition"]);
        $setting->children()->create(['key' => 'cost_per_participant', 'rule' => "required|numeric", 'title' => "Cost Per Participant", 'value' => 10]);
        $setting->children()->create(['key' => 'min_competition_days', 'rule' => "required|numeric", 'title' => "Minimum Competition Days", 'value' => 14]);
        $setting->children()->create(['key' => 'max_competition_days', 'rule' => "required|numeric", 'title' => "Maximum Competition Days", 'value' => 28]);
        $setting->children()->create(['key' => 'voting_delay_days', 'rule' => "required|numeric", 'title' => "Voting Delay Days (after publishing)", 'value' => 3]);
        $setting->children()->create(['key' => 'min_participants_allowed', 'rule' => "required|lte:max_participants_allowed", 'title' => "Minimum Participants Allowed", 'value' => 10]);
        $setting->children()->create(['key' => 'max_participants_allowed', 'rule' => "required|gte:min_participants_allowed", 'title' => "Maximum Participants Allowed", 'value' => 3000]);
        $setting->children()->create(['key' => 'min_prize_money', 'rule' => "required|numeric|lte:max_prize_money", 'title' => "Minimum Prize Money", 'value' => 300]);
        $setting->children()->create(['key' => 'max_prize_money', 'rule' => "required|numeric|gte:min_prize_money", 'title' => "Maximum Prize Money", 'value' => 3000000]);
        $setting->children()->create(['key' => 'min_entry_fee', 'rule' => "required|numeric|lt:max_prize_money", 'title' => "Minimum Entry Fee", 'value' => 10]);
    }
}
