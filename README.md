# tku-duty-sys

This application is made with [**Laravel**](https://laravel.com).

If you're not using the docker image(build from docker file), you will need to make sure your environment meets the following requirements.

 - `php` >= `7.1.3` php processor
    - `pdo_mysql` MySQL connection
    - `gd` for [`github.com/PHPOffice/PhpSpreadsheet`](https://github.com/PHPOffice/PhpSpreadsheet)
    - `zip` for [`github.com/PHPOffice/PhpSpreadsheet`](https://github.com/PHPOffice/PhpSpreadsheet)
    - `igbinary` for `redis` extension
    - `redis`(optional) for Redis
    - `xdebug`(development) for coverage
 - `composer` for app depends php libraries
 - `npm` for font end style sheets and scrips

## setup

### php dependencies

```sh
composer install --optimize-autoloader --no-dev
```

### npm

```sh
npm install
npm run production
rm -fr node_modules # clean up
```

### cache

```sh
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## database

### migrate

```sh
php artisan migrate
```

#### refresh

```sh
php artisan migrate:refresh # do not do this in production unless you know exactly what you're doing
```

## docker

[Docker](https://docs.docker.com/)

### build

```sh
docker build -t image-name -f docker/Dockerfile .
```

### run

```sh
docker run -d --restart always -p port:80 \
    --env DB_CONNECTION=mysql \
    --env DB_HOST=mysql \
    --env DB_PORT=3306 \
    --env DB_DATABASE=database \
    --env DB_USERNAME=username \
    --env DB_PASSWORD=password \
    --env APP_ENV=production \
    --env APP_NAME=app_name \
    --env APP_DEBUG=false \
    image-name
```

### run (with redis)

```sh
docker run -d --restart always -p port:80 \
    --env DB_CONNECTION=mysql \
    --env DB_HOST=mysql \
    --env DB_PORT=3306 \
    --env DB_DATABASE=database \
    --env DB_USERNAME=username \
    --env DB_PASSWORD=password \
    --env REDIS_HOST=redis_hostname \
    --env SESSION_DRIVER=redis \
    --env APP_ENV=production \
    --env APP_NAME=app_name \
    --env APP_DEBUG=false \
    image-name
```

### development

#### build image

```sh
cd docker
docker build -t duty_sys_dev -f dev.dockerfile .
```

#### run

```sh
docker run --interactive --tty --rm --volume "${PWD}:/app" --workdir /app duty_sys ash
```

#### test

```sh
docker run \
    --interactive \
    --tty \
    --rm \
    --network dev \
    --user "$(id -u $(whoami)):$(id -g $(whoami))" \
    --volume "${PWD}:/app" \
    --workdir /app \
    --name tku_duty_sys_test \
    duty_sys_dev \
    ./vendor/bin/phpunit --coverage-text --coverage-html .coverage
```

#### artisan serve

```sh
docker run \
    --interactive \
    --tty \
    --rm \
    --network dev \
    --user "$(id -u $(whoami)):$(id -g $(whoami))" \
    --volume "${PWD}:/app" \
    --workdir /app \
    --publish 127.0.0.1:8000:8000 \
    --name tku_duty_sys_dev \
    duty_sys_dev \
    php artisan serve --host 0.0.0.0 --port 8000 --tries 0
```
