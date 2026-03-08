<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\NewsContentBlock;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $randomUsers = User::factory(10)->create();

        $allUsers = $randomUsers->push($testUser);

        $allUsers->each(function (User $user) {

            News::factory(rand(3, 7))->create(['user_id' => $user->id])->each(function (News $news) {

                $blocksCount = rand(2, 4);
                for ($i = 1; $i <= $blocksCount; $i++) {
                    NewsContentBlock::factory()->create([
                        'news_id' => $news->id,
                        'order' => $i,
                    ]);
                }
            });
        });
    }
}
