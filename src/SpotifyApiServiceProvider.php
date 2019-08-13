<?php

    namespace Polaris;

    use GuzzleHttp\Client;
    use Illuminate\Support\ServiceProvider;

    class SpotifyApiServiceProvider extends ServiceProvider
    {
        /**
         * Bootstrap services.
         *
         * @return void
         */
        public function boot()
        {
            //
        }

        /**
         * Register services.
         *
         * @return void
         */
        public function register()
        {
            $this->app->singleton(SpotifyApi::class, function ($app)
            {
                $client = new Client();

                return new SpotifyApi($client);
            });
        }
    }
