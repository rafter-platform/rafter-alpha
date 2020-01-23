<?php

use App\Events\UserRegistered;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'name' => 'Josh Larson',
            'email' => 'jplhomer@gmail.com',
            'password' => Hash::make('password'),
        ]);

        event(new UserRegistered($user));
    }
}
