<?php

namespace App\Jobs;

use App\Helpers\RuleHelper;
use App\Jobs\Media\UnlinkTemporaryMedia;
use App\Models\Competition;
use App\Models\PostMedia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use FFMpeg;
use Illuminate\Support\Facades\DB;

class UploadVideoToS3 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $media;
    protected $competition;
    protected $path;
    protected $temporaryFilePath;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Competition $competition, PostMedia $media, string $path, string $temporaryFilePath)
    {
        $this->competition = $competition;
        $this->media = $media;
        $this->path = $path;
        $this->temporaryFilePath = $temporaryFilePath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $post_rules = RuleHelper::rules("post");

        $encodedVideo = FFMpeg::fromDisk('local')
            ->open($this->temporaryFilePath)
            ->export()
            ->toDisk('s3')
            ->inFormat(new FFMpeg\Format\Video\X264)
            ->resize((int) $post_rules['video_resize_width'], (int) $post_rules['video_resize_height'], \FFMpeg\Filters\Video\ResizeFilter::RESIZEMODE_SCALE_HEIGHT)
            ->addFilter(['-crf', 28])
            ->save($this->path);

        $aws_path = Storage::disk('s3')->url($this->path);

        DB::beginTransaction();

        if ($encodedVideo) {
            $this->media->update(["media" => $aws_path]);

            UnlinkTemporaryMedia::dispatch($this->temporaryFilePath, $this->competition, $this->media);

            DB::commit();
        }
    }
}
