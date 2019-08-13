<?php

    namespace Polaris;

    use GuzzleHttp\Client;
    use Illuminate\Http\RedirectResponse;
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
         * Wrapper around account object. Simplifies calls from end user.
         *
         * @return RedirectResponse
         * @throws Exceptions\SpotifyAuthException
         */
        public function requestAccessCode(): RedirectResponse
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
            // Try to get access token first from memory and then from session.
            $accessToken = $this->accountClient->getAccessToken() ?? session('spotify_session')->access_token;

            // If no access token found, then raise exception.
            if (!$accessToken) {
                throw new SpotifyAuthException('Invalid access token provided.');
            }

            // Return json decode response.
            return $this->json(
                $this->client->get(self::API_URL.'/me', $this->accountClient->getAuthHeaders())
            );
        }

        /**
         * Json encode response param.
         *
         * @param $response
         * @return mixed
         */
        public function json($response)
        {
            return json_decode($response, false);
        }
    }