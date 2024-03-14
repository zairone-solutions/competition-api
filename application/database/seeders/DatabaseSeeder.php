<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([UsersTableSeeder::class, SettingsSeeder::class]);

        $faker = \Faker\Factory::create();

        // create a user
        $organizer = User::create([
            'username' => 'organizer.user',
            'email' => 'organizer.user@gmail.com',
            'full_name' => 'Organizer User',
            'email_verified_at' => date_format($faker->dateTimeBetween("-5 years"), 'Y-m-d H:i:s'),
            'auth_provider' => 'email',
            'password' => Hash::make("secret_pass"),
            'avatar' => 'https://coursebari.com/wp-content/uploads/2021/06/899048ab0cc455154006fdb9676964b3.jpg',
        ]);

        $participant = User::create([
            'username' => 'participant.user',
            'email' => 'participant.user@gmail.com',
            'full_name' => 'Participant User',
            'email_verified_at' => date_format($faker->dateTimeBetween("-5 years"), 'Y-m-d H:i:s'),
            'auth_provider' => 'email',
            'password' => Hash::make("secret_pass"),
            'avatar' => 'https://coursebari.com/wp-content/uploads/2021/06/899048ab0cc455154006fdb9676964b3.jpg',
        ]);
        $voter = User::create([
            'username' => 'voter.user',
            'email' => 'voter.user@gmail.com',
            'full_name' => 'Voter User',
            'email_verified_at' => date_format($faker->dateTimeBetween("-5 years"), 'Y-m-d H:i:s'),
            'auth_provider' => 'email',
            'password' => Hash::make("secret_pass"),
            'avatar' => 'https://coursebari.com/wp-content/uploads/2021/06/899048ab0cc455154006fdb9676964b3.jpg',
        ]);

        // create categories
        $category1 = Category::create(['title' => 'Cars', 'slug' => 'cars', 'verified' => 1]);
        $category2 = Category::create(['title' => 'Fashion', 'slug' => 'fashion', 'verified' => 1]);

        // create payment methods
        $payment_method1 = PaymentMethod::create(['title' => "Easypaisa", 'code' => "EP", 'credentials' => serialize(['mobile_no' => "03061245658"])]);
        $payment_method2 = PaymentMethod::create(['title' => "Credit Card", 'code' => "CC", 'credentials' => serialize(['IBAN' => "03061245658"])]);

        // create a competition
        $competition1 = $organizer->competitions()->create([
            "category_id" => $category1->id,
            "title" => "Sargodha Cars Competition",
            "slug" => "sargodha-cars-competition",
            "participants_allowed" => 500,
            "announcement_at" => date_format($faker->dateTimeBetween("now", "+14 days"), "Y-m-d H:i:s"),
            "voting_start_at" => date_format($faker->dateTimeBetween("now", "+3 days"), "Y-m-d H:i:s"),
            "published_at" => date_format($faker->dateTimeBetween("now"), "Y-m-d H:i:s"),
        ]);
        $competition1->financial()->create([
            "cost" => 5000,
            "total" => 10000,
            "entry_fee" => 100,
            "platform_charges" => 150,
            "prize_money" => 5000,
        ]);

        // make a payment
        $payment1 = $organizer->payments()->create([
            'competition_id' => $competition1->id,
            'method_id' => $payment_method1->id,
            'title' => $organizer->username . " paid competition hosting fee",
            'amount' => $competition1->financial->cost
        ]);
        // make user an organizer if he is not
        if ($organizer->type !== "organizer") {
            $organizer->update(['type' => 'organizer']);
        }
        // also update the ledger
        $ledger1 = $organizer->ledgers()->create([
            'payment_id' => $payment1->id,
            'title' => $payment1->title,
            'amount' => $payment1->amount,
            'type' => 'debit',
        ]);

        // participant can participate
        $payment2 = $participant->payments()->create([ // make payment
            'competition_id' => $competition1->id,
            'method_id' => $payment_method1->id,
            'title' => $organizer->username . " paid competition participating fee",
            'amount' => $competition1->financial->entry_fee
        ]);
        $ledger2 = $organizer->ledgers()->create([ // update ledger
            'payment_id' => $payment2->id,
            'title' => $payment2->title,
            'amount' => $payment2->amount,
            'type' => 'debit',
        ]);
        $competition1->participants()->create(['participant_id' => $participant->id]);

        // participant can post
        $post1 = $participant->posts()->create([
            'competition_id' => $competition1->id,
            'description' => "Lorem ipsum dolor sit amet, consectetur adipisicing elit",
            'hidden' => 0,
            'approved_at' => date_format($faker->dateTimeBetween("now", "+1 days"), 'Y-m-d H:i:s')
        ]);
        $post1->media()->createMany([ // post images
            ['media' => "https://images.unsplash.com/photo-1525609004556-c46c7d6cf023?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxzZWFyY2h8M3x8Y2Fyc3xlbnwwfHwwfHw%3D&w=1000&q=80"],
            ['media' => "https://images.unsplash.com/photo-1494976388531-d1058494cdd8?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxzZWFyY2h8Mnx8Y2Fyc3xlbnwwfHwwfHw%3D&w=1000&q=80"]
        ]);

        // voter can vote
        $voter->votes()->create(['competition_id' => $competition1->id, 'post_id' => $post1->id,]);

        // competition has a discussion
        $comment1 = $voter->post_comments()->create(["text" => "I have a query about this competition!", 'post_id' => $post1->id]);
        $comment1->replies()->create(["text" => "Yes, you can ask..", 'user_id' => $organizer->id, "type" => "reply", 'post_id' => $post1->id]);
        $comment1->replies()->create(["text" => "Nothing.", 'user_id' => $voter->id, "type" => "reply", 'post_id' => $post1->id]);

        $comment2 = $participant->post_comments()->create(["text" => "Guys! come on vote me", 'post_id' => $post1->id]);
        $comment2->replies()->create(["text" => "Good luck for he voting", 'user_id' => $organizer->id, "type" => "reply", 'post_id' => $post1->id]);
    }
}
