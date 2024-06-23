<?php

namespace App\Jobs\Media;

use App\Models\Post;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class CheckNSFWtext implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $post;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $response = Http::post(env("NSFW_API_URL") . 'filter_text', ["text" => $this->post->description]);

            $result = $response->json();

            if ($result['error'] === false) {

                if ($result['data']['bad_words'] >= 1) {
                    $this->post->update(['description' => $result['data']['processed_text']]);
                }

            }
        } catch (Exception $e) {
        }
    }
}
