FROM 808560791769.dkr.ecr.eu-central-1.amazonaws.com/php-7.3-base:v3.0.0 AS base


FROM base AS build-php

WORKDIR /var/app
COPY --from=composer:1.10 /usr/bin/composer /usr/bin/composer
COPY ./composer.* /var/app/
COPY ./docroot /var/app/docroot
ARG COMPOSER_TOKEN
RUN composer config --global --auth http-basic.repo.packagist.com token ${COMPOSER_TOKEN}
RUN composer install --prefer-dist --optimize-autoloader --no-dev --no-progress


FROM node:14.8.0 AS build-node

COPY --from=build-php /var/app/docroot /app

RUN cd /app/themes/custom/athora \
    && npm install
RUN cd /app/themes/custom/athora/gulp \
    && ../node_modules/.bin/gulp build


FROM base

COPY --from=build-php /var/app /var/app
COPY --from=build-node /app/themes/custom/athora /var/app/docroot/themes/custom/athora
COPY --from=build-node /app/modules/custom/tbn_search/assets/js/tbn_search.min.js /var/app/docroot/modules/custom/tbn_search/assets/js/

COPY ./.docker/image/nginx/site.conf /etc/nginx/conf.d/default.conf
COPY ./.docker/image/cron/crontab /etc/crontab

RUN ln -s /var/app/docroot /var/app/web
RUN chmod -R 777 /tmp
