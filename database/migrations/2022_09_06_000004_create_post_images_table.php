<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_images', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->foreignId("post_id")->constrained("posts")->cascadeOnUpdate()->cascadeOnDelete();

            $table->text("image");
            $table->boolean("approved")->default(0);
            $table->string("mime_type", 50)->default("jpg");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('post_images');
    }
}
