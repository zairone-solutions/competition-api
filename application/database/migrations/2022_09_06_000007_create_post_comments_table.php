<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_comments', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->foreignId("user_id")->constrained("users")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("post_id")->constrained("posts")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("comment_id")->nullable()->constrained("post_comments")->cascadeOnUpdate()->cascadeOnDelete();

            $table->enum("type", ['comment', 'reply'])->default("comment");
            $table->string("text", 500);
            $table->boolean("hidden")->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('post_comments');
    }
}
