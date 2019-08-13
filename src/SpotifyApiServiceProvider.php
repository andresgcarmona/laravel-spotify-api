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
            $client = new Client();

            $this->app->singleton(SpotifyApi::class, function ($app) use ($client)
            {
                return new SpotifyApi($client);
            });

            $this->app->singleton(SpotifyAccount::class, function () use ($client)
            {
                $clientId     = config('services.spotify.client_id');
                $clientSecret = config('services.spotify.client_secret');
                $redirectUrl  = config('services.spotify.redirect_url');

                return new SpotifyAccount($client, $clientId, $clientSecret, $redirectUrl);
            });
        }
    }
