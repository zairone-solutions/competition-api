<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'username' => 'admin',
            'full_name' => 'Admin Admin',
            'email' => 'admin@uniquo.com',
            'email_verified_at' => now(),
            'type' => "admin",
            'password' => Hash::make('secret'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // $faker = \Faker\Factory::create();

        // for ($i = 0; $i < 100; $i++) {
        //     $test_data = array();
        //     for ($j = 1; $j < 100; $j++) {
        //         $username = $faker->userName . rand(111, 999);
        //         $test_data[] = [
        //             'username' => $username,
        //             'email' => $username . "-" . $faker->email,
        //             'full_name' => ucwords($username),
        //             'email_verified_at' => date_format($faker->dateTimeBetween("-5 years"), 'Y-m-d H:i:s'),
        //             'auth_provider' => 'email',
        //             'password' => Hash::make("secret_pass")
        //         ];
        //     }
        //     DB::table('users')->insert($test_data);
        // }
        // User::factory(1000)->create();
    }
}
