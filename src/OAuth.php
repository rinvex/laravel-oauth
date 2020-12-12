<?php

declare(strict_types=1);

namespace Rinvex\OAuth;

use Mockery;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Message\ServerRequestInterface;

class OAuth
{
    /**
     * Get all of the defined scope IDs.
     *
     * @return array
     */
    public static function scopeIds()
    {
        return static::scopes()->pluck('id')->values()->all();
    }

    /**
     * Determine if the given scope has been defined.
     *
     * @param  string  $id
     * @return bool
     */
    public static function hasScope($id)
    {
        return $id === '*' || array_key_exists($id, config('rinvex.oauth.scopes'));
    }

    /**
     * Get all of the scopes defined for the application.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function scopes()
    {
        return collect(config('rinvex.oauth.scopes'))->map(function ($description, $id) {
            return new Scope($id, $description);
        })->values();
    }

    /**
     * Get all of the scopes matching the given IDs.
     *
     * @param  array  $ids
     * @return array
     */
    public static function scopesFor(array $ids)
    {
        return collect($ids)->map(function ($id) {
            if (isset(config('rinvex.oauth.scopes')[$id])) {
                return new Scope($id, config('rinvex.oauth.scopes')[$id]);
            }
        })->filter()->values()->all();
    }

    /**
     * Set the current user for the application with the given scopes.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|\Rinvex\OAuth\Traits\HasApiTokens  $user
     * @param  array  $scopes
     * @param  string  $guard
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public static function actingAs($user, $scopes = [], $guard = 'api')
    {
        $accessToken = Mockery::mock(config('rinvex.oauth.models.access_token'))->shouldIgnoreMissing(false);

        foreach ($scopes as $scope) {
            $accessToken->shouldReceive('can')->with($scope)->andReturn(true);
        }

        $user->withAccessToken($accessToken);

        if (isset($user->wasRecentlyCreated) && $user->wasRecentlyCreated) {
            $user->wasRecentlyCreated = false;
        }

        app('auth')->guard($guard)->setUser($user);

        app('auth')->shouldUse($guard);

        return $user;
    }

    /**
     * Set the current client for the application with the given scopes.
     *
     * @param \Rinvex\OAuth\Models\Client $client
     * @param array $scopes
     * @return \Rinvex\OAuth\Models\Client
     */
    public static function actingAsClient($client, $scopes = [])
    {
        $accessToken = app('rinvex.oauth.access_token');

        $accessToken->client_id = $client->id;
        $accessToken->setRelation('client', $client);

        $accessToken->scopes = $scopes;

        $mock = Mockery::mock(ResourceServer::class);
        $mock->shouldReceive('validateAuthenticatedRequest')
            ->andReturnUsing(function (ServerRequestInterface $request) use ($accessToken) {
                return $request->withAttribute('oauth_client_id', $accessToken->client->id)
                    ->withAttribute('oauth_access_token_id', $accessToken->id)
                    ->withAttribute('oauth_scopes', $accessToken->scopes);
            });

        app()->instance(ResourceServer::class, $mock);

        return $client;
    }

    /**
     * The location of the encryption keys.
     *
     * @param  string  $file
     * @return string
     */
    public static function keyPath($file)
    {
        $file = ltrim($file, '/\\');

        return config('rinvex.oauth.key_path')
            ? rtrim(config('rinvex.oauth.key_path'), '/\\').DIRECTORY_SEPARATOR.$file
            : storage_path($file);
    }
}
