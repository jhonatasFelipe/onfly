## Teste para a empresa Onfly

## Como subir o ambiente

O ambiente está dockerizado, então para que você possa rodar a aplicação, basta rodar o comando: 

`docker-composer up` 

no terminal dentro do diretório raiz, depois de ter feito o clone do mesmo.

## como acessar a aplicação

o Software Adminer  para gerenciamento do banco subirá no seguinte endereço:

`http://localhost:8080/`

A aplicação no endereço:

`http://localhost:8000/`

Obs: a aplicação não possui interface gráfica, então só poderá ser acessada com o Postman ou outro software similar. 


## Rotas disponíveis 

### User
POST => [api/user](http://localhost:8000/api/user) cria um novo usuário.

```
{
    "name": "Jhonatas Felipe",
    "email": "jhonatas1021@gmail.com",
    "password": "Klapaucius1*",
    "password_confirmation": "Klapaucius1*"
}

```


GET => [api/user] http://localhost:8000/api/user  obtém o usuário logado.

### Login

POST => [api/login] http://localhost:8000/api/login obtém um token de acesso para fazer as requisições na aplicação.

```
{
    "email": "jhonatas1021@gmail.com",
    "password": "Klapaucius1*"
}

```


### Expenses

GET => [api/expenses] http://localhost:8000/api/expenses lista todas as despesas do usuário logado.


POST => [api/expenses] http://localhost:8000/api/expenses cria uma nova despesa.

```
{
    "description":"Supermercado",
    "value": 895.57,
    "date": "2024/07/28",
    "user_id": 2 
}

```


PUT => [api/expenses] http://localhost:8000/api/expenses altera uma nova despesa 

```
{
    "description":"conta de luz ",
    "value": 895.57,
    "date": "2024/07/28",
    "user_id": 2
}
```


### Testes

Para rodar os teste da aplicações é só utilizar o seguinte comando: 

`php artisan test`

Obs: para que os testes funcionem é nesessário criar o banco "Teste" na base de dados e rodar o comando:

`php artisan migrate --env=testing`


