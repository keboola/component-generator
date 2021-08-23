FROM php:7.4-cli

ARG COMPOSER_FLAGS="--prefer-dist --no-interaction"
ARG DEBIAN_FRONTEND=noninteractive

ENV LANGUAGE=en_US.UTF-8
ENV LANG=en_US.UTF-8
ENV LC_ALL=en_US.UTF-8

ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_PROCESS_TIMEOUT 3600

COPY docker/composer-install.sh /tmp/composer-install.sh

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        automake \
        git \
        locales \
        libltdl-dev \
        libtool \
        gpg \
        ruby \
        ruby-dev \
        unzip \
        wget \
    && sed -i 's/^# *\(en_US.UTF-8\)/\1/' /etc/locale.gen \
    && locale-gen \
    && chmod +x /tmp/composer-install.sh \
    && /tmp/composer-install.sh

# Install GitHub CLI
RUN curl -fsSL https://cli.github.com/packages/githubcli-archive-keyring.gpg | gpg --dearmor -o /usr/share/keyrings/githubcli-archive-keyring.gpg
RUN echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/githubcli-archive-keyring.gpg] https://cli.github.com/packages stable main" | tee /etc/apt/sources.list.d/github-cli.list > /dev/null

RUN apt-get update \
    && apt-get install -y --no-install-recommends gh \
    && rm -rf /var/lib/apt/lists/*

# Install Travis CLI
RUN gem install travis \
    # Install shell completion
    && echo "y" | travis

RUN git config --global user.email "devel@keboola.com" \
    && git config --global user.name "Keboola Genesis"

COPY . /init-code/
WORKDIR /init-code/

## Composer - deps always cached unless changed
# First copy only composer files
COPY composer.* /init-code/
# Download dependencies, but don't run scripts or init autoloaders as the app is missing
RUN composer install $COMPOSER_FLAGS --no-scripts --no-autoloader
# copy rest of the app
COPY . /init-code/
# run normal composer - all deps are cached already
RUN composer install $COMPOSER_FLAGS

WORKDIR /code/
ENTRYPOINT ["php", "/init-code/application.php"]
CMD []
