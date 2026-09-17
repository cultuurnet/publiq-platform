.PHONY: up up-build down bash install composer-install npm-install key-generate migrate lint stan ci config

ifeq ($(CI),true)
DOCKER_COMPOSE_OPTIONS = -u 451:451 -T
else
DOCKER_COMPOSE_OPTIONS = -T
endif

up:
	docker compose up -d

# 'docker compose up' reuses an existing image even when the Dockerfile changed, so CI has to force the build
up-build:
	docker compose up -d --build

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

publish:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) platform php artisan nova:publish

horizon:
	docker compose exec platform artisan horizon

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

# 'docker compose exec' does not inherit the calling environment, so the vars the e2e tests need have to be forwarded explicitly

# HOME is overridden because DOCKER_COMPOSE_OPTIONS runs as uid 451 in CI, and that uid has no home directory in the image.
E2E_DOCKER_OPTIONS = -e HOME=/tmp \
	-e CI \
	-e E2E_TEST_BASE_URL \
	-e E2E_TEST_EMAIL -e E2E_TEST_PASSWORD \
	-e E2E_TEST_V1_EMAIL -e E2E_TEST_V1_PASSWORD \
	-e E2E_TEST_ADMIN_EMAIL -e E2E_TEST_ADMIN_PASSWORD \
	-e KEYCLOAK_LOGIN_ENABLED

e2e-install:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) -e HOME=/tmp platform npx playwright install chromium

test-e2e:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) $(E2E_DOCKER_OPTIONS) platform npx playwright test $(options)

test-e2e-filter:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) $(E2E_DOCKER_OPTIONS) platform npx playwright test "$(filter)" $(options)
