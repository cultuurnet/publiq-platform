<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Contacts\Repositories\EloquentContactRepository;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ContactRepository::class, EloquentContactRepository::class);
    }
}
