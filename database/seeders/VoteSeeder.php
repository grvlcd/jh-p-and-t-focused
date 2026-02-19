<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Thread;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Seeder;

class VoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Thread::query()->doesntExist()) {
            $this->call(ThreadSeeder::class);
        }

        if (Comment::query()->doesntExist()) {
            $this->call(CommentSeeder::class);
        }

        if (User::query()->doesntExist()) {
            User::factory(10)->create();
        }

        $userIds = User::query()->pluck('id');

        Thread::query()->each(function (Thread $thread) use ($userIds): void {
            $voterIds = $userIds->random(min(8, $userIds->count()));

            foreach ($voterIds as $userId) {
                Vote::query()->updateOrCreate(
                    [
                        'user_id' => $userId,
                        'votable_id' => $thread->id,
                        'votable_type' => Thread::class,
                    ],
                    [
                        'value' => fake()->randomElement([-1, 1]),
                    ],
                );
            }
        });

        Comment::query()->each(function (Comment $comment) use ($userIds): void {
            $voterIds = $userIds->random(min(5, $userIds->count()));

            foreach ($voterIds as $userId) {
                Vote::query()->updateOrCreate(
                    [
                        'user_id' => $userId,
                        'votable_id' => $comment->id,
                        'votable_type' => Comment::class,
                    ],
                    [
                        'value' => fake()->randomElement([-1, 1]),
                    ],
                );
            }
        });
    }
}
