FROM php:7-cli

ENV COMPOSER_ALLOW_SUPERUSER 1

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        automake \
        git \
        libltdl-dev \
        libtool \
        ruby \
        ruby-dev \
        unzip \
        wget \
    && rm -rf /var/lib/apt/lists/*

COPY . /init-code/
WORKDIR /init-code/
RUN /init-code/composer-install.sh \
	&& php composer.phar --no-interaction install \
	&& rm -f composer.phar \
	&& gem install travis \
	# Install shell completion
	&& echo "y" | travis \
	&& git config --global user.email "devel@keboola.com" \
	&& git config --global user.name "Keboola Genesis"

WORKDIR /code/
ENTRYPOINT ["php", "/init-code/application.php"]
CMD []
