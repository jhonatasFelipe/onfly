# Onfly — Travel Orders API

API REST para gestão de pedidos de viagem corporativa, construída com Laravel 12 e Clean Architecture.

## Requisitos

- Docker e Docker Compose
- Make

## Setup

```bash
make setup
```

## Comandos úteis

```bash
make up          # Sobe os containers
make down        # Para os containers
make shell       # Acessa o container da aplicação
make artisan cmd="test"   # Executa os testes
make docs        # Exibe URL da documentação da API
```

## Documentação da API (Scramble)

A documentação interativa OpenAPI é gerada automaticamente pelo [Scramble](https://scramble.dedoc.co):

| Recurso | URL |
|---------|-----|
| UI (Swagger/Elements) | `http://localhost:8080/docs/api` |
| Spec OpenAPI (JSON) | `http://localhost:8080/docs/api.json` |

### Acesso

| Ambiente | Quem acessa |
|----------|-------------|
| `local` | Qualquer pessoa (sem autenticação) |
| Demais (`testing`, `staging`, `production`, …) | Apenas administradores autenticados |

Em ambientes restritos, há duas formas de acesso:

**1. Navegador (sessão web)**

1. Acesse `http://localhost:8080/admin/login`
2. Entre com um usuário `is_admin = true` (ex.: `admin@example.com` / `password` do seeder)
3. Acesse `http://localhost:8080/docs/api`

**2. Spec JSON via Bearer token (Sanctum)**

```bash
# Obter token de admin
curl -X POST http://localhost:8080/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Baixar a spec OpenAPI
curl http://localhost:8080/docs/api.json \
  -H "Authorization: Bearer {token}"
```

Na UI do Scramble, use o botão **Authorize** e informe o token obtido via login da API para testar endpoints protegidos.

### Autenticação na API

Os endpoints protegidos usam **Laravel Sanctum** (Bearer token):

```bash
# Obter token
curl -X POST http://localhost:8080/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Usar token
curl http://localhost:8080/api/v1/travel-orders \
  -H "Authorization: Bearer {token}"
```

## Endpoints principais

| Método | Rota | Auth |
|--------|------|------|
| POST | `/api/v1/auth/register` | Público |
| POST | `/api/v1/auth/login` | Público |
| POST | `/api/v1/auth/logout` | Sanctum |
| POST | `/api/v1/travel-orders` | Sanctum |
| GET | `/api/v1/travel-orders` | Sanctum |
| GET | `/api/v1/travel-orders/{id}` | Sanctum |
| PATCH | `/api/v1/travel-orders/{id}/status` | Sanctum |

Consulte `/docs/api` para schemas completos, filtros e códigos de resposta.

### Rate limiting

Todos os endpoints possuem limite de requisições configurável em `config/rate-limiting.php`:

| Grupo | Rotas | Padrão |
|-------|-------|--------|
| `auth` | login, register | 10/min por IP |
| `api` | travel-orders, logout | 60/min por usuário ou IP |
| `web-login` | `/admin/login` | 5/min por IP |
| `web` | rotas web gerais | 60/min por IP |
| `docs` | `/docs/api`, `/docs/api.json` | 30/min por usuário ou IP |

Variáveis de ambiente: `RATE_LIMIT_AUTH`, `RATE_LIMIT_API`, `RATE_LIMIT_WEB_LOGIN`, `RATE_LIMIT_WEB`, `RATE_LIMIT_DOCS`.

Respostas `429` retornam: `{"message": "Muitas tentativas. Tente novamente mais tarde."}`

Na documentação Scramble (`/docs/api`), cada endpoint exibe os limites aplicados na descrição da operação, na extensão OpenAPI `x-rateLimit` e na resposta `429`.

## Testes

```bash
make artisan cmd="test"
```

## Licença

MIT
