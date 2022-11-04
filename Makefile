.PHONY: up down migrate lint stan test

up:
	vendor/bin/sail up -d

down:
	vendor/bin/sail down

migrate:
	vendor/bin/sail artisan migrate

lint:
	vendor/bin/sail composer lint

stan:
	vendor/bin/sail composer stan

test:
	vendor/bin/sail composer test
