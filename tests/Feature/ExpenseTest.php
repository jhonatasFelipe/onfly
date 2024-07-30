<?php
// <vsK>^,?*e8A;Eu
namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\ExpensesSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{

    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_creation_expense(): void
    {
        $this->seed(UserSeeder::class);
        $user = User::find(1);

        $response = $this->withHeader('Accept', 'Application/json')
        ->actingAs($user)
        ->postJson('api/expenses',[
            'description' => 'description teste',
                'value' => 100,
                'date' =>' 2024/07/28',
                'user_id' => 1
        ]);

        $response->assertStatus(201);

        $response->assertJson([
            "description"=> "description teste",
            "value"=> 100,
            "date" => "28/07/2024",
            "user" => [
                "name" => "user teste1",
                "email" => "userteste1@teste.com.br",
                "email_verified_at" => null
            ]
            ]);
    }



    public function test_to_block_when_not_is_owner_of_the_expense(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(ExpensesSeeder::class);
        $user = User::find(2);

        $responsePut = $this->withHeader('Accept', 'Application/json')
        ->actingAs($user)
        ->putJson('api/expenses/1',[
            'description' => 'description teste',
                'value' => 200,
                'date' =>' 2024/07/28',
                'user_id' => 1
        ]);

        $responsePut->assertStatus(403);

        $responseDelete = $this->withHeader('Accept', 'Application/json')
        ->actingAs($user)
        ->deleteJson('api/expenses/1');

        $responseDelete->assertStatus(403);

        $responseGet = $this->withHeader('Accept', 'Application/json')
        ->actingAs($user)
        ->get('api/expenses/1');

        $responseGet->assertStatus(403);
    }


    public function test_update_expense(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(ExpensesSeeder::class);
        $user = User::find(1);

        $response = $this->withHeader('Accept', 'Application/json')
        ->actingAs($user)
        ->putJson('api/expenses/1',[
            'description' => 'description teste',
                'value' => 200,
                'date' =>' 2024/07/28',
                'user_id' => 1
        ]);

        $response->assertStatus(200);
    }

    public function test_delete_expense(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(ExpensesSeeder::class);
        $user = User::find(1);

        $response = $this->withHeader('Accept', 'Application/json')
        ->actingAs($user)
        ->deleteJson('api/expenses/1');

        $response->assertStatus(200);
    }


    public function test_get_expense(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(ExpensesSeeder::class);
        $user = User::find(1);

        $response = $this->withHeader('Accept', 'Application/json')
        ->actingAs($user)
        ->get('api/expenses/1');

        $response->assertStatus(200);

        $response->assertJson([
            "description"=> "despesa teste",
            "value"=> '200',
            "date" => "28/07/2024",
            "user" => [
                "name" => "user teste1",
                "email" => "userteste1@teste.com.br",
                "email_verified_at" => null
            ]
            ]);
    }
}
