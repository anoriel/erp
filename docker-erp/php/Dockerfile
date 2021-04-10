FROM php:7.4-fpm-alpine

COPY wait-for-it.sh /usr/bin/wait-for-it
COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN chmod +x /usr/bin/wait-for-it
RUN apk --update --no-cache add git bash openssh nodejs npm yarn python3
RUN docker-php-ext-install pdo_mysql
RUN PATH=$PATH:/var/www/vendor/bin:bin

WORKDIR /var/www

CMD composer install ; npm install --force; wait-for-it database:3306 -- bin/console about; bin/console doctrine:migrations:migrate --no-interaction; bin/console doctrine:fixtures:load --no-interaction; php-fpm 

EXPOSE 9000
