FROM mirror.gcr.io/php:8.2-fpm

# Performance optimization - see https://gist.github.com/jpetazzo/6127116
# this forces dpkg not to call sync() after package extraction and speeds up install
RUN echo "force-unsafe-io" > /etc/dpkg/dpkg.cfg.d/02apt-speedup
# we don't need an apt cache in a container
RUN echo "Acquire::http {No-Cache=True;};" > /etc/apt/apt.conf.d/no-cache

RUN apt-get update && apt-get install -y \
        libssl-dev tcl-dev gettext libpq-dev libicu-dev libzip-dev libxml2-dev libpng-dev \
        libjpeg-dev xvfb libfontconfig wkhtmltopdf default-mysql-client

RUN docker-php-ext-install mysqli pdo_mysql \
    && docker-php-ext-install zip intl exif pcntl bcmath gettext dom

RUN apt-get update && apt-get install -y --allow-unauthenticated \
        bash vim nano git ssh zip unzip wget curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*; exit 0

RUN usermod -u 1000 www-data && \
     mkdir /home/www-data && \
     usermod -d /home/www-data www-data && \
     chown www-data:www-data /home/www-data

# Install composer
RUN mkdir -p /composer/vendor/bin && chown -R www-data:www-data /composer
ENV COMPOSER_HOME /composer
ENV PATH /composer/vendor/bin:$PATH
#ADD .docker/composer/auth.json /composer/auth.json
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer clear-cache && composer self-update

RUN mkdir -p /app && chown www-data:www-data /app \
    && mkdir -p /var/www/.config/psysh && chown www-data:www-data /var/www/.config/psysh

# Initialize application
WORKDIR /app

CMD ["php-fpm"]
