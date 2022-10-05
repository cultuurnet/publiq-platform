# publiq-platform

## Pre-requisites
- Docker Desktop
- PHP 8.1 (for the installation of the project)

## Installation

- Clone the repository
```
$ git clone git@github.com:cultuurnet/publiq-platform.git
```

- Install dependencies
```
$ composer install
```

- Create `.env` file
```
$ cp .env.example .env
```

- Generate application key
```
$ php artisan key:generate
```

## Usage

- Start the application
```
$ ./vendor/bin/sail up
```

- Visit the application at [http://localhost](http://localhost)

## Testing

- Run tests
```
$ ./vendor/bin/sail php artisan test
```
