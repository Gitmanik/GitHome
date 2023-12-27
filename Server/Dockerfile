FROM php:7.4-apache

WORKDIR /var/www/html
RUN apt update && apt -y install cron
RUN docker-php-ext-install pdo_mysql

COPY . .
COPY build/cron_run_every_second /etc/cron.d/smarthome
RUN chmod 0644 /etc/cron.d/smarthome
RUN crontab /etc/cron.d/smarthome

COPY build/logging.ini /usr/local/etc/php/conf.d/logging.ini

RUN rm -rf build
RUN rm Dockerfile
RUN rm -rf .devcontainer

EXPOSE 80

CMD ["/bin/bash", "-c", "/usr/sbin/cron ; apache2-foreground"]