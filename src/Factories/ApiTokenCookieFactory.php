<?php

declare(strict_types=1);

namespace Rinvex\Oauth\Factories;

use Firebase\JWT\JWT;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Cookie;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Config\Repository as Config;

class ApiTokenCookieFactory
{
    /**
     * The configuration repository implementation.
     *
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * The encrypter implementation.
     *
     * @var \Illuminate\Contracts\Encryption\Encrypter
     */
    protected $encrypter;

    /**
     * Create an API token cookie factory instance.
     *
     * @param \Illuminate\Contracts\Config\Repository    $config
     * @param \Illuminate\Contracts\Encryption\Encrypter $encrypter
     *
     * @return void
     */
    public function __construct(Config $config, Encrypter $encrypter)
    {
        $this->config = $config;
        $this->encrypter = $encrypter;
    }

    /**
     * Create a new API token cookie.
     *
     * @param mixed  $userId
     * @param string $csrfToken
     *
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    public function make($userId, $csrfToken)
    {
        $config = $this->config->get('session');

        $expiration = Carbon::now()->addMinutes($config['lifetime']);

        return new Cookie(
            config('rinvex.oauth.cookie'),
            $this->createToken($userId, $csrfToken, $expiration),
            $expiration,
            $config['path'],
            $config['domain'],
            $config['secure'],
            true,
            false,
            $config['same_site'] ?? null
        );
    }

    /**
     * Create a new JWT token for the given user ID and CSRF token.
     *
     * @param mixed          $userId
     * @param string         $csrfToken
     * @param \Carbon\Carbon $expiration
     *
     * @return string
     */
    protected function createToken($userId, $csrfToken, Carbon $expiration)
    {
        return JWT::encode([
            'sub' => $userId,
            'csrf' => $csrfToken,
            'expiry' => $expiration->getTimestamp(),
        ], $this->encrypter->getKey(), 'HS256');
    }
}
