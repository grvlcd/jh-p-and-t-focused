<?php

namespace Database\Seeders;

use App\Models\Protocol;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProtocolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (User::query()->doesntExist()) {
            User::factory(10)->create();
        }

        Protocol::factory()
            ->count(12)
            ->create();
    }
}
