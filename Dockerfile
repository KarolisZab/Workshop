FROM php:8.1-apache
RUN curl https://repo.mongodb.org/apt/debian/dists/bullseye/mongodb-org/4.4/main/binary-amd64/mongodb-database-tools_100.5.3_amd64.deb -L -o /tmp/mongo-tools.deb \
    && apt-get update && apt-get install -y \
        git \
        libpq-dev \
        libcurl4-openssl-dev \
        pkg-config \
        libssl-dev \
        locales \
        zip \
        apt-transport-https \
        python3-pip \
        libicu-dev \
        libapache2-mod-security2 \
        libxml2-dev \
        /tmp/mongo-tools.deb \
    && rm -rf /tmp/mongo-tools.deb

RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen
RUN locale-gen
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN pecl install mongodb \
     && docker-php-ext-enable mongodb

RUN composer global config allow-plugins.symfony/flex true --no-interaction
RUN set -eux; \
    composer global require "symfony/flex" --prefer-dist --no-progress --no-suggest --classmap-authoritative; \
    composer clear-cache
ENV PATH="${PATH}:/root/.composer/vendor/bin"

RUN yes | pecl install xdebug-3.1.0 \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.log_level=0" >> /usr/local/etc/php/conf.d/xdebug.ini

RUN a2enmod rewrite headers
RUN a2enmod security2
ARG app_env='prod'
ARG app_debug='0'
ARG url_basepath='/'
ARG app_version=''

RUN sed -i 's@html@html/public@g' /etc/apache2/sites-available/000-default.conf
RUN sed -i "/<\/VirtualHost>/ i\Options FollowSymLinks" /etc/apache2/sites-available/000-default.conf
RUN echo "ServerTokens Full" >> /etc/apache2/conf-available/security.conf
RUN echo "ServerSignature Off" >> /etc/apache2/conf-available/security.conf
RUN echo 'SecServerSignature " "' >> /etc/apache2/conf-available/security.conf

COPY ./ /var/www/html 

RUN mkdir /var/www/html/var || echo 'var directory already exists'
RUN chmod -R 777 /var/www/html/var
RUN chmod -R 777 /var/www/html/public
RUN chmod -R 777 /tmp