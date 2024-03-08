<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;

class SendRegisterEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            echo "Sending Email...\n";

            $faker = \Faker\Factory::create();

            $participant = User::create([
                'username' =>  $faker->userName,
                'email' =>  $faker->email,
                'full_name' => 'Participant User',
                'email_verified_at' => date_format($faker->dateTimeBetween("-5 years"), 'Y-m-d H:i:s'),
                'auth_provider' => 'email',
                'password' => Hash::make("secret_pass"),
                'avatar' => 'https://coursebari.com/wp-content/uploads/2021/06/899048ab0cc455154006fdb9676964b3.jpg',
            ]);
            echo "Sent!\n";
        } catch (\Throwable $th) {
            echo "Send Email: " . $th->getMessage();
        }
    }
}
