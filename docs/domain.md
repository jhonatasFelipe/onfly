# Domínio e regras de negócio

Este documento descreve as regras de negócio, estados, autorização e eventos do bounded context **TravelOrder**.

> Para visualizar os diagramas abaixo no editor, use o preview do Markdown com a extensão [Markdown Preview Mermaid Support](https://marketplace.visualstudio.com/items?itemName=bierner.markdown-mermaid). Detalhes no [índice da documentação](README.md).

## Visão do modelo de domínio

O bounded context **TravelOrder** concentra quase toda a lógica de negócio em `app/Domain/`. O diagrama abaixo mostra como os componentes se organizam e se relacionam.

### Mapa de componentes

```mermaid
flowchart TB
    subgraph travelOrderBC [TravelOrder]
        Entity[TravelOrder Entity]
        VO["TravelPeriod e TravelOrderStatus"]
        Events[Domain Events]
        Repo[TravelOrderRepositoryInterface]
        Criteria[ListTravelOrdersCriteria]
        Collections[TravelOrderCollection + PaginatedTravelOrders]
        Exceptions["4 exceções de domínio"]
    end
    subgraph shared [Shared]
        Uuid[Uuid]
        Pagination[Pagination]
    end
    Entity --> VO
    Entity --> Events
    Criteria --> Pagination
    Criteria --> VO
    Collections --> Entity
    Repo --> Entity
    Entity --> Uuid
```

### Diagrama do aggregate

O aggregate root `TravelOrder` é o único ponto de entrada para mudanças de estado. Campos como `id`, `userId`, `requesterName` e `destination` são **atributos validados** dentro da entidade; `TravelPeriod` e `TravelOrderStatus` são value objects explícitos.

```mermaid
classDiagram
    class TravelOrder {
        -string id
        -int userId
        -string requesterName
        -string destination
        -TravelPeriod period
        -TravelOrderStatus status
        +create()
        +reconstitute()
        +approve()
        +cancel()
        +belongsTo()
        +pullDomainEvents()
    }
    class TravelPeriod {
        +DateTimeImmutable departure
        +DateTimeImmutable returnDate
        +fromStrings()
    }
    class TravelOrderStatus {
        <<enumeration>>
        Solicitado
        Aprovado
        Cancelado
        +canTransitionTo()
    }
    class TravelOrderApproved {
        +string orderId
        +int userId
    }
    class TravelOrderCancelled {
        +string orderId
        +int userId
    }
    class Uuid {
        +generate()
        +fromString()
    }
    TravelOrder *-- TravelPeriod
    TravelOrder *-- TravelOrderStatus
    TravelOrder ..> TravelOrderApproved : approve
    TravelOrder ..> TravelOrderCancelled : cancel
    TravelOrder ..> Uuid : create
```

## Aggregate: TravelOrder

O aggregate root `TravelOrder` encapsula todo o ciclo de vida de um pedido de viagem.

**Localização:** `app/Domain/TravelOrder/Entities/TravelOrder.php`

### Dados do pedido

| Campo | Tipo | Descrição |
|-------|------|-----------|
| ID | `string` (UUID) | Gerado na criação via `Uuid::generate()`; validado em `normalizeId()` |
| Solicitante | `int` | ID do usuário autenticado; validado em `normalizeUserId()` |
| Nome | `string` | Nome do solicitante; validado em `normalizeRequesterName()` |
| Destino | `string` | Destino da viagem; validado em `normalizeDestination()` |
| Período | `TravelPeriod` | Value object — datas de ida e volta |
| Status | `TravelOrderStatus` | Value object (enum) — estado atual do pedido |

### Criação

Todo pedido é criado com status `solicitado`:

```php
TravelOrder::create(
    userId: $userId,
    requesterName: $requesterName,
    destination: $destination,
    period: $period,
);
```

O ID é gerado automaticamente via `Uuid::generate()` dentro de `TravelOrder::create()`.

### Fluxo de criação

A criação de um pedido **não** dispara eventos de domínio — apenas `approve()` e `cancel()` registram eventos.

```mermaid
sequenceDiagram
    participant UC as CreateTravelOrderUseCase
    participant Entity as TravelOrder
    participant Period as TravelPeriod
    participant Repo as TravelOrderRepositoryInterface

    UC->>Period: fromStrings(departure, returnDate)
    UC->>Entity: create(userId, requesterName, destination, period)
    Entity-->>Entity: status = solicitado, id = Uuid::generate()
    UC->>Repo: save(order)
    Note over Entity: Nenhum domain event registrado
```

## Máquina de estados

```mermaid
stateDiagram-v2
    [*] --> solicitado: create
    solicitado --> aprovado: approve (admin)
    solicitado --> cancelado: cancel (admin)
    aprovado --> [*]
    cancelado --> [*]
```

| Status | Valor | Transições permitidas |
|--------|-------|----------------------|
| `solicitado` | Default na criação | → `aprovado`, → `cancelado` |
| `aprovado` | Estado final | Nenhuma |
| `cancelado` | Estado final | Nenhuma |

### Regras de transição

- Apenas pedidos em status `solicitado` podem ser aprovados ou cancelados
- Tentativa de transição inválida lança `InvalidTravelOrderStateException` → HTTP **409**
- Estados finais (`aprovado`, `cancelado`) não permitem novas transições

A lógica está no aggregate:

```php
public function approve(): void
{
    if (! $this->status->canTransitionTo(TravelOrderStatus::Aprovado)) {
        throw new InvalidTravelOrderStateException('Only requested orders can be approved.');
    }
    $this->status = TravelOrderStatus::Aprovado;
    $this->record(new TravelOrderApproved($this->id, $this->userId));
}
```

## Validações de domínio

### Período de viagem (`TravelPeriod`)

- A data de volta deve ser **igual ou posterior** à data de ida
- Violação lança `InvalidTravelPeriodException` → HTTP **422**

```php
if ($this->return < $this->departure) {
    throw new InvalidTravelPeriodException('Return date must be on or after departure date.');
}
```

### Value Objects e atributos validados

| Conceito | Onde vive | Validação |
|----------|-----------|-----------|
| `TravelPeriod` | `Domain/TravelOrder/ValueObjects/` | Volta ≥ ida no construtor |
| `TravelOrderStatus` | `Domain/TravelOrder/ValueObjects/` | Backed enum; `canTransitionTo()` define transições |
| `Uuid` | `Domain/Shared/ValueObjects/` | UUID válido (Ramsey) |
| `Pagination` | `Domain/Shared/ValueObjects/` | `page` e `perPage` positivos |
| Destino, nome, userId, id | Métodos `normalize*()` em `TravelOrder` | Não vazio, tamanho máximo, ID positivo, UUID válido |

Validações de **formato de input** (tipos, campos obrigatórios) ficam nos Form Requests da camada Http. Validações de **regra de negócio** ficam no Domain.

## Autorização

### Papéis

| Papel | Identificação | Permissões |
|-------|---------------|------------|
| Usuário comum | `is_admin = false` | Criar pedidos; listar e visualizar **apenas os próprios** |
| Administrador | `is_admin = true` | Listar todos; visualizar qualquer pedido; **aprovar** e **cancelar** |

### Onde a autorização é aplicada

| Ação | Mecanismo | Local |
|------|-----------|-------|
| Aprovar pedido | `TravelOrderPolicy::approve()` | Form Request + Policy |
| Cancelar pedido | `TravelOrderPolicy::cancel()` | Form Request + Policy |
| Visualizar pedido | Ownership no use case | `ShowTravelOrderUseCase` |
| Listar pedidos | Escopo no use case | `ListTravelOrdersUseCase` |

### Policy

```php
// app/Policies/TravelOrderPolicy.php
public function approve(UserModel $user, TravelOrderModel $order): bool
{
    return $user->is_admin;
}
```

Usuário comum que tenta aprovar/cancelar recebe HTTP **403**.

Usuário comum que tenta visualizar pedido de outro recebe `UnauthorizedTravelOrderAccessException` → HTTP **403**.

## Eventos de domínio

Eventos são registrados dentro do aggregate e despachados pelo use case após persistência.

| Evento | Disparado quando | Dados |
|--------|------------------|-------|
| `TravelOrderApproved` | `$order->approve()` | `orderId` (string UUID), `userId` (int) |
| `TravelOrderCancelled` | `$order->cancel()` | `orderId` (string UUID), `userId` (int) |

### Fluxo de side effects

```mermaid
sequenceDiagram
    participant UC as UpdateTravelOrderStatusUseCase
    participant Entity as TravelOrder
    participant Repo as Repository
    participant Events as EventDispatcherPort
    participant Listener as NotificationListener
    participant Notif as NotificationPort

    UC->>Entity: approve() / cancel()
    Entity-->>Entity: record domain event
    UC->>Repo: save(order)
    UC->>Events: dispatch(events)
    Events->>Listener: handle(event)
    Listener->>Notif: notify user
```

### Notificações resultantes

| Evento | Notificação | Canais |
|--------|-------------|--------|
| `TravelOrderApproved` | `TravelOrderApprovedNotification` | `mail`, `database` |
| `TravelOrderCancelled` | `TravelOrderCancelledNotification` | `mail`, `database` |

Notificações implementam `ShouldQueue` — são enfileiradas via `QUEUE_CONNECTION=database`. Em testes, a fila roda em modo `sync`.

## Filtros de listagem

A listagem de pedidos aceita filtros via `ListTravelOrdersCriteria`:

| Filtro | Tipo | Descrição |
|--------|------|-----------|
| `pagination` | `Pagination` | Página e itens por página (`page`, `per_page` na API) |
| `userId` | `int` | Filtrar por solicitante (aplicado automaticamente para não-admin) |
| `status` | `TravelOrderStatus` | Filtrar por status |
| `destination` | `string` | Busca parcial por destino |
| `createdFrom` / `createdTo` | `string` (data) | Intervalo de criação |
| `departureFrom` / `departureTo` | `string` (data) | Intervalo de data de partida |

**Comportamento por papel:**
- **Admin:** vê todos os pedidos; pode filtrar por qualquer `userId`
- **Usuário comum:** `userId` é forçado para o ID do usuário autenticado

## Exceções de domínio

O diagrama abaixo liga cada ação de domínio à exceção correspondente e ao status HTTP retornado pela API.

```mermaid
flowchart LR
    subgraph aggregate [TravelOrder]
        create[create / reconstitute]
        approve[approve]
        cancel[cancel]
        period[TravelPeriod]
    end
    subgraph useCases [Use Cases]
        show[ShowTravelOrderUseCase]
        list[ListTravelOrdersUseCase]
    end
    create -->|atributo inválido| invalidArg[InvalidArgumentException]
    period -->|volta antes da ida| invalidPeriod[InvalidTravelPeriodException]
    approve -->|status final| invalidState[InvalidTravelOrderStateException]
    cancel -->|status final| invalidState
    show -->|pedido alheio| unauthorized[UnauthorizedTravelOrderAccessException]
    show -->|não encontrado| notFound[TravelOrderNotFoundException]
    invalidArg --> http422[HTTP 422]
    invalidPeriod --> http422
    invalidState --> http409[HTTP 409]
    unauthorized --> http403[HTTP 403]
    notFound --> http404[HTTP 404]
```

| Exceção | Quando | HTTP |
|---------|--------|------|
| `TravelOrderNotFoundException` | Pedido não existe | 404 |
| `UnauthorizedTravelOrderAccessException` | Acesso a pedido alheio ou ação não permitida | 403 |
| `InvalidTravelOrderStateException` | Transição de status inválida | 409 |
| `InvalidTravelPeriodException` | Data de volta anterior à ida | 422 |
| `InvalidArgumentException` | Atributo inválido na normalização (`userId`, nome, destino, id) | 422 |

## Casos de uso e responsabilidades

| Use Case | Regra de negócio no Domain | Orquestração |
|----------|---------------------------|--------------|
| `CreateTravelOrderUseCase` | `TravelOrder::create()` | Persiste novo pedido |
| `ListTravelOrdersUseCase` | Criteria + escopo por papel | Consulta via port |
| `ShowTravelOrderUseCase` | `belongsTo()` | Checa ownership |
| `UpdateTravelOrderStatusUseCase` | `approve()` / `cancel()` | Persiste + dispatch eventos |

Nenhuma regra de negócio deve existir nos use cases — apenas orquestração.

## Linguagem ubíqua

| Termo técnico evitado | Termo de domínio |
|-----------------------|------------------|
| Order | TravelOrder (pedido de viagem) |
| Pending | Solicitado |
| Approved | Aprovado |
| Rejected | Cancelado |
| Customer | Solicitante (requester) |

Métodos expressam intenção de negócio: `approve()`, `cancel()`, `belongsTo()` — não `updateStatus($id, 'aprovado')`.
