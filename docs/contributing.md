# Guia de contribuição

Este documento define convenções de código, onde colocar novas funcionalidades e o checklist para abrir um pull request.

## Onde colocar código novo

Use este guia de decisão:

| Pergunta | Camada | Pasta |
|----------|--------|-------|
| É regra de negócio ou invariante? | **Domain** | `app/Domain/` |
| Orquestra passos entre objetos? | **Application** | `app/Application/UseCases/` |
| Fala com banco, cache, mail ou API externa? | **Infrastructure** | `app/Infrastructure/` |
| Parseia HTTP ou formata JSON? | **Presentation** | `app/Http/` |
| É reação a algo que já aconteceu? | **Application Listener** | `app/Application/*/Listeners/` |
| Interface de repositório? | **Domain** | `app/Domain/*/Repositories/` |
| Interface de infra (mail, payment)? | **Application Port** | `app/Application/Ports/` |
| Implementação de interface? | **Infrastructure** | `app/Infrastructure/` |

### Pastas que NÃO devem ser usadas

| Pasta | Usar em vez disso |
|-------|-------------------|
| `app/Models/` | `app/Infrastructure/Persistence/Eloquent/` |
| `app/Services/` | `app/Application/UseCases/` |

## Convenções de código

### Geral

- `declare(strict_types=1);` em todo arquivo PHP
- Classes `final` por padrão (a menos que extensão seja intencional)
- Constructor injection — nunca `app()` ou `new` de Infrastructure dentro de Use Cases
- Type hints em parâmetros, retornos e propriedades
- Value Objects imutáveis (`readonly`)

### Domain

```php
// ✅ Correto — regra no aggregate
public function approve(): void
{
    if (! $this->status->isSolicitado()) {
        throw new InvalidTravelOrderStateException('...');
    }
    $this->status = TravelOrderStatus::Aprovado;
}

// ❌ Errado — Eloquent no Domain
use App\Infrastructure\Persistence\Eloquent\TravelOrderModel;
```

### Application (Use Cases)

```php
// ✅ Correto — depende de interface
final class CreateTravelOrderUseCase
{
    public function __construct(
        private readonly TravelOrderRepositoryInterface $orders,
        private readonly AuthenticatedUserPort $user,
    ) {}
}

// ❌ Errado — depende de implementação concreta
public function __construct(
    private readonly EloquentTravelOrderRepository $orders,
) {}
```

### Infrastructure

- Eloquent models são modelos de persistência — use Mappers/Translators para Domain
- Repositórios implementam interfaces do Domain
- Queries com `with()` para evitar N+1

### Presentation (Http)

```php
// ✅ Controller fino
final class StoreTravelOrderController extends Controller
{
    public function __invoke(
        StoreTravelOrderRequest $request,
        CreateTravelOrderUseCase $useCase,
    ): JsonResponse {
        $output = $useCase->execute(new CreateTravelOrderInput(/* ... */));
        return response()->json(new TravelOrderResource($output->order), 201);
    }
}

// ❌ Lógica de negócio no controller
public function __invoke(Request $request) {
    if ($request->status === 'aprovado') { /* ... */ }
}
```

- Validação em **Form Requests** — nunca `$request->validate()` no controller
- Respostas API via **API Resources** — nunca modelo Eloquent cru
- Autorização via **Policies** — `$this->authorize()` ou `authorize()` no Form Request

## Como adicionar um novo use case

Exemplo: adicionar funcionalidade de "reabrir pedido cancelado".

### 1. Domain — regra de negócio

```php
// app/Domain/TravelOrder/Entities/TravelOrder.php
public function reopen(): void
{
    if (! $this->status === TravelOrderStatus::Cancelado) {
        throw new InvalidTravelOrderStateException('...');
    }
    $this->status = TravelOrderStatus::Solicitado;
}
```

### 2. Application — use case

```php
// app/Application/TravelOrder/UseCases/ReopenTravelOrderUseCase.php
final class ReopenTravelOrderUseCase
{
    public function __construct(
        private readonly TravelOrderRepositoryInterface $orders,
    ) {}

    public function execute(ReopenTravelOrderInput $input): ReopenTravelOrderOutput
    {
        $order = $this->orders->findById(/* ... */)
            ?? throw new TravelOrderNotFoundException('...');

        $order->reopen();
        $this->orders->save($order);

        return new ReopenTravelOrderOutput($order);
    }
}
```

### 3. Application — DTOs

```php
// app/Application/TravelOrder/DTOs/ReopenTravelOrderInput.php
// app/Application/TravelOrder/DTOs/ReopenTravelOrderOutput.php
```

### 4. Presentation — controller, request, resource

```php
// app/Http/Controllers/Api/V1/TravelOrder/ReopenTravelOrderController.php
// app/Http/Requests/TravelOrder/ReopenTravelOrderRequest.php
```

### 5. Rota

```php
// routes/api.php
Route::patch('travel-orders/{id}/reopen', ReopenTravelOrderController::class)
    ->whereUuid('id');
```

### 6. Testes

- Unit: `ReopenTravelOrderUseCaseTest` (mocks)
- Unit: `TravelOrderTest::test_reopen_from_cancelled` (domínio)
- Feature: endpoint HTTP com auth e autorização

### 7. Binding (se houver nova interface)

```php
// app/Providers/RepositoryServiceProvider.php
$this->app->bind(NovaInterface::class, NovaImplementacao::class);
```

## Checklist antes de abrir PR

### Arquitetura

- [ ] Domain não importa `Illuminate\*`, Eloquent ou HTTP
- [ ] Use Case depende de interfaces, não de classes concretas de Infrastructure
- [ ] Controller não contém lógica de negócio
- [ ] Eloquent model não é usado diretamente como entidade de domínio em Use Cases
- [ ] Interface de repositório está no Domain; implementação na Infrastructure
- [ ] Side effects pós-persist via eventos de domínio ou ports dedicados
- [ ] Novo binding registrado em Service Provider (se aplicável)

### Laravel

- [ ] Validação em Form Request
- [ ] Resposta API via Resource
- [ ] Eager loading quando há relacionamentos
- [ ] Rotas nomeadas
- [ ] `config()` em vez de `env()` no código de aplicação
- [ ] Migration nova (não editar migration já commitada)

### Código

- [ ] `declare(strict_types=1)` presente
- [ ] Classes `final` onde apropriado
- [ ] Métodos pequenos, early return
- [ ] Sem código morto, `dd()` ou `dump()`
- [ ] Exceções de domínio expressivas

### Testes

- [ ] Comportamento novo coberto por testes
- [ ] Factories em vez de dados hardcoded
- [ ] Feature test para endpoints HTTP novos
- [ ] Casos de erro e autorização negada cobertos
- [ ] `php artisan test --coverage --min=100` passa localmente (ou aguardar CI no PR para `main`)

## Padrão de commits

Siga o estilo do histórico do repositório. Mensagens em inglês, focadas no **porquê**:

```
Add travel order status transition validation

Fix unauthorized access when listing orders as regular user

Refactor authentication adapters to use ports
```

Evite commits genéricos como "fix", "update", "changes".

## Migrations

- **Nunca** edite migrations já commitadas — crie uma nova
- Sempre implemente `down()` corretamente
- Use `foreignIdFor()` para foreign keys
- Adicione índices em colunas usadas em `WHERE`, `ORDER BY` e `JOIN`

```php
public function up(): void
{
    Schema::table('travel_orders', function (Blueprint $table): void {
        $table->string('notes')->nullable()->after('status');
    });
}

public function down(): void
{
    Schema::table('travel_orders', function (Blueprint $table): void {
        $table->dropColumn('notes');
    });
}
```

## Regras do projeto

O projeto possui regras detalhadas em `.cursor/rules/` que complementam este guia:

| Regra | Foco |
|-------|------|
| `clean-architecture.mdc` | Camadas e dependências |
| `laravel.mdc` | Convenções Laravel |
| `ddd.mdc` | Modelagem de domínio |
| `oop-solid.mdc` | Design de classes |
| `clean-code.mdc` | Legibilidade e simplicidade |
| `testing.mdc` | Pirâmide de testes |

Consulte essas regras para decisões arquiteturais mais detalhadas.

## Fluxo de trabalho sugerido

1. Crie uma branch a partir da main
2. Implemente seguindo as camadas (Domain → Application → Infrastructure → Http)
3. Escreva testes para cada camada
4. Execute `make artisan cmd="test --coverage --min=100"` antes de abrir o PR (o CI exige 100% de cobertura)
5. Abra o PR para `main` com descrição clara do que mudou e por quê — o workflow de testes roda automaticamente
