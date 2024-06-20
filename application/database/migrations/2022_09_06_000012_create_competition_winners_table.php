<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompetitionWinnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('competition_winners', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->foreignId("competition_id")->constrained("competitions")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("winner_id")->nullable()->constrained("users")->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('post_votes');
    }
}
