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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Image;

class UploadImageToS3 implements ShouldQueue
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

        $resizedImage = Image::make(public_path(str_replace("public", "storage", $this->temporaryFilePath)))
            ->resize((int) $post_rules['image_resize_width'] ?? 720, (int) $post_rules['image_resize_height'] ?? 480, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode(null, (int) $post_rules['image_quality'] ?? 60);

        Storage::disk('s3')->put($this->path, $resizedImage);

        $aws_path = Storage::disk('s3')->url($this->path);

        DB::beginTransaction();

        $this->media->update(["media" => $aws_path]);

        UnlinkTemporaryMedia::dispatch($this->temporaryFilePath, $this->competition, $this->media);

        DB::commit();
    }
}
