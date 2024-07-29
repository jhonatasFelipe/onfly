<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_creation_expense(): void
    {
        $response = $this->postJson('/api/expense',[

            
        ]);

        $response->assertStatus(201);
    }
}
