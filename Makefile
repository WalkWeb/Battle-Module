# Executables (local)
DOCKER_COMP = docker compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec php-8.4

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer

# ----------------------------------------------------------------------------------------------------------------------

phpunit:
	@$(DOCKER_COMP) exec php-8.4 vendor/bin/phpunit --colors=auto

coverage:
	@$(DOCKER_COMP) exec -e XDEBUG_MODE=coverage php-8.4 vendor/bin/phpunit --coverage-html var/phpunit/coverage

stan:
	@$(DOCKER_COMP) exec php-8.4 php vendor/bin/phpstan analyse src

cs:
	@$(DOCKER_COMP) exec -e PHP_CS_FIXER_IGNORE_ENV=1 php-8.4 vendor/bin/php-cs-fixer fix src

rector:
	@$(DOCKER_COMP) exec php-8.4 vendor/bin/rector

make test: phpunit stan rector cs
