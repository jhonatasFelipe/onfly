<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            "id" => 1,
            "name" =>"user teste1",
            "email" => "userteste1@teste.com.br",
            "password" => Hash::make('password')
        ]);


        User::create([
            'id' => 2,
            "name" =>"user teste2",
            "email" => "userteste2@teste.com.br",
            "password" => Hash::make('password')
        ]);

        
    }
}
