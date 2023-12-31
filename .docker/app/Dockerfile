FROM php:8.1-fpm

#
#--------------------------------------------------------------------------
# Core Software's Installation
#--------------------------------------------------------------------------
#

RUN apt-get -qy update && DEBIAN_FRONTEND=noninteractive apt-get -qy install --no-install-recommends \
		zip unzip \
        wget curl \
        libpq-dev \
		libicu-dev \
		nginx runit \
		librabbitmq-dev \
		libssl-dev tcl-dev gettext git ssh \
		bash vim procps net-tools gosu && \
		apt-get autoremove -qy && apt-get clean -qy && \
		rm -rf /tmp/* /var/tmp/* /var/lib/apt/lists/* /etc/nginx/conf.d/* /etc/nginx/sites-enabled/* /etc/nginx/sites-available/*

# END Core Software's Installation  -------------

#
#--------------------------------------------------------------------------
# PHP Installation
#--------------------------------------------------------------------------
#
RUN echo "docker-php-ext-install"     && \
    pecl update-channels              && \
    docker-php-ext-install bcmath     && \
    docker-php-ext-install opcache    && \
    docker-php-ext-install pcntl      && \
    pecl install -o -f xdebug    && \
    docker-php-ext-configure intl            \
        && docker-php-ext-install intl          \
    && rm -rf /tmp/* /var/tmp/* /var/lib/apt/lists/*

# END PHP Installation --------------------------

#
#--------------------------------------------------------------------------
# nginx & runit Configuration
#--------------------------------------------------------------------------
#

ADD ./configs/nginx.microservice.conf /etc/nginx/sites-enabled/
ADD ./configs/nginx.conf /etc/nginx/
ADD	./service/ /etc/service/
RUN	touch /etc/inittab && \
    rm -rf /etc/nginx/conf.d/* && \
	chmod +x /etc/service/*/run

ADD ./50-custom.ini         /usr/local/etc/php/conf.d/

# Installing Composer
RUN printf "\nInstalling Composer\n\n"; \
    EXPECTED_SIGNATURE=$(wget -q -O - https://composer.github.io/installer.sig); \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"; \
    ACTUAL_SIGNATURE=$(php -r "echo hash_file('SHA384', 'composer-setup.php');"); \
    if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]; \
    then \
      >&2 echo 'ERROR: Invalid installer signature'; \
      exit 1; \
    else \
      php composer-setup.php --install-dir=/usr/local/bin --filename=composer.phar; \
    fi; \
    rm composer-setup.php
    
ADD ./composer.sh        /usr/local/sbin/composer
RUN sed -i 's/\r//'  /usr/local/sbin/composer && \
    chmod +x         /usr/local/sbin/composer

ENV PATH /var/www:$PATH

# ubuntu >= 16.10
ARG PUID=1000
ENV PUID ${PUID}
RUN if [ -n "${PUID}" ] && [ "${PUID%:*}" != 0 ]; then \
  usermod -u ${PUID} www-data >/dev/null 2>&1 \
;fi

WORKDIR /var/www

ARG PORT=80
EXPOSE $PORT

ENTRYPOINT ["runsvdir", "-P", "/etc/service"]
