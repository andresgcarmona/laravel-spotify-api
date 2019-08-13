<?php

    namespace Polaris;

    use GuzzleHttp\Client;
    use GuzzleHttp\Exception\RequestException;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Support\Str;
    use Polaris\Exceptions\SpotifyAuthException;

    /**
     *
     * Class SpotifyAccount
     *
     * @package Polaris
     */
    class SpotifyAccount
    {
        protected const ACCOUNT_URL  = 'https://accounts.spotify.com';

        /**
         * Holds a references to Guzzle client object.
         *
         * @var Client
         */
        protected $client;

        /**
         * Spotify client id.
         *
         * @var string
         */
        protected $clientId;

        /**
         * Spotify client secret.
         *
         * @var string
         */
        protected $clientSecret;

        /**
         * The redirect URL for authorization purposes.
         *
         * @var string
         */
        protected $redirectUrl;

        /**
         * Spotify's access token.
         *
         * @var string
         */
        protected $accessToken;

        /**
         * Spotify's refresh token.
         *
         * @var string
         */
        protected $refreshToken;

        /**
         * Expiration time in seconds.
         *
         * @var int
         */
        protected $expirationTime;

        /**
         * Array of scopes that were granted by the user.
         *
         * @var array.
         */
        protected $grantedScopes;

        /**
         * SpotifyAccount constructor.
         *
         * @param  Client  $client
         * @param  string  $clientId
         * @param  string  $clientSecret
         * @param  string  $redirectUrl
         */
        public function __construct(Client $client, string $clientId, string $clientSecret, string $redirectUrl)
        {
            $this->client       = $client;
            $this->clientId     = $clientId;
            $this->clientSecret = $clientSecret;
            $this->redirectUrl  = $redirectUrl;
        }

        /**
         * Returns access token.
         *
         * @return string
         */
        public function getAccessToken(): string
        {
            return $this->accessToken;
        }

        /**
         * Returns refresh token.
         *
         * @return string
         */
        public function getRefreshToken(): string
        {
            return $this->refreshToken;
        }

        /**
         * Returns expiration time.
         *
         * @return int
         */
        public function getExpirationTime(): string
        {
            return $this->expirationTime;
        }

        /**
         * Returns the authentication header that is need to perform further requests.
         *
         * @return array
         */
        public function getAuthHeaders(): array
        {
            return [
                'Accept'         => 'application/json',
                'Authentication' => 'Bearer '.$this->getAccessToken(),
            ];
        }

        /**
         * Redirects the user to the Spotify's app authorization page.
         *
         * @return RedirectResponse
         * @throws SpotifyAuthException
         */
        public function requestAccessCode(?string $state = null): RedirectResponse
        {
            // Get scopes from services config file. Pass default if config not provided.
            $scopes = urlencode(implode(' ', config('services.spotify.scopes', [
                'user-read-recently-played',
                'user-read-private',
                'user-read-email',
                'user-library-read',
            ])));

            $showDialog = config('services.spotify.show_dialog', false);

            $params = [
                'response_type' => 'code',
                'client_id'     => $this->clientId,
                'redirect_uri'  => $this->redirectUrl,
                'show_dialog'   => $showDialog,
                'scopes'        => $scopes,
                'state'         => $state ?? Str::random(),
            ];

            return redirect()->to($this->getRequestAccessCodeUrl($params));
        }

        /**
         * Request access token from Spotify.
         *
         * @param  string  $code
         * @return mixed
         * @throws SpotifyAuthException
         */
        public function requestAccessToken(string $code)
        {
            try {
                // Send post request to api/token end point to exchange provided code for access token.
                $response = $this->client->post(self::ACCOUNT_URL.'/api/token', [
                    'headers'     => [
                        'Authorization' => 'Basic '.base64_encode($this->clientId.':'.$this->clientSecret),
                        'Accept'        => 'application/json',
                    ],
                    'form_params' => [
                        'grant_type'   => 'authorization_code',
                        'code'         => $code,
                        'redirect_uri' => $this->redirectUrl,
                    ],
                ]);

                // Encode response as json object.
                $response = json_decode($response->getBody(), false);

                // Set local state fields.
                if (isset($response->access_token, $response->refresh_token)) {
                    $this->accessToken    = $response->access_token;
                    $this->refreshToken   = $response->refresh_token;
                    $this->expirationTime = $response->expires_in;
                    $this->grantedScopes  = $response->scopes ?? [];
                }

                return $response;
            } catch (RequestException $exception) {
                throw new SpotifyAuthException($exception->getMessage(), $exception->getCode());
            }
        }

        /**
         * Returns the formatted URL to request the access code.
         *
         * @param  array  $params
         * @return string
         */
        private function getRequestAccessCodeUrl(array $params): string
        {
            return self::ACCOUNT_URL.'/authorize?'.http_build_query($params);
        }
    }