<?php

namespace Database\Seeders;

use App\Models\Expenses;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpensesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Expenses::create([
            'id'=> 1,
            "description" => "despesa teste",
            "value"=> 200,
            "date" => "2024/07/28",
            "user_id" => 1 
        ]);
    }
}
