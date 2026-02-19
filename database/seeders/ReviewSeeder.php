<?php

namespace Database\Seeders;

use App\Models\Protocol;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Protocol::query()->doesntExist()) {
            $this->call(ProtocolSeeder::class);
        }

        if (User::query()->doesntExist()) {
            User::factory(10)->create();
        }

        $userIds = User::query()->pluck('id');

        Protocol::query()->each(function (Protocol $protocol) use ($userIds): void {
            $reviewerIds = $userIds->random(min(5, $userIds->count()));

            foreach ($reviewerIds as $userId) {
                Review::factory()->create([
                    'protocol_id' => $protocol->id,
                    'user_id' => $userId,
                ]);
            }
        });
    }
}
