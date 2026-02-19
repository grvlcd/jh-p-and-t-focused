<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Thread::query()->doesntExist()) {
            $this->call(ThreadSeeder::class);
        }

        if (User::query()->doesntExist()) {
            User::factory(10)->create();
        }

        $threadIds = Thread::query()->pluck('id');
        $userIds = User::query()->pluck('id');

        // Top-level comments
        $comments = Comment::factory()
            ->count(30)
            ->state(fn () => [
                'thread_id' => $threadIds->random(),
                'user_id' => $userIds->random(),
                'parent_id' => null,
            ])
            ->create();

        // Replies to random existing comments
        Comment::factory()
            ->count(20)
            ->state(fn () => [
                'thread_id' => $threadIds->random(),
                'user_id' => $userIds->random(),
                'parent_id' => $comments->random()->id,
            ])
            ->create();
    }
}
