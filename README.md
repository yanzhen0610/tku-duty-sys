# tku-duty-sys

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

#### refresh

```sh
php artisan migrate:refresh # do not do this in production unless you know exactly what you're doing
```

## docker

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
