<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Competition;
use App\Models\PaymentMethod;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class DatabaseSeeder extends Seeder
{

    private User $organizer;
    private PaymentMethod $paymentMethod;
    private $faker;

    public function __construct()
    {
        $this->faker = \Faker\Factory::create();

    }

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([UsersTableSeeder::class, SettingsSeeder::class]);


        // create a user
        $organizer = User::create([
            'username' => 'organizer.user',
            'email' => 'organizer.user@gmail.com',
            'full_name' => 'Organizer User',
            'email_verified_at' => date_format($this->faker->dateTimeBetween("-5 years"), 'Y-m-d H:i:s'),
            'auth_provider' => 'email',
            'password' => Hash::make("secret_pass"),
            'avatar' => 'https://img.freepik.com/premium-photo/graphic-designer-digital-avatar-generative-ai_934475-9292.jpg',
        ]);
        $this->organizer = $organizer;

        $participant1 = User::create([
            'username' => 'participant1.user',
            'email' => 'participant1.user@gmail.com',
            'full_name' => 'Participant User',
            'email_verified_at' => date_format($this->faker->dateTimeBetween("-5 years"), 'Y-m-d H:i:s'),
            'auth_provider' => 'email',
            'password' => Hash::make("secret_pass"),
            'avatar' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQOg1GQ7pg6qagSzJv9EhanSkqMrUj_3Q6p6Q&s',
        ]);
        $participant2 = User::create([
            'username' => 'participant2.user',
            'email' => 'participant2.user@gmail.com',
            'full_name' => 'Participant User',
            'email_verified_at' => date_format($this->faker->dateTimeBetween("-5 years"), 'Y-m-d H:i:s'),
            'auth_provider' => 'email',
            'password' => Hash::make("secret_pass"),
            'avatar' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGMNCUbrpereDWZjrBeH_Xsspa8S_F9ifgFs9MLLFw8eq11EPqJG2kGPTv0YA3U1qp_1A&usqp=CAU',
        ]);
        $participant3 = User::create([
            'username' => 'participant3.user',
            'email' => 'participant3.user@gmail.com',
            'full_name' => 'Participant User',
            'email_verified_at' => date_format($this->faker->dateTimeBetween("-5 years"), 'Y-m-d H:i:s'),
            'auth_provider' => 'email',
            'password' => Hash::make("secret_pass"),
            'avatar' => 'https://img.freepik.com/free-photo/portrait-businessman-with-glasses-mustache-3d-rendering_1142-43442.jpg',
        ]);
        $voter1 = User::create([
            'username' => 'voter1.user',
            'email' => 'voter1.user@gmail.com',
            'full_name' => 'Voter User',
            'email_verified_at' => date_format($this->faker->dateTimeBetween("-5 years"), 'Y-m-d H:i:s'),
            'auth_provider' => 'email',
            'password' => Hash::make("secret_pass"),
            'avatar' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQo6nmwUNova1Yli3loNeu037N6k1fYhQBcHwFNqyIGN4QmmxrPlBhhHtFpYkhOp7W-LPA&usqp=CAU',
        ]);

        $voter2 = User::create([
            'username' => 'voter2.user',
            'email' => 'voter2.user@gmail.com',
            'full_name' => 'Voter User',
            'email_verified_at' => date_format($this->faker->dateTimeBetween("-5 years"), 'Y-m-d H:i:s'),
            'auth_provider' => 'email',
            'password' => Hash::make("secret_pass"),
            'avatar' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQJbx8R2dK1COaBPmt5fX8o5Y2eus-Zo-gVghUOdn5GRv0zaWFX9odtmM0VZwD3keRNDbk&usqp=CAU',
        ]);

        $voter3 = User::create([
            'username' => 'voter3.user',
            'email' => 'voter3.user@gmail.com',
            'full_name' => 'Voter User',
            'email_verified_at' => date_format($this->faker->dateTimeBetween("-5 years"), 'Y-m-d H:i:s'),
            'auth_provider' => 'email',
            'password' => Hash::make("secret_pass"),
            'avatar' => 'https://t3.ftcdn.net/jpg/05/90/59/88/360_F_590598870_TOcGd4cUZzPoEMlxSc7XYwcupHOE0vLM.jpg',
        ]);



        // create categories
        $category1 = Category::create(['title' => 'Cars', 'slug' => 'cars', 'verified' => 1]);
        $category2 = Category::create(['title' => 'Memes', 'slug' => 'memes', 'verified' => 1]);
        $category3 = Category::create(['title' => 'Sports', 'slug' => 'sports', 'verified' => 1]);
        $category4 = Category::create(['title' => 'Fashion', 'slug' => 'fashion', 'verified' => 1]);
        $category5 = Category::create(['title' => 'Photography', 'slug' => 'photography', 'verified' => 1]);

        // create payment methods
        $payment_method1 = PaymentMethod::create(['title' => "JazzCash", 'code' => "JC", 'credentials' => json_encode(['mobile_no' => "03061245658"])]);
        $payment_method2 = PaymentMethod::create(['title' => "Easypaisa", 'code' => "EP", 'credentials' => json_encode(['mobile_no' => "03061245658"])]);
        $payment_method3 = PaymentMethod::create(['title' => "Card", 'code' => "CC", 'credentials' => json_encode(['IBAN' => "DE68500105178297336485"])]);
        $this->paymentMethod = $payment_method1;

        // create a competition
        $competition1 = $this->createCompetition($category1, "Sargodha Cars Competition");
        $competition2 = $this->createCompetition($category2, "Best 2024 Memes");
        $competition3 = $this->createCompetition($category5, "Mountain Photography");
        $competition4 = $this->createCompetition($category4, "June Fashion Week");
        $competition5 = $this->createCompetition($category3, "Horse Riding");
        $competition6 = $this->createCompetition($category2, "Cricket WC Memes", true);

        // make user an organizer if he is not
        if ($organizer->type !== "organizer") {
            $organizer->update(['type' => 'organizer']);
        }

        // participant can participate
        $this->participate($participant1, $competition1);
        $this->participate($participant1, $competition2);
        $this->participate($participant1, $competition3);
        $this->participate($participant1, $competition4);
        $this->participate($participant1, $competition5);

        $this->participate($participant2, $competition1);
        $this->participate($participant2, $competition2);
        $this->participate($participant2, $competition3);
        $this->participate($participant2, $competition4);
        $this->participate($participant2, $competition5);

        $this->participate($participant3, $competition1);
        $this->participate($participant3, $competition2);
        $this->participate($participant3, $competition3);
        $this->participate($participant3, $competition4);
        $this->participate($participant3, $competition5);

        // participant can post
        $post1 = $this->createPost($participant1, $competition1);
        $post2 = $this->createPost($participant1, $competition2);
        $post3 = $this->createPost($participant1, $competition3);
        $post4 = $this->createPost($participant1, $competition4);
        $post5 = $this->createPost($participant1, $competition5);

        $post6 = $this->createPost($participant2, $competition1);
        $post7 = $this->createPost($participant2, $competition2);
        $post8 = $this->createPost($participant2, $competition3);
        $post9 = $this->createPost($participant2, $competition4);
        $post10 = $this->createPost($participant2, $competition5);

        $post11 = $this->createPost($participant3, $competition1);
        $post12 = $this->createPost($participant3, $competition2);
        $post13 = $this->createPost($participant3, $competition3);
        $post14 = $this->createPost($participant3, $competition4);
        $post15 = $this->createPost($participant3, $competition5);

        // voter can vote
        $voter1->votes()->create(['competition_id' => $competition1->id, 'post_id' => $post1->id]);
        $voter1->votes()->create(['competition_id' => $competition2->id, 'post_id' => $post2->id]);
        $voter1->votes()->create(['competition_id' => $competition3->id, 'post_id' => $post3->id]);
        $voter1->votes()->create(['competition_id' => $competition4->id, 'post_id' => $post4->id]);
        $voter1->votes()->create(['competition_id' => $competition5->id, 'post_id' => $post5->id]);

        $voter2->votes()->create(['competition_id' => $competition1->id, 'post_id' => $post6->id]);
        $voter2->votes()->create(['competition_id' => $competition2->id, 'post_id' => $post7->id]);
        $voter2->votes()->create(['competition_id' => $competition3->id, 'post_id' => $post8->id]);
        $voter2->votes()->create(['competition_id' => $competition4->id, 'post_id' => $post9->id]);
        $voter2->votes()->create(['competition_id' => $competition5->id, 'post_id' => $post10->id]);

        $voter3->votes()->create(['competition_id' => $competition1->id, 'post_id' => $post1->id]);
        $voter3->votes()->create(['competition_id' => $competition2->id, 'post_id' => $post2->id]);
        $voter3->votes()->create(['competition_id' => $competition3->id, 'post_id' => $post3->id]);
        $voter3->votes()->create(['competition_id' => $competition4->id, 'post_id' => $post4->id]);
        $voter3->votes()->create(['competition_id' => $competition5->id, 'post_id' => $post5->id]);

        // posts will win
        $this->postWon($post1);
        $this->postWon($post2);
        $this->postWon($post3);
        $this->postWon($post4);
        $this->postWon($post5);

        // posts have comments
        $this->createComments($post1, [$organizer, $participant1, $participant2, $participant3, $voter1, $voter2, $voter3]);
        $this->createComments($post2, [$organizer, $participant1, $participant2, $participant3, $voter1, $voter2, $voter3]);
        $this->createComments($post3, [$organizer, $participant1, $participant2, $participant3, $voter1, $voter2, $voter3]);
        $this->createComments($post4, [$organizer, $participant1, $participant2, $participant3, $voter1, $voter2, $voter3]);
        $this->createComments($post5, [$organizer, $participant1, $participant2, $participant3, $voter1, $voter2, $voter3]);
        $this->createComments($post6, [$organizer, $participant1, $participant2, $participant3, $voter1, $voter2, $voter3]);
        $this->createComments($post7, [$organizer, $participant1, $participant2, $participant3, $voter1, $voter2, $voter3]);
        $this->createComments($post8, [$organizer, $participant1, $participant2, $participant3, $voter1, $voter2, $voter3]);
        $this->createComments($post9, [$organizer, $participant1, $participant2, $participant3, $voter1, $voter2, $voter3]);
        $this->createComments($post10, [$organizer, $participant1, $participant2, $participant3, $voter1, $voter2, $voter3]);
        $this->createComments($post11, [$organizer, $participant1, $participant2, $participant3, $voter1, $voter2, $voter3]);
        $this->createComments($post12, [$organizer, $participant1, $participant2, $participant3, $voter1, $voter2, $voter3]);
        $this->createComments($post13, [$organizer, $participant1, $participant2, $participant3, $voter1, $voter2, $voter3]);
        $this->createComments($post14, [$organizer, $participant1, $participant2, $participant3, $voter1, $voter2, $voter3]);
        $this->createComments($post15, [$organizer, $participant1, $participant2, $participant3, $voter1, $voter2, $voter3]);
    }

    private function createCompetition(Category $category, string $title, bool $upForParticipation = false): Competition
    {
        $cost_per_participant_setting = Setting::where("key", "cost_per_participant")->first();
        $platform_charges_setting = Setting::where("key", "platform_charges")->first();

        $competition = $this->organizer->competitions()->create([
            "category_id" => $category->id,
            "title" => $title,
            "slug" => Str::slug($title),
            "participants_allowed" => 500,
            "announcement_at" => date_format($this->faker->dateTimeBetween("+3 days", "+14 days"), "Y-m-d H:i:s"),
            "voting_start_at" => date_format($this->faker->dateTimeBetween($upForParticipation ? "+1 day" : "now", "+3 days"), "Y-m-d H:i:s"),
            "published_at" => date_format($this->faker->dateTimeBetween("now"), "Y-m-d H:i:s"),
        ]);

        $cost = (int) $competition->participants_allowed * (int) $cost_per_participant_setting->value;
        $entry_fee = rand(0, 1) > 0.5 ? rand(1, 10) * 10 : 0;
        $platform_charges = (int) $platform_charges_setting->value;
        $prize_money = rand(1, 10) * 1000;
        $total = $cost + $platform_charges + $prize_money;

        $competition->financial()->create([
            "cost" => $cost,
            "total" => $total,
            "entry_fee" => $entry_fee,
            "platform_charges" => $platform_charges,
            "prize_money" => $prize_money,
        ]);

        $payment = $this->organizer->payments()->create([
            'competition_id' => $competition->id,
            'method_id' => $this->paymentMethod->id,
            'title' => $this->organizer->username . " paid competition hosting fee",
            'amount' => $competition->financial->cost
        ]);
        $payment->update(["verified_at" => date_format($this->faker->dateTimeBetween("now"), "Y-m-d H:i:s")]);

        $ledger = $this->organizer->ledgers()->create([
            'payment_id' => $payment->id,
            'title' => $payment->title,
            'amount' => $payment->amount,
            'type' => 'debit',
        ]);

        $competition->update(["payment_verified_at" => date_format($this->faker->dateTimeBetween("now"), "Y-m-d H:i:s")]);

        return $competition;
    }

    private function createPost(User $participant, Competition $competition): Post
    {
        $post = $participant->posts()->create([
            'competition_id' => $competition->id,
            'description' => $this->faker->sentence(),
            'hidden' => 0,
            'state' => "voted",
            'approved_at' => date_format($this->faker->dateTimeBetween("now", "+1 days"), 'Y-m-d H:i:s')
        ]);

        $media = [];
        switch ($competition->category->slug) {
            case 'cars':
                $media = [
                    "https://images.unsplash.com/photo-1525609004556-c46c7d6cf023?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxzZWFyY2h8M3x8Y2Fyc3xlbnwwfHwwfHw%3D&w=1000&q=80",
                    "https://images.unsplash.com/photo-1494976388531-d1058494cdd8?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxzZWFyY2h8Mnx8Y2Fyc3xlbnwwfHwwfHw%3D&w=1000&q=80",
                    "https://images.unsplash.com/photo-1553440569-bcc63803a83d?q=80&w=1425&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1514316454349-750a7fd3da3a?q=80&w=1374&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1493238792000-8113da705763?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1571607388263-1044f9ea01dd?q=80&w=1395&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1630312465536-5c6b1f76dc3f?q=80&w=1527&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1600510424051-30d592a75353?q=80&w=1374&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1593219535889-7873a100874a?q=80&w=1374&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1592198084033-aade902d1aae?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                ];
                break;

            case 'memes':
                $media = [
                    "https://images.unsplash.com/photo-1505628346881-b72b27e84530?q=80&w=1374&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1507808973436-a4ed7b5e87c9?q=80&w=1480&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://i.pinimg.com/736x/d4/b6/63/d4b663fd38607e24a210c2d1b0b5c6b7.jpg",
                    "https://i.pinimg.com/736x/0e/15/84/0e1584bc8264931193f2d92065834ae0.jpg",
                    "https://i.pinimg.com/736x/60/76/56/6076564599665ac585e2dcc53cba566e.jpg",
                    "https://i.pinimg.com/736x/69/db/21/69db21bb7828f14cd47b6bc7c46b5075.jpg",
                    "https://i.pinimg.com/736x/80/e2/fe/80e2fe6e9e936fe8b71b0b912b520f1d.jpg",
                    "https://i.pinimg.com/736x/20/fa/df/20fadf78e217b0b210c9f8d2a1c63bd2.jpg",
                    "https://i.pinimg.com/736x/05/a6/fc/05a6fcbf52e335395942fa2fbe18ba90.jpg",
                    "https://i.pinimg.com/736x/b2/11/0c/b2110c1dfc2afef7d4e797086dd09777.jpg"
                ];
                break;

            case 'sports':
                $media = [
                    "https://images.unsplash.com/photo-1605264522799-1996bdbe5f72?q=80&w=1374&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1613085411234-9c83af5562d8?q=80&w=1408&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/flagged/photo-1568381670226-fab8dc323343?q=80&w=1374&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1593357116960-06ca2550f4ac?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1512934772407-b292436089ee?q=80&w=1502&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1552406992-93397f876bcf?q=80&w=1374&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1619368202270-e8c5569590f3?q=80&w=1452&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1546700990-7b6416f2d90c?q=80&w=1370&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1526038039141-92d734991065?q=80&w=1374&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1523222167982-0b227abf728d?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1526039003500-f2f1cd73570e?q=80&w=1446&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                ];
                break;

            case "fashion":
                $media = [
                    "https://images.unsplash.com/photo-1554881070-74595ca2b74c?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1554882195-8cf792f9a571?q=80&w=1471&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1539109136881-3be0616acf4b?q=80&w=1374&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1634921276487-c9651116f473?q=80&w=1528&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1634922797538-3f8e4c26ed1e?q=80&w=1528&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1634922797463-a957abb73cfe?q=80&w=1530&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1634922797410-3d2e3bbaab92?q=80&w=1528&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://plus.unsplash.com/premium_photo-1683133958411-8c852bf26e19?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://plus.unsplash.com/premium_photo-1663045499038-cc92f8ae4862?q=80&w=1471&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1634133118553-1e6e18299886?q=80&w=1374&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"

                ];
                break;

            case "photography":
                $media = [
                    "https://images.unsplash.com/photo-1613456806102-6d5ef869f75e?q=80&w=1374&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1595221895661-11682941909b?q=80&w=1476&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1597165261740-2f3d8dc22a0d?q=80&w=1471&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1597165261740-2f3d8dc22a0d?q=80&w=1471&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1643318882652-e1190f4d6ff5?q=80&w=1374&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1582274452372-a453019a483f?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1615244609008-96bc4c0def38?q=80&w=1632&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1567776165715-7a908e2df0e2?q=80&w=1375&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://plus.unsplash.com/premium_photo-1676489458216-938159ba8091?q=80&w=1374&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                    "https://images.unsplash.com/photo-1531582785230-77676720425c?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                ];
                break;
            default:
                break;
        }

        $post->media()->createMany(array_map(fn($item) => ["media" => $media[rand(0, count($media) - 1)]], [1, 2, 3]));

        return $post;
    }
    private function participate(User $participant, Competition $competition)
    {
        $payment = $participant->payments()->create([ // make payment
            'competition_id' => $competition->id,
            'method_id' => $this->paymentMethod->id,
            'title' => $this->organizer->username . " paid competition participating fee",
            'amount' => $competition->financial->entry_fee
        ]);

        $payment->update(["verified_at" => date_format($this->faker->dateTimeBetween("now"), "Y-m-d H:i:s")]);

        $ledger = $this->organizer->ledgers()->create([ // update ledger
            'payment_id' => $payment->id,
            'title' => $payment->title,
            'amount' => $payment->amount,
            'type' => 'debit',
        ]);

        $competition->participants()->create(['participant_id' => $participant->id]);
    }
    private function postWon(Post $post)
    {
        $post->update(["won" => 1]);
        $post->competition()->update(["winner_id" => $post->user->id]);
    }
    private function createComments(Post $post, array $users)
    {
        $comments = [];
        for ($i = 0; $i < 30; $i++) {
            $user = $users[rand(0, count($users) - 1)];

            if (rand(0, 1) > 0) {
                // comment
                $comments[] = $user->post_comments()->create(["text" => $this->faker->sentence(), 'post_id' => $post->id]);
            } else {
                // reply
                if (count($comments)) {
                    $comment = $comments[rand(0, count($comments) - 1)];
                    $comment->replies()->create(["text" => $this->faker->sentence(), 'user_id' => $user->id, "type" => "reply", 'post_id' => $post->id]);
                }

            }
        }
    }


}
