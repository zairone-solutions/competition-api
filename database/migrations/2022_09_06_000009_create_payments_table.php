<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->foreignId("competition_id")->nullable()->constrained("competitions")->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId("user_id")->nullable()->constrained("users")->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId("method_id")->nullable()->constrained("payment_methods")->cascadeOnUpdate()->nullOnDelete();

            $table->enum("type", ['to', 'from'])->default("from");
            $table->string("title")->nullable();
            $table->string("device")->nullable();
            $table->double("discount")->default(0);
            $table->double("amount");
            $table->dateTime("verified_at")->nullable();
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
        Schema::dropIfExists('payments');
    }
}
