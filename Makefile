.PHONY: up down bash install composer-install npm-install key-generate migrate lint stan ci config

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
	docker compose exec platform composer install

key-generate:
	docker compose exec platform artisan key:generate

migrate:
	docker compose exec platform artisan migrate

seed:
	vendor/bin/sail artisan db:seed

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
	vendor/bin/sail npm install

npm-dev:
	vendor/bin/sail npm run dev

npm-build:
	vendor/bin/sail npm run build

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
	docker-compose exec laravel npx playwright install chromium --with-deps

test-e2e:
	docker-compose exec laravel npx playwright test $(options)

test-e2e-filter:
	docker-compose exec laravel npx playwright test "$(filter)" $(options)
