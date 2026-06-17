<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class ApiDocumentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_api_documentation_ui(): void
    {
        $admin = UserModel::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/docs/api')
            ->assertOk();
    }

    public function test_admin_can_access_openapi_json_spec(): void
    {
        $admin = UserModel::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->getJson('/docs/api.json');

        $response->assertOk()
            ->assertJsonPath('info.title', 'Onfly Travel Orders API')
            ->assertJsonStructure([
                'paths' => [
                    '/travel-orders',
                    '/travel-orders/{id}',
                    '/travel-orders/{id}/status',
                    '/auth/register',
                    '/auth/login',
                    '/auth/logout',
                ],
            ]);
    }

    public function test_admin_can_access_openapi_spec_with_sanctum_bearer_token(): void
    {
        $admin = UserModel::factory()->admin()->create([
            'password' => Hash::make('password'),
        ]);

        $token = $admin->createToken('docs')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/docs/api.json')
            ->assertOk();
    }

    public function test_non_admin_bearer_token_cannot_access_openapi_spec(): void
    {
        $user = UserModel::factory()->create();
        $token = $user->createToken('docs')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/docs/api.json')
            ->assertForbidden();
    }

    public function test_openapi_spec_declares_bearer_security(): void
    {
        $admin = UserModel::factory()->admin()->create();

        $response = $this->actingAs($admin)->getJson('/docs/api.json');

        $response->assertJsonPath('components.securitySchemes.http.type', 'http')
            ->assertJsonPath('components.securitySchemes.http.scheme', 'bearer');

        $this->assertNotEmpty($response->json('security'));
        $this->assertSame([], $response->json('paths./auth/login.post.security'));
        $this->assertSame([], $response->json('paths./auth/register.post.security'));
    }

    public function test_openapi_spec_contains_named_operations_with_descriptions(): void
    {
        $admin = UserModel::factory()->admin()->create();

        $response = $this->actingAs($admin)->getJson('/docs/api.json');
        $paths = $response->json('paths');

        $this->assertSame('auth.registerUser', $paths['/auth/register']['post']['operationId']);
        $this->assertSame('Registrar usuário', $paths['/auth/register']['post']['summary']);
        $this->assertStringContainsString('token Sanctum', $paths['/auth/register']['post']['description']);

        $this->assertSame('auth.loginUser', $paths['/auth/login']['post']['operationId']);
        $this->assertSame('Autenticar usuário', $paths['/auth/login']['post']['summary']);

        $this->assertSame('auth.logoutUser', $paths['/auth/logout']['post']['operationId']);
        $this->assertSame('Encerrar sessão', $paths['/auth/logout']['post']['summary']);

        $this->assertSame('travelOrders.create', $paths['/travel-orders']['post']['operationId']);
        $this->assertSame('Criar pedido de viagem', $paths['/travel-orders']['post']['summary']);

        $this->assertSame('travelOrders.list', $paths['/travel-orders']['get']['operationId']);
        $this->assertSame('Listar pedidos de viagem', $paths['/travel-orders']['get']['summary']);

        $this->assertSame('travelOrders.show', $paths['/travel-orders/{id}']['get']['operationId']);
        $this->assertSame('Consultar pedido de viagem', $paths['/travel-orders/{id}']['get']['summary']);

        $this->assertSame('travelOrders.updateStatus', $paths['/travel-orders/{id}/status']['patch']['operationId']);
        $this->assertSame('Atualizar status do pedido', $paths['/travel-orders/{id}/status']['patch']['summary']);
    }

    public function test_openapi_spec_documents_list_query_parameters(): void
    {
        $admin = UserModel::factory()->admin()->create();

        $response = $this->actingAs($admin)->getJson('/docs/api.json');
        $parameters = collect($response->json('paths./travel-orders.get.parameters'))
            ->pluck('name')
            ->all();

        $this->assertContains('status', $parameters);
        $this->assertContains('destination', $parameters);
        $this->assertContains('created_from', $parameters);
        $this->assertContains('departure_to', $parameters);
    }

    public function test_openapi_spec_documents_rate_limits_per_endpoint(): void
    {
        $admin = UserModel::factory()->admin()->create();

        $response = $this->actingAs($admin)->getJson('/docs/api.json');
        $paths = $response->json('paths');

        $login = $paths['/auth/login']['post'];
        $this->assertStringContainsString('**Rate limit**', $login['description']);
        $this->assertStringContainsString('`auth`: 10 requisições por minuto', $login['description']);
        $this->assertStringContainsString('`api`: 60 requisições por minuto', $login['description']);
        $this->assertEqualsCanonicalizing([
            [
                'name' => 'auth',
                'limit' => 10,
                'period' => '1m',
                'by' => 'IP do cliente',
            ],
            [
                'name' => 'api',
                'limit' => 60,
                'period' => '1m',
                'by' => 'usuário autenticado ou IP',
            ],
        ], $login['x-rateLimit']);

        $listOrders = $paths['/travel-orders']['get'];
        $this->assertStringContainsString('`api`: 60 requisições por minuto', $listOrders['description']);
        $this->assertSame([
            [
                'name' => 'api',
                'limit' => 60,
                'period' => '1m',
                'by' => 'usuário autenticado ou IP',
            ],
        ], $listOrders['x-rateLimit']);

        $this->assertArrayHasKey('429', $listOrders['responses']);
        $this->assertStringContainsString('Limite de requisições excedido', $listOrders['responses']['429']['description']);
        $this->assertStringContainsString('`api` (60/minuto)', $listOrders['responses']['429']['description']);
    }

    public function test_guest_cannot_access_api_documentation(): void
    {
        $this->get('/docs/api')->assertForbidden();
        $this->getJson('/docs/api.json')->assertForbidden();
    }

    public function test_non_admin_user_cannot_access_api_documentation(): void
    {
        $user = UserModel::factory()->create();

        $this->actingAs($user)
            ->get('/docs/api')
            ->assertForbidden();

        $this->actingAs($user)
            ->getJson('/docs/api.json')
            ->assertForbidden();
    }

    public function test_admin_can_login_via_web_and_access_documentation(): void
    {
        UserModel::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ])->assertRedirect('/docs/api');

        $this->get('/docs/api')->assertOk();
    }

    public function test_non_admin_cannot_login_via_web_for_documentation(): void
    {
        UserModel::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->from('/admin/login')
            ->post('/admin/login', [
                'email' => 'user@example.com',
                'password' => 'password',
            ])
            ->assertRedirect('/admin/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
