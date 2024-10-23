USER := $(shell id -u):$(shell id -g)

up:
	docker compose up --build -d --remove-orphans
	$(MAKE) reset-db

shell:
	docker compose exec app bash

gen-mig:
	docker compose exec app php bin/console doctrine:migrations:diff

migrate:
	docker compose exec app php bin/console doctrine:migrations:migrate -vv --env=dev --no-interaction

clear:
	docker compose exec app php bin/console cache:clear -v --env=dev

reset-db:
	docker compose exec app bin/console doctrine:schema:drop --force --full-database
	$(MAKE) migrate
	docker compose exec app bin/console doctrine:fixtures:load --no-interaction

lint:
	docker compose exec app vendor/bin/php-cs-fixer fix --dry-run -vv --diff
	docker compose exec app php -d memory_limit=8G vendor/bin/phpstan analyse

lint-fix:
	docker compose exec app vendor/bin/php-cs-fixer fix -vv --diff

fix-perms:
	sudo chown -R "$(USER)" .

gen-key:
	docker compose exec app bin/console lexik:jwt:generate-keypair # @TODO: Only during init, if not exist

lint-ts:
	@docker compose exec frontend npx prettier . --write
	@docker compose exec frontend npm run lint -- --fix

