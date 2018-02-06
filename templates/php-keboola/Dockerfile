FROM php:7.1-cli

ENV COMPOSER_ALLOW_SUPERUSER 1

WORKDIR /code

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        wget \
	&& rm -r /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php \
  && mv /code/composer.phar /usr/local/bin/composer

COPY . /code/
COPY docker/php.ini /usr/local/etc/php/php.ini
COPY docker/composer-install.sh /tmp/composer-install.sh

RUN /tmp/composer-install.sh \
	&& php composer.phar --no-interaction install \
	&& rm -f composer.phar

CMD ["php", "/code/src/run.php"]
