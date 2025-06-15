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
	@$(DOCKER_COMP) exec app composer install

phpunit:
	@$(DOCKER_COMP) exec app vendor/bin/phpunit --colors=auto

coverage:
	@$(DOCKER_COMP) exec -e XDEBUG_MODE=coverage app vendor/bin/phpunit --coverage-html var/phpunit/coverage

stan:
	@$(DOCKER_COMP) exec app php vendor/bin/phpstan analyse src

cs:
	@$(DOCKER_COMP) exec -e PHP_CS_FIXER_IGNORE_ENV=1 app vendor/bin/php-cs-fixer fix src

rector:
	@$(DOCKER_COMP) exec app vendor/bin/rector

make test: phpunit stan rector cs
