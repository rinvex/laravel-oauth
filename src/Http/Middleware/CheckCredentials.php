<?php

declare(strict_types=1);

namespace Rinvex\OAuth\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

abstract class CheckCredentials
{
    /**
     * The Resource Server instance.
     *
     * @var \League\OAuth2\Server\ResourceServer
     */
    protected $server;

    /**
     * Create a new middleware instance.
     *
     * @param  \League\OAuth2\Server\ResourceServer  $server
     * @return void
     */
    public function __construct(ResourceServer $server)
    {
        $this->server = $server;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$scopes
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$scopes)
    {
        $psr = (new PsrHttpFactory(
            new Psr17Factory,
            new Psr17Factory,
            new Psr17Factory,
            new Psr17Factory
        ))->createRequest($request);

        try {
            $psr = $this->server->validateAuthenticatedRequest($psr);
        } catch (OAuthServerException $e) {
            throw new AuthenticationException;
        }

        $this->validate($psr, $scopes);

        return $next($request);
    }

    /**
     * Validate the scopes and token on the incoming request.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $psr
     * @param  array  $scopes
     * @return void
     *
     * @throws \Rinvex\OAuth\Exceptions\MissingScopeException|\Illuminate\Auth\AuthenticationException
     */
    protected function validate($psr, $scopes)
    {
        $accessToken = app('rinvex.oauth.access_token')->where('id', $psr->getAttribute('oauth_access_token_id'))->first();

        $this->validateCredentials($accessToken);

        $this->validateScopes($accessToken, $scopes);
    }

    /**
     * Validate token credentials.
     *
     * @param  \Rinvex\OAuth\Models\AccessToken  $accessToken
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    abstract protected function validateCredentials($accessToken);

    /**
     * Validate token scopes.
     *
     * @param  \Rinvex\OAuth\Models\AccessToken  $accessToken
     * @param  array  $scopes
     * @return void
     *
     * @throws \Rinvex\OAuth\Exceptions\MissingScopeException
     */
    abstract protected function validateScopes($accessToken, $scopes);
}
