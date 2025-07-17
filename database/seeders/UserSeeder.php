<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    protected static ?string $password;
    public function run(): void
    {
        // Insert user
        $userId = DB::table('users')->insertGetId([
            'name' => 'Deniel Tomenio',
            'username' => 'Tomenio',
            'position' => 'Administrator',  // <-- REQUIRED!
            'active' => true,
            'role_id' => 1, // Assuming role_id 2 is for admin
            'created_at' => now(),
            'updated_at' => now(),
            'password' => Hash::make('admin'), // securely hash password
            'remember_token' => Str::random(10),
        ]);

        // Insert corresponding user_rsp_control
       DB::table('user_rsp_control')->insert([
            'user_id' => $userId,
            'isFunded' => true,
            'isUserM' => true,
            'isRaterM' => true,
            'isCriteria' => true,
            'isDashboardStat' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
