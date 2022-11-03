.PHONY: up down composer-install npm-install key-generate migrate lint stan test watch build

up:
	vendor/bin/sail up -d

down:
	vendor/bin/sail down

composer-install:
	vendor/bin/sail composer install

npm-install:
	vendor/bin/sail npm install

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

watch:
	vendor/bin/sail npm run dev

build:
	vendor/bin/sail npm run build
