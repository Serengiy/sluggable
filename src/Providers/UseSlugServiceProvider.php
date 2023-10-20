<?php

namespace Serengiy\Sluggable\Providers;

use Illuminate\Support\ServiceProvider;
use Serengiy\Sluggable\Traits\Models\HasSlug;

class UseSlugServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(HasSlug::class);
    }

}
