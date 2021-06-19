<?php

declare(strict_types=1);

namespace Rinvex\Oauth\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Rinvex\Oauth\Exceptions\MissingScopeException;

class CheckClientCredentials extends CheckCredentials
{
    /**
     * Validate token credentials.
     *
     * @param \Rinvex\Oauth\Models\AccessToken $accessToken
     *
     * @throws \Illuminate\Auth\AuthenticationException
     *
     * @return void
     */
    protected function validateCredentials($accessToken)
    {
        if (! $accessToken) {
            throw new AuthenticationException();
        }
    }

    /**
     * Validate token credentials.
     *
     * @param \Rinvex\Oauth\Models\AccessToken $accessToken
     * @param array                            $scopes
     *
     * @throws \Rinvex\Oauth\Exceptions\MissingScopeException
     *
     * @return void
     */
    protected function validateScopes($accessToken, $scopes)
    {
        foreach ($scopes as $scope) {
            if (! $accessToken->abilities->map->getRouteKey()->contains($scope)) {
                throw new MissingScopeException($scope);
            }
        }
    }
}
