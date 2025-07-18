init: docker-down-clear docker-pull docker-build-pull docker-up app-init
down: docker-down-clear
lint: app-lint
analyze: app-analyze
test: app-test
test-unit: app-test-unit
test-functional: app-test-functional

docker-up:
	docker-compose up -d

docker-down-clear:
	docker-compose down --remove-orphans

docker-pull:
	docker-compose pull

docker-build-pull:
	docker-compose build --pull

app-init: composer-install

app-lint:
	docker-compose run --rm php-cli composer lint
	docker-compose run --rm php-cli composer cs-check

app-fix:
	docker-compose run --rm php-cli composer cs-fix

app-analyze:
	docker-compose run --rm php-cli composer psalm

app-test:
	docker-compose run --rm php-cli composer test

app-test-unit:
	docker compose run --rm php-cli composer test -- --testsuite=unit

app-test-functional:
	docker compose run --rm php-cli composer test -- --testsuite=functional

composer-install:
	docker-compose run --rm php-cli composer install

composer-update:
	docker-compose run --rm php-cli composer update


