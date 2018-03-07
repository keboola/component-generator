FROM php:7.1-cli

ENV COMPOSER_ALLOW_SUPERUSER 1

WORKDIR /code

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        wget \
	&& rm -r /var/lib/apt/lists/*

COPY . /code/
COPY docker/php-prod.ini /usr/local/etc/php/php.ini
COPY docker/composer-install.sh /tmp/composer-install.sh

RUN /tmp/composer-install.sh \
	&& php /usr/local/bin/composer.phar --no-interaction install

CMD ["php", "/code/src/run.php"]
