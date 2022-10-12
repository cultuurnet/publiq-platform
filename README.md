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

- Create an `auth.json` file inside the root of the project to install Laravel Nova
```
{
    "http-basic": {
        "nova.laravel.com": {
            "username": "dev@publiq.be",
            "password": "***"
        }
    }
}
```

- Install composer dependencies with lightweight container (this container can be deleted after installation)
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

## Nova

- Create a new Nova admin user with the following command
```
$ docker-compose exec laravel php artisan nova:user
```
Visit the application at [http://localhost/admin](http://localhost/admin) and login with the credentials you just created

- Check the Laravel Nova license key registration with
```
$ php artisan nova:check-license
```
This requires:
- correct value of the `NOVA_LICENSE_KEY` environment variable in the `.env` file
- correct production URL on [https://nova.laravel.com/licenses](https://nova.laravel.com/licenses)
