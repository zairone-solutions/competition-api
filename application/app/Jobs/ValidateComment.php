<?php

namespace App\Jobs;

use App\Helpers\NotificationHelper;
use App\Models\Category;
use App\Models\PostComment;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ValidateComment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $comment;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(PostComment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $response = Http::post(env("NSFW_API_URL") . 'filter_text', ["text" => $this->comment->text]);

            $result = $response->json();

            if ($result['error'] === false) {

                if ($result['data']['bad_words'] >= 1) {
                    if (((int) $result['data']['total_words'] / (int) $result['data']['bad_words']) < 2) {

                        $this->comment->delete();

                        NotificationHelper::send($this->comment->user_id, $this->comment->user->notification_token, "Bad content warning!", "bad_content", "One of your comment was rated as NSFW (Not Safe For Work), and it is deleted from the post.");
                    } else {
                        $this->comment->update(["text" => $result['data']['processed_text']]);
                    }

                }

            }
        } catch (Exception $e) {
        }
    }
}
