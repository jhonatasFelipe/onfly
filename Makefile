.PHONY: up down build build-fresh restart logs shell artisan composer \
        install-laravel configure-env migrate fix-permissions wait-app wait-mysql post-up setup ps about docs \
        install-deps seed key-generate phpstan pint validate-static

up: post-up

post-up:
	docker compose up -d
	@$(MAKE) wait-app
	@if [ -f artisan ]; then $(MAKE) fix-permissions; fi

down:
	docker compose down

build:
	docker compose build

build-fresh:
	docker compose build --no-cache

restart:
	docker compose restart
	@$(MAKE) wait-app
	@if [ -f artisan ]; then $(MAKE) fix-permissions; fi

logs:
	docker compose logs -f

ps:
	docker compose ps

shell:
	docker compose exec app bash

artisan:
	docker compose exec app php artisan $(cmd)

composer:
	docker compose exec app composer $(cmd)

about:
	docker compose exec app php artisan about

docs:
	@echo "API docs: http://localhost:8080/docs/api"

phpstan:
	docker compose exec app vendor/bin/phpstan analyse

pint:
	docker compose exec app vendor/bin/pint --test

validate-static: pint phpstan

wait-app:
	@echo "Aguardando container app..."
	@until docker compose exec -T app php -v >/dev/null 2>&1; do sleep 2; done
	@echo "Container app pronto."

wait-mysql:
	@echo "Aguardando MySQL..."
	@until docker compose exec -T mysql mysqladmin ping -h localhost -u root -proot --silent 2>/dev/null; do sleep 2; done
	@echo "MySQL pronto."

fix-permissions:
	@if [ -f artisan ]; then \
		docker compose exec -T app bash -c 'mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache'; \
		docker compose exec -T app chmod -R 775 storage bootstrap/cache; \
		docker compose exec -T app chown -R www-data:www-data storage bootstrap/cache; \
		echo "Permissões de storage e bootstrap/cache ajustadas."; \
	fi

configure-env:
	@if [ -f .env ]; then \
		docker compose exec -T app bash -c '\
			sed -i "s|^APP_NAME=.*|APP_NAME=Onfly|" .env; \
			sed -i "s|^APP_URL=.*|APP_URL=http://localhost:8080|" .env; \
			sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=mysql|" .env; \
			set_db_var() { \
				local key="$$1" val="$$2"; \
				if grep -qE "^#?[[:space:]]*$${key}=" .env; then \
					sed -i "s|^#*[[:space:]]*$${key}=.*|$${key}=$${val}|" .env; \
				else \
					echo "$${key}=$${val}" >> .env; \
				fi; \
			}; \
			set_db_var DB_HOST mysql; \
			set_db_var DB_PORT 3306; \
			set_db_var DB_DATABASE onfly; \
			set_db_var DB_USERNAME onfly; \
			set_db_var DB_PASSWORD onfly'; \
		echo ".env do Laravel configurado para MySQL."; \
	fi

migrate:
	@$(MAKE) wait-mysql
	docker compose exec -T app php artisan migrate --force

key-generate:
	@docker compose exec -T app bash -c '\
		if ! grep -qE "^APP_KEY=base64:" .env 2>/dev/null; then \
			php artisan key:generate --force; \
			echo "APP_KEY gerada."; \
		else \
			echo "APP_KEY já configurada."; \
		fi'

install-deps:
	docker compose exec -T app composer install --no-interaction
	docker compose exec -T app npm install
	docker compose exec -T app npm run build

seed:
	@$(MAKE) wait-mysql
	docker compose exec -T app php artisan db:seed --force

install-laravel:
	@if [ -f artisan ]; then \
		echo "Laravel já está instalado."; \
		$(MAKE) configure-env; \
		$(MAKE) key-generate; \
		$(MAKE) migrate; \
		$(MAKE) install-deps; \
		$(MAKE) seed; \
		$(MAKE) fix-permissions; \
	else \
		docker compose exec -T app composer create-project laravel/laravel:^12.0 /tmp/laravel --prefer-dist --no-interaction; \
		docker compose exec -T app bash -c 'shopt -s dotglob nullglob && for f in /tmp/laravel/* /tmp/laravel/.[!.]*; do name=$$(basename "$$f"); [ -e "/var/www/$$name" ] || cp -r "$$f" /var/www/; done && rm -rf /tmp/laravel'; \
		$(MAKE) configure-env; \
		$(MAKE) key-generate; \
		$(MAKE) migrate; \
		$(MAKE) install-deps; \
		$(MAKE) seed; \
		$(MAKE) fix-permissions; \
	fi

setup: build post-up install-laravel
	@echo ""
	@echo "Ambiente pronto. Acesse http://localhost:8080"
