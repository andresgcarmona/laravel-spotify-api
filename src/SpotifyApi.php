<?php

    namespace Polaris;

    use GuzzleHttp\Client;

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
        const API_URL = 'https://api.spotify.com';

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
    }