<?php

namespace Database\Seeders;

use App\Models\Protocol;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Seeder;

class ThreadSeeder extends Seeder
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

        $protocolIds = Protocol::query()->pluck('id');
        $userIds = User::query()->pluck('id');

        Thread::factory()
            ->count(10)
            ->state(fn () => [
                'protocol_id' => $protocolIds->random(),
                'user_id' => $userIds->random(),
            ])
            ->create();
    }
}
