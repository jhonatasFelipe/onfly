echo "montando os containers"
docker-compose up -d --build 
sleep 3
echo "executando migrations do projeto"
docker exec TesteOnfly php artisan migrate 
echo "deploy finalizado acesse a aplicação em http://localhost:8000"