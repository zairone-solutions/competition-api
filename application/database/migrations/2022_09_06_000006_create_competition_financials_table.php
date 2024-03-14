<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompetitionFinancialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('competition_financials', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->foreignId("competition_id")->constrained("competitions")->cascadeOnUpdate()->cascadeOnDelete();

            $table->double("cost")->default(0);
            $table->double("platform_charges")->default(0);
            $table->double("entry_fee")->default(0);
            $table->double("prize_money")->default(0);
            $table->double("discount")->default(0);
            $table->double("total")->default(0);

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
        Schema::dropIfExists('competition_comments');
    }
}
