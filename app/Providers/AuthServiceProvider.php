<?php

namespace App\Providers;

use App\Models\Portfolio;
use App\Policies\PortfolioPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Portfolio::class => PortfolioPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}