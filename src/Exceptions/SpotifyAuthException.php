<?php /** @noinspection ALL */

    namespace Polaris\Exceptions;

    use Exception;
    use Throwable;

    /**
     * Custom spotify auth exception.
     *
     * Class SpotifyAuthException
     *
     * @package Polaris\Exceptions
     */
    class SpotifyAuthException extends Exception
    {
        public function __construct($message = '', $code = 0, Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
        }
    }