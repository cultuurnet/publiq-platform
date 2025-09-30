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

install: composer-install key-generate migrate seed npm-install npm-build

composer-install:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) platform composer install

key-generate:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) platform php artisan key:generate

migrate:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) platform php artisan migrate

seed:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) platform php artisan db:seed

horizon:
	vendor/bin/sail artisan horizon

lint:
	vendor/bin/sail composer lint

stan:
	vendor/bin/sail composer stan

test:
	vendor/bin/sail composer test

test-filter:
	vendor/bin/sail composer test -- --filter=$(filter)

test-insightly:
	vendor/bin/sail composer test tests/Insightly/HttpInsightlyClientTest.php

ci: lint stan test

npm-install:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) platform npm install

npm-dev:
	vendor/bin/sail npm run dev

npm-build:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) platform npm run build

npm-format:
	vendor/bin/sail npm run format

npm-format-check:
	vendor/bin/sail npm run format:check

npm-lint:
	vendor/bin/sail npm run lint

npm-lint-check:
	vendor/bin/sail npm run lint:check

npm-types-check:
	vendor/bin/sail npm run types:check

npm-ci: npm-format npm-lint-check npm-types-check

e2e-install:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) platform npx playwright install chromium --with-deps

test-e2e:
	docker-compose exec $(DOCKER_COMPOSE_OPTIONS) platform npx playwright test $(options)

test-e2e-filter:
	docker-compose exec $(DOCKER_COMPOSE_OPTIONS) platform npx playwright test "$(filter)" $(options)
