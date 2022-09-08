<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->string('username');
            $table->string('email')->unique();
            $table->string('full_name');
            $table->string('password')->nullable();
            $table->string('phone_code')->nullable();
            $table->integer('phone_no')->nullable();
            $table->enum('type', ['voter', 'organizer', 'participant'])->default("voter");
            $table->double("balance")->default(0);
            $table->text("notification_token")->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->enum('auth_provider', ['email', 'google', 'facebook'])->default("email");
            $table->text("avatar")->nullable();

            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
