FROM php:7.4-apache

WORKDIR /var/www/html

RUN apt update && apt -y install cron

COPY cron_run_every_second /etc/cron.d/cron_run_every_second

RUN chmod 0644 /etc/cron.d/cron_run_every_second
COPY . .

EXPOSE 80

CMD ["/bin/bash", "-c", "/usr/sbin/cron ; apache2-foreground"]