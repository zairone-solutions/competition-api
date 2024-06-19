<?php

namespace App\Jobs\Media;

use Ably\AblyRest;
use App\Http\Resources\PostResource;
use App\Models\Competition;
use App\Models\Post;
use App\Models\PostMedia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UnlinkTemporaryMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $temporaryFilePath;
    protected $media;
    protected $competition;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($temporaryFilePath, Competition $competition, PostMedia $media)
    {
        $this->temporaryFilePath = $temporaryFilePath;
        $this->competition = $competition;
        $this->media = $media;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        unlink(storage_path("app/" . $this->temporaryFilePath));

        $ably = new AblyRest(config('broadcasting.connections.ably.key'));

        $post = Post::findOrFail($this->media->post_id);

        $channel = $ably->channels->get("post-updated");
        $channel->publish('competition-' . $this->competition->id . "-post-" . $this->media->post_id, ['post' => PostResource::make($post)]);

    }
}
