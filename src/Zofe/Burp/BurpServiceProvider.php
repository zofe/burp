<?php

namespace Zofe\Burp;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class BurpServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('zofe/burp', 'burp');
    }

    public function register()
    {

        $this->app->booting(function () {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Burp', 'Zofe\Burp\Burp');

        });

    }

}