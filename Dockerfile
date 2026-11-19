FROM php:8.3-apache

RUN docker-php-ext-install mysqli

WORKDIR /var/www/html
COPY . /var/www/html
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

EXPOSE 10000
CMD ["docker-entrypoint"]
