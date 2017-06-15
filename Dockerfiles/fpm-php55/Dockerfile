FROM php:5.5-fpm

RUN apt-get update \
  && apt-get install -y libmemcached11 libmemcachedutil2 build-essential libmemcached-dev libz-dev libmcrypt-dev libghc-postgresql-libpq-dev php5-gd libjpeg-dev libpng-dev freetype* libxml2-dev php-soap \
  && pecl install memcached \
  && apt-get remove -y build-essential libmemcached-dev libz-dev \
  && apt-get autoremove -y \
  && apt-get clean \
  && rm -rf /tmp/pear

RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/
RUN docker-php-ext-install mysql mysqli pdo pdo_mysql mbstring mcrypt pgsql pdo_pgsql gd soap
RUN docker-php-ext-enable memcached

COPY php.ini /usr/local/etc/php/

RUN usermod -u 1000 www-data
RUN usermod -G staff www-data
