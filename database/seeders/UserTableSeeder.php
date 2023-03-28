<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::truncate();
        //creates 3 dummy users
        User::factory(3)
            ->state([
                'password' => \Hash::make('userpassword')
            ])
            ->create();

        //Create 1 admin user
        User::factory(1)
            ->state([
                'is_admin' => true,
                'email' => 'admin@buckhill.co.uk',
                'password' => \Hash::make('admin')
            ])
            ->create();
    }
}
