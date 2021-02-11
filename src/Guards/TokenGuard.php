<?php

declare(strict_types=1);

namespace Rinvex\OAuth\Guards;

use Exception;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Rinvex\OAuth\TransientToken;
use Rinvex\OAuth\OAuthUserProvider;
use Nyholm\Psr7\Factory\Psr17Factory;
use Illuminate\Cookie\CookieValuePrefix;
use League\OAuth2\Server\ResourceServer;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Cookie\Middleware\EncryptCookies;
use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

class TokenGuard
{
    /**
     * The resource server instance.
     *
     * @var \League\OAuth2\Server\ResourceServer
     */
    protected $server;

    /**
     * The user provider implementation.
     *
     * @var \Rinvex\OAuth\OAuthUserProvider
     */
    protected $provider;

    /**
     * The encrypter implementation.
     *
     * @var \Illuminate\Contracts\Encryption\Encrypter
     */
    protected $encrypter;

    /**
     * Create a new token guard instance.
     *
     * @param \League\OAuth2\Server\ResourceServer       $server
     * @param \Rinvex\OAuth\OAuthUserProvider            $provider
     * @param \Illuminate\Contracts\Encryption\Encrypter $encrypter
     *
     * @return void
     */
    public function __construct(ResourceServer $server, OAuthUserProvider $provider, Encrypter $encrypter)
    {
        $this->server = $server;
        $this->provider = $provider;
        $this->encrypter = $encrypter;
    }

    /**
     * Determine if the requested provider matches the client's provider.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function hasValidProvider(Request $request)
    {
        $client = $this->client($request);

        if ($client && ! $client->provider) {
            return true;
        }

        return $client && $client->provider === $this->provider->getProviderName();
    }

    /**
     * Get the user for the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function user(Request $request)
    {
        if ($request->bearerToken()) {
            return $this->authenticateViaBearerToken($request);
        } elseif ($request->cookie(config('rinvex.oauth.cookie'))) {
            return $this->authenticateViaCookie($request);
        }
    }

    /**
     * Get the client for the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function client(Request $request)
    {
        if ($request->bearerToken()) {
            if (! $psr = $this->getPsrRequestViaBearerToken($request)) {
                return;
            }

            $client = app('rinvex.oauth.client')->resolveRouteBinding($psr->getAttribute('oauth_client_id'));

            return $client && ! $client->is_revoked ? $client : null;
        } elseif ($request->cookie(config('rinvex.oauth.cookie'))) {
            if ($token = $this->getTokenViaCookie($request)) {
                $client = app('rinvex.oauth.client')->resolveRouteBinding($token['aud']);

                return $client && ! $client->is_revoked ? $client : null;
            }
        }
    }

    /**
     * Authenticate the incoming request via the Bearer token.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    protected function authenticateViaBearerToken($request)
    {
        if (! $psr = $this->getPsrRequestViaBearerToken($request)) {
            return;
        }

        if (! $this->hasValidProvider($request)) {
            return;
        }

        // If the access token is valid we will retrieve the user according to the user ID
        // associated with the token. We will use the provider implementation which may
        // be used to retrieve users from Eloquent. Next, we'll be ready to continue.
        [, $userId] = explode(':', $psr->getAttribute('oauth_user_id'));
        $user = $this->provider->retrieveById($userId ?: null);

        if (! $user) {
            return;
        }

        // Next, we will assign a token instance to this user which the developers may use
        // to determine if the token has a given scope, etc. This will be useful during
        // authorization such as within the developer's Laravel model policy classes.
        $token = app('rinvex.oauth.access_token')->where('id', $psr->getAttribute('oauth_access_token_id'))->first();
        $clientId = $psr->getAttribute('oauth_client_id');

        // Finally, we will verify if the client that issued this token is still valid and
        // its tokens may still be used. If not, we will bail out since we don't want a
        // user to be able to send access tokens for deleted or revoked applications.
        $client = app('rinvex.oauth.client')->resolveRouteBinding($clientId);

        if (is_null($client) || $client->is_revoked) {
            return;
        }

        return $token ? $user->withAccessToken($token) : null;
    }

    /**
     * Authenticate and get the incoming PSR-7 request via the Bearer token.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    protected function getPsrRequestViaBearerToken($request)
    {
        // First, we will convert the Symfony request to a PSR-7 implementation which will
        // be compatible with the base OAuth2 library. The Symfony bridge can perform a
        // conversion for us to a new Nyholm implementation of this PSR-7 request.
        $psr = (new PsrHttpFactory(
            new Psr17Factory(),
            new Psr17Factory(),
            new Psr17Factory(),
            new Psr17Factory()
        ))->createRequest($request);

        try {
            return $this->server->validateAuthenticatedRequest($psr);
        } catch (OAuthServerException $e) {
            $request->headers->set('Authorization', '', true);

            app(ExceptionHandler::class)->report($e);
        }
    }

    /**
     * Authenticate the incoming request via the token cookie.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    protected function authenticateViaCookie($request)
    {
        if (! $token = $this->getTokenViaCookie($request)) {
            return;
        }

        // If this user exists, we will return this user and attach a "transient" token to
        // the user model. The transient token assumes it has all scopes since the user
        // is physically logged into the application via the application's interface.
        if ($user = $this->provider->retrieveById($token['sub'])) {
            return $user->withAccessToken(new TransientToken());
        }
    }

    /**
     * Get the token cookie via the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    protected function getTokenViaCookie($request)
    {
        // If we need to retrieve the token from the cookie, it'll be encrypted so we must
        // first decrypt the cookie and then attempt to find the token value within the
        // database. If we can't decrypt the value we'll bail out with a null return.
        try {
            $token = $this->decodeJwtTokenCookie($request);
        } catch (Exception $e) {
            return;
        }

        // We will compare the CSRF token in the decoded API token against the CSRF header
        // sent with the request. If they don't match then this request isn't sent from
        // a valid source and we won't authenticate the request for further handling.
        if (! config('rinvex.oauth.ignore_csrf_token') && (! $this->validCsrf($token, $request) ||
            time() >= $token['expiry'])) {
            return;
        }

        return $token;
    }

    /**
     * Decode and decrypt the JWT token cookie.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    protected function decodeJwtTokenCookie($request)
    {
        return (array) JWT::decode(
            CookieValuePrefix::remove($this->encrypter->decrypt($request->cookie(config('rinvex.oauth.cookie')), config('rinvex.oauth.unserializes_cookies'))),
            $this->encrypter->getKey(),
            ['HS256']
        );
    }

    /**
     * Determine if the CSRF / header are valid and match.
     *
     * @param array                    $token
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function validCsrf($token, $request)
    {
        return isset($token['csrf']) && hash_equals(
            $token['csrf'],
            (string) $this->getTokenFromRequest($request)
        );
    }

    /**
     * Get the CSRF token from the request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     */
    protected function getTokenFromRequest($request)
    {
        $token = $request->header('X-CSRF-TOKEN');

        if (! $token && $header = $request->header('X-XSRF-TOKEN')) {
            $token = CookieValuePrefix::remove($this->encrypter->decrypt($header, static::serialized()));
        }

        return $token;
    }

    /**
     * Determine if the cookie contents should be serialized.
     *
     * @return bool
     */
    public static function serialized()
    {
        return EncryptCookies::serialized('XSRF-TOKEN');
    }
}
