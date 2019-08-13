<?php

    namespace Polaris;

    use GuzzleHttp\Client;
    use GuzzleHttp\Exception\RequestException;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Support\Str;
    use Polaris\Exceptions\SpotifyAuthException;

    class SpotifyApi
    {
        const ACCOUNT_URL = 'https://accounts.spotify.com';
        const API_URL     = 'https://api.spotify.com';

        protected $client;

        protected $clientId;

        protected $clientSecret;

        protected $redirectUrl;

        public function __construct(Client $client, string $clientId, string $clientSecret, string $redirectUrl)
        {
            $this->client       = $client;
            $this->clientId     = $clientId;
            $this->clientSecret = $clientSecret;
            $this->redirectUrl  = $redirectUrl;
        }

        /**
         * Redirects the user to the Spotify's app authorization page.
         *
         * @return RedirectResponse
         * @throws SpotifyAuthException
         */
        public function requestAccessCode(): RedirectResponse
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
                'state'         => Str::random(),
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

                return json_decode($response->getBody(), true);
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