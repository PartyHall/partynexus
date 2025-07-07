.PHONY: tests migrations export consume-mails consume-export

USER := $(shell id -u):$(shell id -g)

VERSION = 0.1.22
COMMIT = $(shell git rev-parse --short HEAD)

up:
	@docker compose up --build -d --remove-orphans
	$(MAKE) reset-db

shell:
	@docker compose exec app bash

migrations:
	@docker compose exec app php bin/console doctrine:migrations:diff

migrate:
	@docker compose exec app php bin/console doctrine:migrations:migrate -vv --env=dev --no-interaction

clear:
	@docker compose exec app php bin/console cache:clear -v --env=dev

reset-db:
	@docker compose exec app bin/console doctrine:schema:drop --force --full-database
	$(MAKE) migrate
	@docker compose exec app rm -rf /app/var/uploaded_pictures /app/var/exports /app/var/timelapses /app/public/backdrops/* /app/public/song_covers/*
	@docker compose exec app bin/console doctrine:fixtures:load --no-interaction --append
	$(MAKE) export

export:
	@docker compose exec app bin/console event:export 0192bf5a-67d8-7d9d-8a5e-962b23aceeaa -vvv

consume-mails:
	@docker compose exec app bin/console messenger:consume emails -vvv

consume-export:
	@docker compose exec app bin/console messenger:consume export -vvv

lint:
	$(MAKE) phpcsfixer
	$(MAKE) phpstan

phpstan:
	@docker compose exec app php -d memory_limit=8G vendor/bin/phpstan analyse

phpcsfixer:
	@docker compose exec app php -d memory_limit=-1 vendor/bin/php-cs-fixer fix --dry-run -vv --diff

phpcsfixer-fix:
	@docker compose exec app php -d memory_limit=-1 vendor/bin/php-cs-fixer fix -vv --diff

lint-fix:
	@docker compose exec app php -d memory_limit=-1 vendor/bin/php-cs-fixer fix -vv --diff

fix-perms:
	sudo chown -R "$(USER)" .

gen-key:
	@docker compose exec app bin/console lexik:jwt:generate-keypair # @TODO: Only during init, if not exist

lint-ts:
	@docker compose exec frontend npx prettier . --write
	@docker compose exec frontend npm run lint -- --fix

tests:
	@docker compose exec app bin/phpunit

build-release:
	@rm -rf config/jwt
	@docker build --no-cache --file ./docker/app/Dockerfile --build-arg PARTYNEXUS_VERSION=$(VERSION) --build-arg PARTYNEXUS_COMMIT=$(COMMIT) --target frankenphp_prod --tag partynexus .
