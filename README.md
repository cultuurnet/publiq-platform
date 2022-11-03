# publiq-platform

## Pre-requisites
- Docker Desktop, can be downloaded from [https://www.docker.com/products/docker-desktop/](https://www.docker.com/products/docker-desktop)

## Installation and setup

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
$ make up
```

- Install the backend and frontend apps
```
$ make install
```

- Watch the frontend assets (development only)
```
$ docker-compose exec laravel npm run dev
```

## Updating

After pulling new changes via git, you can update the backend and frontend applications by re-running `make install`.

## Makefile

- Bringing up the application containers
```
$ make up
```

- Stopping the application containers
```
$ make down
```

- Start an interactive shell session
```
$ make shell
```

- Install composer dependencies
```
$ make composer-install
```

- Install npm dependencies
```
$ make npm-install
```

- Watch frontend assets
```
$ make watch
```

- Build frontend assets
```
$ make build
```

- Running migrations
```
$ make migrate
```

- Run linting
```
$ make lint
```

- Run static analysis
```
$ make stan
```

- Run tests
```
$ make test
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
