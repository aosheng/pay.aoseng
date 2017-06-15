FROM php:7.0-fpm

RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
        libpq-dev \
        apt-utils \
    && docker-php-ext-install -j$(nproc) mcrypt \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd



#####################################
# xDebug:
#####################################

ARG INSTALL_XDEBUG=true
RUN if [ ${INSTALL_XDEBUG} = true ]; then \
    # Install the xdebug extension
    pecl install xdebug && \
    docker-php-ext-enable xdebug \
;fi

# Copy xdebug configration for remote debugging
COPY ./xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

#####################################
# PHP REDIS EXTENSION FOR PHP 7.0
#####################################
ARG INSTALL_PHPREDIS=true
RUN if [ ${INSTALL_PHPREDIS} = true ]; then \
    # Install Php Redis Extension
    pecl install -o -f redis \
    &&  rm -rf /tmp/pear \
    &&  docker-php-ext-enable redis \
    &&  echo "extension=redis.so" > /usr/local/etc/php/conf.d/redis.ini\
;fi



#####################################
# ZipArchive:
#####################################

#ARG INSTALL_ZIP_ARCHIVE=true
#RUN if [ ${INSTALL_ZIP_ARCHIVE} = true ]; then \
#    # Install the zip extension
#    pecl install zip && \
#    docker-php-ext-enable zip \
#;fi



#####################################
# bcmath:
#####################################

ARG INSTALL_BCMATH=true
RUN if [ ${INSTALL_BCMATH} = true ]; then \
    # Install the bcmath extension
    docker-php-ext-install bcmath \
;fi



#####################################
# PHP Memcached:
#####################################

RUN apt-get update && apt-get install -y \
        libmemcached11 \
        libmemcachedutil2 \
        libmemcached-dev \
        libz-dev \
        git \
    && cd /root \
    && git clone -b php7 https://github.com/php-memcached-dev/php-memcached \
    && cd php-memcached \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && cd .. \
    && rm -rf  php-memcached \
    && echo extension=memcached.so >> /usr/local/etc/php/conf.d/memcached.ini \
    && apt-get remove -y build-essential libmemcached-dev libz-dev \
    && apt-get remove -y \
        libmemcached-dev \
        libz-dev \
    && apt-get autoremove -y \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get clean



#####################################
# Opcache:
#####################################
ARG INSTALL_OPCACHE=false
RUN if [ ${INSTALL_OPCACHE} = true ]; then \
    docker-php-ext-install opcache && \
    docker-php-ext-enable opcache \
;fi



#####################################
# Codeigniter Modifications:
#####################################

ARG CODEIGNITER=true
RUN if [ ${CODEIGNITER} = true ]; then \
    # Install Codeigniter PHP extentions requirements
     docker-php-ext-install mysqli && \
     docker-php-ext-install pdo_mysql pdo_pgsql \
;fi



#####################################
# Exif:
#####################################

ARG INSTALL_EXIF=true
RUN if [ ${INSTALL_EXIF} = true ]; then \
    # Enable Exif PHP extentions requirements
    docker-php-ext-install exif && \
    docker-php-ext-enable exif \
;fi


# Clean up
RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN usermod -u 1000 www-data

WORKDIR /var/www
