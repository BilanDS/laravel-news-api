<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\News;
use App\Models\NewsContentBlock;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Створюємо зручного тестового користувача для логіну
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'), // Пароль: password
        ]);

        // 2. Створюємо ще 10 випадкових користувачів
        User::factory(10)->create()->each(function (User $user) {

            // Кожному створюємо від 3 до 7 новин
            News::factory(rand(3, 7))->create(['user_id' => $user->id])->each(function (News $news) {

                // Кожній новині створюємо від 2 до 4 блоків контенту
                $blocksCount = rand(2, 4);
                for ($i = 1; $i <= $blocksCount; $i++) {
                    NewsContentBlock::factory()->create([
                        'news_id' => $news->id,
                        'order' => $i, // Зберігаємо правильний порядок
                    ]);
                }
            });
        });
    }
}
