<?php

namespace Publiq\InsightlyLink;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Nova::serving(function (ServingNova $event) {
            Nova::script('insightly-link', __DIR__.'/../dist/js/field.js');
            Nova::style('insightly-link', __DIR__.'/../dist/css/field.css');
        });
    }

    public function register(): void
    {
    }
}
