<?php

namespace Database\Seeders;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Database\Seeder;

class DeviceTokenSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->whereIn('role', ['CUSTOMER', 'FARMER'])->orderBy('id')->get();

        foreach ($users as $index => $user) {
            DeviceToken::query()->create([
                'user_id' => $user->id,
                'token' => 'seed-device-'.$user->id.'-'.$user->role,
                'platform' => $index % 2 === 0 ? 'android' : 'ios',
            ]);
        }
    }
}
