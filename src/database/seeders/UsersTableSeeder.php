<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use DateTime;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => '鈴木　一郎',
                'email' => 'user1@test.com',
                'email_verified_at' => new DateTime(),
                'password' => Hash::make('testtest'),
                'role' => 'user',
            ],
            [
                'name' => '田中　花子',
                'email' => 'user2@test.com',
                'email_verified_at' => new DateTime(),
                'password' => Hash::make('testtest'),
                'role' => 'user',
            ],
            [
                'name' => '木村　次郎',
                'email' => 'user3@test.com',
                'email_verified_at' => new DateTime(),
                'password' => Hash::make('testtest'),
                'role' => 'user',
            ],
            [
                'name' => '竹田　直美',
                'email' => 'user4@test.com',
                'email_verified_at' => new DateTime(),
                'password' => Hash::make('testtest'),
                'role' => 'user',
            ],
            [
                'name' => '加藤　三郎',
                'email' => 'user5@test.com',
                'email_verified_at' => new DateTime(),
                'password' => Hash::make('testtest'),
                'role' => 'user',
            ],
            [
                'name' => '管理者',
                'email' => 'admin@test.com',
                'email_verified_at' => new DateTime(),
                'password' => Hash::make('testtest'),
                'role' => 'admin'
            ],
        ]);
    }
}