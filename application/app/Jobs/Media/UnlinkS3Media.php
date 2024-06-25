<?php

namespace App\Jobs\Media;

use Ably\AblyRest;
use App\Helpers\CompetitionHelper;
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
use Illuminate\Support\Facades\Storage;

class UnlinkS3Media implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $url;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $uri = CompetitionHelper::extractUri($this->url);

        if (Storage::disk('s3')->exists($uri)) {
            Storage::disk('s3')->delete($uri);
        }
    }
}
