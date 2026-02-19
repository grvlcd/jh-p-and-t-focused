FROM richarvey/nginx-php-fpm:latest

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . .

# Image config
ENV WEBROOT /var/www/html/public
ENV APP_ENV production
ENV APP_DEBUG false

RUN mkdir -p storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

CMD ["bash", "scripts/00-laravel-deploy.sh"]