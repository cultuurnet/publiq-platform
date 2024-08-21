.PHONY: up down bash install composer-install npm-install key-generate migrate lint stan ci config

up:
	vendor/bin/sail up -d

down:
	vendor/bin/sail down

restart: down up

destroy:
	vendor/bin/sail down -v

bash:
	docker-compose exec laravel bash

config:
	./config.sh

install: composer-install key-generate migrate seed npm-install npm-build

composer-install:
	vendor/bin/sail composer install

key-generate:
	vendor/bin/sail artisan key:generate

migrate:
	vendor/bin/sail artisan migrate

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

e2e-install:
	docker-compose exec laravel npx playwright install chromium --with-deps

test-e2e:
	docker-compose exec laravel npx playwright test $(options)

test-e2e-filter:
	docker-compose exec laravel npx playwright test "$(filter)" $(options)
