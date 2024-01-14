FROM php:7.4-apache
WORKDIR /var/www/html
RUN apt update
RUN docker-php-ext-install pdo_mysql

# Latest releases available at https://github.com/aptible/supercronic/releases
ENV SUPERCRONIC_URL=https://github.com/aptible/supercronic/releases/download/v0.2.29/supercronic-linux-amd64 \
    SUPERCRONIC=supercronic-linux-amd64 \
    SUPERCRONIC_SHA1SUM=cd48d45c4b10f3f0bfdd3a57d054cd05ac96812b

RUN curl -fsSLO "$SUPERCRONIC_URL" \
 && echo "${SUPERCRONIC_SHA1SUM}  ${SUPERCRONIC}" | sha1sum -c - \
 && chmod +x "$SUPERCRONIC" \
 && mv "$SUPERCRONIC" "/usr/local/bin/${SUPERCRONIC}" \
 && ln -s "/usr/local/bin/${SUPERCRONIC}" /usr/local/bin/supercronic

COPY . .

COPY private/logging.ini /usr/local/etc/php/conf.d/logging.ini
RUN chmod +x private/start.sh

RUN rm Dockerfile
RUN rm -rf .devcontainer

EXPOSE 80

CMD /bin/bash -c 'private/start.sh'