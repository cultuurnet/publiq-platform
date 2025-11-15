.PHONY: up down bash install composer-install npm-install key-generate migrate lint stan ci config

ifeq ($(CI),true)
DOCKER_COMPOSE_OPTIONS = -u 451:451 -T
else
DOCKER_COMPOSE_OPTIONS = -T
endif

up:
	docker compose up -d

down:
	docker compose down

restart: down up

destroy:
	docker compose down -v

bash:
	docker compose exec platform bash

bash-xdebug:
	docker compose exec platform-xdebug bash

config:
	sh ./docker/config.sh

install: composer-install key-generate migrate seed npm-install npm-build optimize

composer-install:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) platform composer install

key-generate:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) platform php artisan key:generate

migrate:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) platform php artisan migrate

seed:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) platform php artisan db:seed

horizon:
	docker compose exec platform artisan horizon

optimize:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) platform php artisan optimize

lint:
	docker compose exec platform composer lint

stan:
	docker compose exec platform composer stan

test:
	docker compose exec platform composer test

test-filter:
	docker compose exec platform composer test -- --filter=$(filter)

test-insightly:
	docker compose exec platform composer test tests/Insightly/HttpInsightlyClientTest.php

ci: lint stan test

npm-install:
	docker compose exec platform npm config set cache /var/www/html/.npm --global
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) platform npm install

npm-dev:
	docker compose exec platform npm run dev

npm-build:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) platform npm run build

npm-format:
	docker compose exec platform npm run format

npm-format-check:
	docker compose exec platform npm run format:check

npm-lint:
	docker compose exec platform npm run lint

npm-lint-check:
	docker compose exec platform npm run lint:check

npm-types-check:
	docker compose exec platform npm run types:check

npm-ci: npm-format npm-lint-check npm-types-check

e2e-install:
	npx playwright install chromium --with-deps

test-e2e:
	npx playwright test $(options)

test-e2e-filter:
	npx playwright test "$(filter)" $(options)
