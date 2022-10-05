# publiq-platform

## Pre-requisites
- Docker Desktop, can be downloaded from [https://www.docker.com/products/docker-desktop/](https://www.docker.com/products/docker-desktop)

## Installation

- Clone the repository and put the working directory to `publiq-platform`
```
$ git clone git@github.com:cultuurnet/publiq-platform.git
$ cd publiq-platform
```

- Create `.env` file
```
$ cp .env.example .env
```

- Install composer dependencies with lightweight container
```
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php81-composer:latest \
    composer install
```

- Start the docker containers
```
$ docker-compose up -d
```

- Generate application key
```
$ docker-compose exec laravel php artisan key:generate
```

## Usage

- Start the application containers in detached mode and then visit the application at [http://localhost](http://localhost)
```
$ docker-compose up -d
```

- Start an interactive shell session
```
$ docker-compose exec laravel sh
```

- Stopping the application containers
```
$ docker-compose down
```

## Testing

- Run tests
```
$ docker-compose exec laravel php artisan test
```
