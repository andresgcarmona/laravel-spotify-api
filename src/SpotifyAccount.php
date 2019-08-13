<?php

    namespace Polaris;

    use Carbon\Carbon;
    use GuzzleHttp\Client;
    use GuzzleHttp\Exception\RequestException;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Session\SessionManager;
    use Illuminate\Session\Store;
    use Illuminate\Support\Str;
    use Polaris\Exceptions\SpotifyAuthException;
    use Psr\Http\Message\ResponseInterface;
    use stdClass;

    /**
     *
     * Class SpotifyAccount
     *
     * @package Polaris
     */
    class SpotifyAccount
    {
        protected const ACCOUNT_URL  = 'https://accounts.spotify.com';
        protected const SESSION_NAME = 'spotify_session';

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
        protected $expiresAt;

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
         * Return the object saved in Session under SESSION_NAME key.
         *
         * @return SessionManager|Store|mixed
         */
        public function getAccessTokenSession()
        {
            return session(self::SESSION_NAME, null);
        }

        /**
         * Returns access token.
         *
         * @return string
         */
        public function getAccessToken(): ?string
        {
            return $this->accessToken ?? session(self::SESSION_NAME) ? session(self::SESSION_NAME)->access_token : null;
        }

        /**
         * Returns refresh token.
         *
         * @return string
         */
        public function getRefreshToken(): string
        {
            return $this->refreshToken ?? session(self::SESSION_NAME) ? session(self::SESSION_NAME)->refresh_token : null;
        }

        /**
         * Returns expiration time.
         *
         * @return int
         */
        public function getExpiresAt(): string
        {
            return $this->expiresAt;
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

                $this->updateSession($response);

                return $response;
            } catch (RequestException $exception) {
                throw new SpotifyAuthException($exception->getMessage(), $exception->getCode());
            }
        }

        /**
         * Calls /api/token with a grant_type of refresh_token to update de access token.
         *
         * @return mixed|ResponseInterface
         * @throws SpotifyAuthException
         */
        public function refreshAccessToken()
        {
            // Get access token from memory or from session.
            $refreshToken = $this->refreshToken ?? $this->getAccessTokenSession()->refresh_token;

            // If refresh token is null or not isset throw an exception.
            if (!$refreshToken) {
                throw new SpotifyAuthException('Invalid refresh access token.', 402);
            }

            try {
                // Send post request to api/token end point to refresh the access token.
                $response = $this->client->post(self::ACCOUNT_URL.'/api/token', [
                    'headers'     => [
                        'Authorization' => 'Basic '.base64_encode($this->clientId.':'.$this->clientSecret),
                        'Accept'        => 'application/json',
                    ],
                    'form_params' => [
                        'grant_type'    => 'refresh_token',
                        'refresh_token' => $refreshToken,
                    ],
                ]);

                // Encode response as json object.
                $response = json_decode($response->getBody(), false);

                $this->updateSession($response);

                return $response;
            } catch (RequestException $exception) {
                throw new SpotifyAuthException($exception->getMessage(), $exception->getCode());
            }
        }

        /**
         * Returns true if the accessToken is set AND the expiration time (expires_at) is less than or equal to now().
         *
         * @return bool
         */
        public function isAccessTokenValid(): bool
        {
            // getAccessToken returns the accessTokenSession field in this class if present, or the access_token from session.
            $accessTokenSession = $this->getAccessTokenSession();

            return $accessTokenSession && Carbon::createFromTimestamp($accessTokenSession->expires_at)
                                                ->lte(Carbon::now());
        }

        /**
         * Validate the access token. If the token is not valid then calls refreshAccessToken to get a new one.
         *
         * @return bool
         * @throws SpotifyAuthException
         */
        public function validateAccessToken(): bool
        {
            // Token is null or has expired.
            if (!$this->isAccessTokenValid()) {
                $this->refreshAccessToken();
            }

            return true;
        }

        /**
         * Returns the formatted URL to request the access code.
         *
         * @param  array  $params
         * @return string
         */
        protected function getRequestAccessCodeUrl(array $params): string
        {
            return self::ACCOUNT_URL.'/authorize?'.http_build_query($params);
        }

        /**
         * Updates the session object with fresh data.
         *
         * @param  stdClass  $response
         */
        protected function updateSession(stdClass $response): void
        {
            // Set expires_at field.
            $response->expires_at = Carbon::now()->addSeconds($response->expires_in)->timestamp;

            // Set local state fields.
            if (isset($response->access_token)) {
                $this->accessToken   = $response->access_token;
                $this->refreshToken  = $response->refresh_token ?? $this->getRefreshToken();
                $this->expiresAt     = $response->expires_at;
                $this->grantedScopes = $response->scopes ?? [];
            }

            // Save response in session.
            session(self::SESSION_NAME, $response);
        }
    }