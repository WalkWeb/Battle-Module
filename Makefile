# Executables (local)
DOCKER_COMP = docker compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec php-7.4

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer

# ----------------------------------------------------------------------------------------------------------------------

phpunit:
	@$(DOCKER_COMP) exec php-7.4 vendor/bin/phpunit --colors=auto

coverage:
	@$(DOCKER_COMP) exec -e XDEBUG_MODE=coverage php-7.4 vendor/bin/phpunit --coverage-html var/phpunit/coverage
