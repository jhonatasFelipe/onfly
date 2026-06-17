# Onfly — Travel Orders API

API REST para gestão de pedidos de viagem corporativa, construída com **Laravel 12** e **Clean Architecture**.

A documentação técnica detalhada (arquitetura, banco de dados, domínio, testes e contribuição) está em [`docs/README.md`](docs/README.md).

## Stack

| Tecnologia | Versão / Uso |
|------------|--------------|
| PHP | 8.4 (container Docker) |
| Laravel | 12 |
| MySQL | 8.0 |
| Laravel Sanctum | Autenticação API (Bearer token) |
| Scramble | Documentação OpenAPI interativa |
| Vite + Tailwind | Assets da interface web (login admin) |

## Pré-requisitos

- [Docker](https://docs.docker.com/get-docker/) e [Docker Compose](https://docs.docker.com/compose/)
- [Make](https://www.gnu.org/software/make/)

Portas padrão utilizadas:

| Porta | Serviço |
|-------|---------|
| `8080` | Aplicação (Nginx) |
| `3306` | MySQL |

> **WSL2 / Linux:** se houver problemas de permissão nos volumes, ajuste `UID` e `GID` no `.env` para corresponder ao seu usuário local (`id -u` e `id -g`).

## Setup do ambiente de desenvolvimento

### 1. Clonar e configurar variáveis de ambiente

```bash
git clone <url-do-repositorio>
cd onfly
cp .env.example .env
```

O arquivo `.env` é **obrigatório** antes de rodar o setup — o Makefile usa essas variáveis para configurar o banco e as portas.

### 2. Subir o ambiente completo

```bash
make setup
```

Esse comando executa, em sequência:

1. Build das imagens Docker
2. Sobe os containers (`app`, `nginx`, `mysql`)
3. Configura o `.env` para MySQL no Docker
4. Gera `APP_KEY` (se ainda não existir)
5. Executa as migrations
6. Instala dependências (`composer install`, `npm install`, `npm run build`)
7. Popula o banco com usuários de desenvolvimento (`db:seed`)
8. Ajusta permissões de `storage/` e `bootstrap/cache/`

### 3. Verificar se está funcionando

| Recurso | URL |
|---------|-----|
| Aplicação | http://localhost:8080 |
| Health check | http://localhost:8080/up |
| Documentação API | http://localhost:8080/docs/api |

## Serviços Docker

| Container | Imagem / Build | Função |
|-----------|----------------|--------|
| `onfly-app` | `docker/php/Dockerfile` (PHP 8.4-FPM) | Executa a aplicação Laravel; código montado em `/var/www` |
| `onfly-nginx` | `nginx:alpine` | Proxy reverso; expõe a porta `${APP_PORT:-8080}` |
| `onfly-mysql` | `mysql:8.0` | Banco de dados; volume persistente `mysql_data`; healthcheck antes do app subir |

## Credenciais de desenvolvimento

Criadas automaticamente pelo seeder (`make setup`):

| Usuário | E-mail | Senha | Admin |
|---------|--------|-------|-------|
| Admin User | `admin@example.com` | `password` | sim |
| Test User | `test@example.com` | `password` | não |

## Comandos Make

| Comando | Descrição |
|---------|-----------|
| `make setup` | Setup completo do ambiente (primeira vez) |
| `make up` | Sobe os containers |
| `make down` | Para os containers |
| `make restart` | Reinicia os containers |
| `make build` | Rebuild das imagens |
| `make build-fresh` | Rebuild sem cache |
| `make logs` | Acompanha logs de todos os serviços |
| `make ps` | Lista containers em execução |
| `make shell` | Abre bash no container `app` |
| `make artisan cmd="..."` | Executa comando Artisan (ex.: `cmd="test"`) |
| `make composer cmd="..."` | Executa Composer (ex.: `cmd="install"`) |
| `make install-deps` | Instala dependências PHP/Node e compila assets |
| `make seed` | Executa `db:seed` |
| `make migrate` | Executa migrations |
| `make fix-permissions` | Corrige permissões de `storage/` e `bootstrap/cache/` |
| `make docs` | Exibe URL da documentação da API |
| `make about` | Exibe informações do Laravel (`php artisan about`) |

## Variáveis de ambiente

### Docker Compose

| Variável | Padrão | Descrição |
|----------|--------|-----------|
| `APP_PORT` | `8080` | Porta exposta pelo Nginx |
| `MYSQL_PORT` | `3306` | Porta exposta pelo MySQL |
| `MYSQL_ROOT_PASSWORD` | `root` | Senha root do MySQL |
| `MYSQL_DATABASE` | `onfly` | Nome do banco |
| `MYSQL_USER` | `onfly` | Usuário do banco |
| `MYSQL_PASSWORD` | `onfly` | Senha do usuário |
| `UID` / `GID` | `1000` | UID/GID do usuário no container PHP |

### Laravel

| Variável | Padrão | Descrição |
|----------|--------|-----------|
| `APP_NAME` | `Onfly` | Nome da aplicação |
| `APP_ENV` | `local` | Ambiente (`local`, `testing`, `production`) |
| `APP_KEY` | — | Chave de criptografia (gerada pelo setup) |
| `APP_URL` | `http://localhost:8080` | URL base da aplicação |
| `DB_HOST` | `mysql` | Host do banco (nome do serviço Docker) |
| `QUEUE_CONNECTION` | `database` | Driver de filas |
| `CACHE_STORE` | `database` | Driver de cache |
| `SESSION_DRIVER` | `database` | Driver de sessão |

### Rate limiting (requisições por minuto)

| Variável | Padrão | Grupo |
|----------|--------|-------|
| `RATE_LIMIT_AUTH` | `10` | login, register |
| `RATE_LIMIT_API` | `60` | travel-orders, logout |
| `RATE_LIMIT_WEB_LOGIN` | `5` | `/admin/login` |
| `RATE_LIMIT_WEB` | `60` | rotas web gerais |
| `RATE_LIMIT_DOCS` | `30` | `/docs/api` |

## Documentação da API (Scramble)

A documentação interativa OpenAPI é gerada automaticamente pelo [Scramble](https://scramble.dedoc.co):

| Recurso | URL |
|---------|-----|
| UI (Stoplight Elements) | http://localhost:8080/docs/api |
| Spec OpenAPI (JSON) | http://localhost:8080/docs/api.json |

### Acesso à documentação

| Ambiente | Quem acessa |
|----------|-------------|
| `local` | Qualquer pessoa (sem autenticação) |
| Demais (`testing`, `staging`, `production`, …) | Apenas administradores autenticados |

Em ambientes restritos:

**1. Navegador (sessão web)**

1. Acesse http://localhost:8080/admin/login
2. Entre com um usuário admin (`admin@example.com` / `password`)
3. Acesse http://localhost:8080/docs/api

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

Na UI do Scramble, use o botão **Authorize** e informe o token para testar endpoints protegidos.

## Endpoints principais

| Método | Rota | Auth |
|--------|------|------|
| POST | `/api/v1/auth/register` | Público |
| POST | `/api/v1/auth/login` | Público |
| POST | `/api/v1/auth/logout` | Sanctum |
| POST | `/api/v1/travel-orders` | Sanctum |
| GET | `/api/v1/travel-orders` | Sanctum |
| GET | `/api/v1/travel-orders/{id}` | Sanctum |
| PATCH | `/api/v1/travel-orders/{id}/status` | Sanctum (admin para aprovar/cancelar) |

Consulte `/docs/api` para schemas completos, filtros e códigos de resposta. Para regras de negócio (status, permissões, eventos), veja [`docs/domain.md`](docs/domain.md).

### Autenticação na API

```bash
# Obter token
curl -X POST http://localhost:8080/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Usar token
curl http://localhost:8080/api/v1/travel-orders \
  -H "Authorization: Bearer {token}"
```

### Rate limiting

| Grupo | Rotas | Padrão |
|-------|-------|--------|
| `auth` | login, register | 10/min por IP |
| `api` | travel-orders, logout | 60/min por usuário ou IP |
| `web-login` | `/admin/login` | 5/min por IP |
| `web` | rotas web gerais | 60/min por IP |
| `docs` | `/docs/api`, `/docs/api.json` | 30/min por usuário ou IP |

Respostas `429` retornam: `{"message": "Muitas tentativas. Tente novamente mais tarde."}`

## Testes

```bash
make artisan cmd="test"
```

Ou dentro do container:

```bash
make shell
php artisan test
```

Para mais detalhes sobre suites, cobertura e como escrever novos testes, consulte [`docs/testing.md`](docs/testing.md).

## Troubleshooting

### Porta 8080 ou 3306 já em uso

Altere `APP_PORT` e/ou `MYSQL_PORT` no `.env` e reinicie:

```bash
make down && make up
```

### Erro de permissão em `storage/` ou `bootstrap/cache/`

```bash
make fix-permissions
```

### `APP_KEY` vazio ou inválido

```bash
make artisan cmd="key:generate"
```

### MySQL não sobe ou app não conecta

```bash
docker compose logs mysql
make ps   # verificar se mysql está healthy
```

### Assets Vite ausentes (página de login sem estilo)

```bash
make install-deps
```

### Notificações não são enviadas

O projeto usa `QUEUE_CONNECTION=database`. Notificações de aprovação/cancelamento são enfileiradas. Para processá-las em desenvolvimento:

```bash
make artisan cmd="queue:work"
```

Em testes, a fila roda em modo `sync` automaticamente.

### Reinstalar dependências do zero

```bash
make install-deps
```

### Repopular banco de desenvolvimento

```bash
make artisan cmd="migrate:fresh --seed"
```

## Documentação técnica

| Documento | Conteúdo |
|-----------|----------|
| [`docs/README.md`](docs/README.md) | Índice da documentação |
| [`docs/architecture.md`](docs/architecture.md) | Arquitetura, camadas e fluxos |
| [`docs/database.md`](docs/database.md) | Schema, migrations e modelos |
| [`docs/domain.md`](docs/domain.md) | Regras de negócio e estados |
| [`docs/testing.md`](docs/testing.md) | Guia de testes |
| [`docs/contributing.md`](docs/contributing.md) | Convenções e contribuição |

## Licença

MIT
