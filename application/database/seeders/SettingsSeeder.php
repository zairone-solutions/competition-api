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
        $competition = Setting::create(['key' => "competition", 'title' => "Competition"]);
        $competition->children()->create(['key' => 'cost_per_participant', 'rule' => "required|numeric", 'title' => "Cost Per Participant", 'value' => 10]);
        $competition->children()->create(['key' => 'platform_charges', 'rule' => "required|numeric", 'title' => "Platform Charges", 'value' => 150]);
        $competition->children()->create(['key' => 'min_competition_days', 'rule' => "required|numeric", 'title' => "Minimum Competition Days", 'value' => 14]);
        $competition->children()->create(['key' => 'max_competition_days', 'rule' => "required|numeric", 'title' => "Maximum Competition Days", 'value' => 28]);
        $competition->children()->create(['key' => 'voting_delay_days', 'rule' => "required|numeric", 'title' => "Voting Delay Days (after publishing)", 'value' => 3]);
        $competition->children()->create(['key' => 'min_participants_allowed', 'rule' => "required|lte:max_participants_allowed", 'title' => "Minimum Participants Allowed", 'value' => 10]);
        $competition->children()->create(['key' => 'max_participants_allowed', 'rule' => "required|gte:min_participants_allowed", 'title' => "Maximum Participants Allowed", 'value' => 3000]);
        $competition->children()->create(['key' => 'min_prize_money', 'rule' => "required|numeric|lte:max_prize_money", 'title' => "Minimum Prize Money", 'value' => 300]);
        $competition->children()->create(['key' => 'max_prize_money', 'rule' => "required|numeric|gte:min_prize_money", 'title' => "Maximum Prize Money", 'value' => 3000000]);
        $competition->children()->create(['key' => 'min_entry_fee', 'rule' => "required|numeric|lt:max_prize_money", 'title' => "Minimum Entry Fee", 'value' => 0]);
        $competition->children()->create(['key' => 'max_drafts_allowed', 'rule' => "required|numeric", 'title' => "Post Drafts Per Competition", 'value' => 3]);

        $post = Setting::create(['key' => "post", 'title' => "Post"]);
        $post->children()->create(['key' => 'max_image_size', 'rule' => "required|numeric", 'title' => "Max Image Size (MB)", 'value' => 10]);
        $post->children()->create(['key' => 'no_of_images_allowed', 'rule' => "required|numeric", 'title' => "Allowed Media per post", 'value' => 3]);
        $post->children()->create(['key' => 'image_resize_width', 'rule' => "required|numeric", 'title' => "Image Resize Width (px)", 'value' => 720]);
        $post->children()->create(['key' => 'image_resize_height', 'rule' => "required|numeric", 'title' => "Image Resize Height (px)", 'value' => 480]);
        $post->children()->create(['key' => 'image_quality', 'rule' => "required|numeric", 'title' => "Image Quality (%)", 'value' => 60]);

        $post->children()->create(['key' => 'max_video_size', 'rule' => "required|numeric", 'title' => "Max Video Size (MB)", 'value' => 40]);
        $post->children()->create(['key' => 'video_resize_width', 'rule' => "required|numeric", 'title' => "Video Resize Width (px)", 'value' => 480]);
        $post->children()->create(['key' => 'video_resize_height', 'rule' => "required|numeric", 'title' => "Video Resize Height (px)", 'value' => 320]);
    }
}
