# Executables (local)
DOCKER_COMP = docker compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec app

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer

# ----------------------------------------------------------------------------------------------------------------------

install:
	docker compose up -d
	@$(PHP_CONT) composer install

up:
	docker compose up -d

down:
	docker compose down --remove-orphans

rebuild:
	docker compose up -d --build --remove-orphans

phpunit:
	@$(PHP) vendor/bin/phpunit --colors=auto

coverage:
	@$(DOCKER_COMP) exec -e XDEBUG_MODE=coverage app vendor/bin/phpunit --coverage-html var/phpunit/coverage

stan:
	@$(PHP) vendor/bin/phpstan analyse src

cs:
	@$(DOCKER_COMP) exec -e PHP_CS_FIXER_IGNORE_ENV=1 app vendor/bin/php-cs-fixer fix src

rector:
	@$(PHP) vendor/bin/rector

make test: phpunit stan rector cs
