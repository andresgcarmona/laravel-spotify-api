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
                $client       = new Client();
                $clientId     = config('services.spotify.client_id');
                $clientSecret = config('services.spotify.client_secret');
                $redirectUrl  = config('services.spotify.redirect_url');

                return new SpotifyApi($client, $clientId, $clientSecret, $redirectUrl);
            });
        }
    }
