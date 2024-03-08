<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId("parent_id")->nullable()->constrained("settings")->cascadeOnUpdate()->nullOnDelete();

            $table->string("key")->unique();
            $table->enum("type", ['input', 'textarea', 'select'])->default("input");
            $table->string("title");
            $table->string("rule")->default("required");
            $table->mediumText("value")->nullable();
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
        Schema::dropIfExists('settings');
    }
}
