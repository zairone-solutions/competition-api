<?php

namespace App\Jobs\Media;

use App\Helpers\NotificationHelper;
use App\Helpers\RealTimeHelper;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\PostMedia;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class CheckNSFWimage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $media;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(PostMedia $media)
    {
        $this->media = $media;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $load_image = Http::get($this->media->media);

            if ($load_image->successful()) {
                $imageContents = $load_image->body();
                $imageName = 'temp_' . uniqid() . '.jpg';
                $response = Http::attach(
                    'image',
                    $imageContents,
                    $imageName

                )->post(env("NSFW_API_URL") . 'filter_image');

                $result = $response->json();

                if ($result['error'] === false) {

                    if ($result['data']['score'] > 0.5) {
                        $this->media->delete();

                        $post = Post::findOrFail($this->media->post_id);

                        RealTimeHelper::sendMessage("post-updated", 'competition-' . $post->competition_id . "-post-" . $this->media->post_id, ['post' => PostResource::make($post)]);

                        NotificationHelper::send($post->user_id, $post->user->notification_token, "Bad content warning!", "bad_content", "Your uploaded media was rated as NSFW (Not Safe For Work), and it is deleted from the draft.");

                    }

                }
            }

        } catch (Exception $e) {
        }
    }
}
