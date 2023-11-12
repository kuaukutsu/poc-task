PHP_VERSION ?= 8.1
USER = $$(id -u)

composer:
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app \
		composer:latest \
		composer install --ignore-platform-req=ext-pcntl --ignore-platform-req=ext-sync

composer-up:
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app \
		composer:latest \
		composer update --ignore-platform-req=ext-pcntl --ignore-platform-req=ext-sync

composer-dump:
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app \
		composer:latest \
		composer dump-autoload --ignore-platform-req=ext-pcntl --ignore-platform-req=ext-sync

psalm:
	docker run --init -it --rm -v "$$(pwd):/app" -e XDG_CACHE_HOME=/tmp -w /app \
		ghcr.io/kuaukutsu/php:${PHP_VERSION}-cli \
		./vendor/bin/psalm

phpunit:
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app \
		ghcr.io/kuaukutsu/php:${PHP_VERSION}-cli \
		./vendor/bin/phpunit

phpcs:
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app \
		ghcr.io/kuaukutsu/php:${PHP_VERSION}-cli \
		./vendor/bin/phpcs

phpcbf:
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app \
		ghcr.io/kuaukutsu/php:${PHP_VERSION}-cli \
		./vendor/bin/phpcbf

rector:
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app \
		ghcr.io/kuaukutsu/php:${PHP_VERSION}-cli \
		./vendor/bin/rector

app-cli-build:
	USER=${USER} docker-compose -f ./docker-compose.yml build cli

cli:
	docker-compose -f ./docker-compose.yml run --rm -u ${USER} -w /src cli sh

test-builder:
	docker-compose -f ./docker-compose.yml run --rm -u ${USER} -w /src/tests -e XDEBUG_MODE=off cli \
		php builder.php --task=5

test-pm:
	docker-compose -f ./docker-compose.yml run --rm -u ${USER} -w /src/tests -e XDEBUG_MODE=off cli \
		php pm.php
