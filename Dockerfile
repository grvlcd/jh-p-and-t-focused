FROM richarvey/nginx-php-fpm:latest
COPY . .
# Image config
ENV WEBROOT /var/www/html/public
ENV APP_ENV production
ENV APP_DEBUG false
RUN composer install --no-dev
CMD ["/start.sh"]