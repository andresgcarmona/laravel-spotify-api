<?php

    namespace Polaris;

    use GuzzleHttp\Client;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Support\Collection;
    use Polaris\Exceptions\SpotifyAuthException;

    /**
     * Class SpotifyApi
     *
     * @package Polaris
     */
    class SpotifyApi
    {
        /**
         *
         */
        const API_URL = 'https://api.spotify.com/v1';

        /**
         * Holds a references to Guzzle Client class.
         *
         * @var Client
         */
        protected $client;

        /**
         * Holds a reference to the SpotifyAccount class.
         *
         * @var SpotifyAccount
         */
        protected $accountClient;

        /**
         * SpotifyApi constructor.
         *
         * @param  Client  $client
         * @param  SpotifyAccount  $accountClient
         */
        public function __construct(Client $client, SpotifyAccount $accountClient)
        {
            $this->client        = $client;
            $this->accountClient = $accountClient;
        }

        /**
         * Returns the accountClient object.
         *
         * @return SpotifyAccount
         */
        public function getAccountClient(): SpotifyAccount
        {
            return $this->accountClient;
        }

        /**
         * Wrapper around account object. Simplifies calls from end user.
         *
         * @param  string  $state
         * @return RedirectResponse
         * @throws SpotifyAuthException
         */
        public function requestAccessCode(string $state): RedirectResponse
        {
            return $this->accountClient->requestAccessCode();
        }

        /**
         * Wrapper around account object. Simplifies calls from end user.
         *
         * @param $code
         * @return mixed
         * @throws Exceptions\SpotifyAuthException
         */
        public function requestAccessToken($code)
        {
            return $this->accountClient->requestAccessToken($code);
        }

        /**
         * Calls Spotify's https://api.spotify.com/v1/me to get user account information.
         *
         * @return mixed
         * @throws SpotifyAuthException
         */
        public function me()
        {
            // Validate access token first.
            if($this->accountClient->validateAccessToken()) {
                // Return json decode response.
                return $this->json(
                    $this->client->get(self::API_URL.'/me', $this->accountClient->getAuthHeaders())
                );
            }
        }

        /**
         * Returns recently played collection of tracks.
         *
         * @return Collection
         * @throws SpotifyAuthException
         */
        public function recentlyPlayed(): Collection
        {
            // Validate access token first.
            if($this->accountClient->validateAccessToken()) {
                // Return json decode response.
                return collect(
                    $this->json(
                        $this->client->get(self::API_URL.'/me/player/recently-played',
                            $this->accountClient->getAuthHeaders())
                    )
                );
            }

            return collect();
        }

        /**
         * Json encode response param.
         *
         * @param $response
         * @return mixed
         */
        public function json($response)
        {
            // Return json decode response.
            return json_decode($response, false);
        }
    }