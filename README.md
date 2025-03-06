# publiq-platform

## Pre-requisites
- Docker Desktop, can be downloaded from [https://www.docker.com/products/docker-desktop/](https://www.docker.com/products/docker-desktop)
-  appconfig: you'll have to clone [appconfig](https://github.com/cultuurnet/appconfig) in the same folder as where you will clone [publiq-platform](https://github.com/cultuurnet/publiq-platform)

## Installation and setup

- Clone the repository and put the working directory to `publiq-platform`
```
$ git clone git@github.com:cultuurnet/publiq-platform.git
$ cd publiq-platform
```

- Install the config files from [appconfig](https://github.com/cultuurnet/appconfig) by running
```
$ make config
```

- Install composer dependencies with lightweight container (this container can be deleted after installation)
```
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install
```

- Start the docker containers
```
$ make up
```

If you also have the `udb3-backend` container running, you will get errors because of colliding port numbers for MySQL and Redis.
You can fix this with adding to the `.env` file:
```
FORWARD_DB_PORT=3307
FORWARD_REDIS_PORT=6380
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

## Integrations

The following integrations are provided:
- Keycloak, can be disabled with `KEYCLOAK_CREATION_ENABLED`
- UiTiD, can be disabled with `UITID_V1_CONSUMER_CREATION_ENABLED`
- Insightly, can be disabled with `INSIGHTLY_ENABLED`
- Sentry, can be disabled with `SENTRY_LARAVEL_ENABLED`

Check their relevant parts inside the environment file for correct configuration options.

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
## Dependabot

This project uses dependabot to keep libraries up-to-date.
More info can be found on our [Confluence](https://confluence.publiq.be/pages/viewpage.action?pageId=159482246)

## Makefile

- Bringing up the application containers
```
$ make up
```

- Stopping the application containers
```
$ make down
```

- Start an interactive bash session
```
$ make bash
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

- Run a test filtered on file
```
$ make test-filter filter=UpdateClientsTest
```

- Run a test with filtered on test method name
```
$ make test-filter filter=test_it_can_get_an_organization_with_vat
```

- Install dependencies needed to run e2e tests
```
$ make e2e-install
```

- Run all e2e tests
```
$ make test-e2e
```

- Run an e2e test filtered on file path or parts of file name
```
$ make test-e2e-filter filter=view-integrations-in-overview.test.ts
```

- Run an e2e test filtered on the test title
```
$ make test-e2e options="-g 'view my integrations in overview'"
```
