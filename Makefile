.PHONY: up down bash install composer-install npm-install key-generate migrate lint stan

up:
	vendor/bin/sail up -d

down:
	vendor/bin/sail down

bash:
	docker-compose exec laravel bash

install: composer-install key-generate migrate npm-install npm-build

composer-install:
	vendor/bin/sail composer install

key-generate:
	vendor/bin/sail artisan key:generate

migrate:
	vendor/bin/sail artisan migrate

lint:
	vendor/bin/sail composer lint

stan:
	vendor/bin/sail composer stan

test:
	vendor/bin/sail composer test

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
