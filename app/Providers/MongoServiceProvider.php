<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use MongoDB\Client;

class MongoServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('mongodb', function () {
            return new Client(getenv('DB_CONNECTION_STRING'));
        });

        $this->app->singleton('mongoCollection', function ($app) {
            $client = $app->make('mongodb');
            $database = $client->geo_city;
            return $database->geo_city_connection;
        });
    }
}
