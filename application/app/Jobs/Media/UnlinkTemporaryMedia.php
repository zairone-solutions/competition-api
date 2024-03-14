<?php

namespace App\Jobs\Media;

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
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($temporaryFilePath)
    {
        $this->temporaryFilePath = $temporaryFilePath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        unlink(storage_path("app/" . $this->temporaryFilePath));
    }
}
