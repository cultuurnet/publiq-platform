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
            "password": "laravel nova license key here"
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
$ make npm-dev
```

## Updating

After pulling new changes via git, you can update the backend and frontend applications by re-running `make install`.

## Nova

Visit the application at [http://localhost/admin](http://localhost/admin) and login with your UiTiD credentials from the acceptance environment.

- On localhost Laravel Nova allows all authenticated users. On other hosts a check is done on email. The allowed emails are configured inside the config `users` in `config/nova.php`. For example:
```
'users' => [
    'dev@publiq.be',
],
```

- Check the Laravel Nova license key registration with
```
$ php artisan nova:check-license
```
This requires:
- correct value of the `NOVA_LICENSE_KEY` environment variable in the `.env` file
- correct production URL on [https://nova.laravel.com/licenses](https://nova.laravel.com/licenses)

## Auth0

### Authentication

Authentication will be handled by Auth0. The following environment variables need to be set in the `.env` file:
- `AUTH0_LOGIN_DOMAIN`
- `AUTH0_LOGIN_CLIENT_ID`
- `AUTH0_LOGIN_CLIENT_SECRET`
- `AUTH0_LOGIN_REDIRECT_URI`

### Management API

Besides simple authentication an integration with the Management API of Auth0 is required. The following environment variables need to be set in the `.env` file:
- `AUTH0_MANAGEMENT_DOMAIN`
- `AUTH0_MANAGEMENT_CLIENT_ID`
- `AUTH0_MANAGEMENT_CLIENT_SECRET`
- `AUTH0_MANAGEMENT_AUDIENCE`

## Insightly

The following environment variables need to be set in the `.env` file
- `INSIGHTLY_HOST`
- `INSIGHTLY_API_KEY`

## Horizon

### Configuration

By default Laravel processes jobs synchronously, because `QUEUE_CONNECTION` is set to `sync`.

To enable Horizon, set the `QUEUE_CONNECTION` environment variable to `redis` in the `.env` file.

Once asynchronous processing is enabled, you also need to start the queues with `make horizon`.

### Monitoring

Horizon has a dashboard for monitoring the queue. It can be accessed at [http://localhost/horizon](http://localhost/horizon).

- On localhost Horizon allows all authenticated users. On other hosts a check is done on email. The allowed emails are configured inside the config `users` in `config/nova.php`. For example:
```
'users' => [
    'dev@pulic.be`,
],
```

## Front-end setup Visual Studio Code

The code formatter Prettier is used for javascript/typescript files. To make sure the Visual Studio Code `format on save` action doesn't influence non-frontend files you need to add a `.vscode/settings.json` file and add the following config:

```json
{
  "editor.formatOnSave": false,
  "[typescript]": {
    "editor.defaultFormatter": "esbenp.prettier-vscode",
    "editor.formatOnSave": true
  },
  "[typescriptreact]": {
    "editor.defaultFormatter": "esbenp.prettier-vscode",
    "editor.formatOnSave": true
  },
  "[javascript]": {
    "editor.defaultFormatter": "esbenp.prettier-vscode",
    "editor.formatOnSave": true
  },
  "[javascriptreact]": {
    "editor.defaultFormatter": "esbenp.prettier-vscode",
    "editor.formatOnSave": true
  }
}
```

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

- Install/update the backend and frontend applications
```
$ make install
```

- Generate a new application key for encryption (also included in `make install`)
```
$ make key-generate
```

- Install composer dependencies (also included in `make install`)
```
$ make composer-install
```

- Install npm dependencies (also included in `make install`)
```
$ make npm-install
```

- Watch frontend assets
```
$ make npm-dev
```

- Build frontend assets (also included in `make install`)
```
$ make npm-build
```

- Format frontend files (with auto fixing)
```
$ make npm-format
```

- Check formatting frontend files
```
$ make npm-format-check
```

- Lint frontend files (with auto fixing)
```
$ make npm-lint
```

- Check linting frontend files
```
$ make npm-lint-check
```

- Check typing frontend files
```
$ make npm-types-check
```

- Running migrations (also included in `make install`)
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
