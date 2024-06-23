<?php

namespace App\Jobs;

use App\Helpers\NotificationHelper;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ValidateCategory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $category;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $response = Http::post(env("NSFW_API_URL") . 'filter_text', ["text" => $this->category->title]);

            $result = $response->json();

            if ($result['error'] === false) {

                if ($result['data']['bad_words'] == 0) {

                    $this->category->update(["verified" => 1]);

                    NotificationHelper::send($this->category->suggest_id, $this->category->suggested_by->notification_token, "Category verified!", "category_verified", "Your suggested category \"" . $this->category->title . "\" has been verified.", ["category" => CategoryResource::make($this->category)]);
                }

            }
        } catch (Exception $e) {
        }
    }
}
