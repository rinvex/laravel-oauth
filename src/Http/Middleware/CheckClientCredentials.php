<?php

declare(strict_types=1);

namespace Rinvex\OAuth\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Rinvex\OAuth\Exceptions\MissingScopeException;

class CheckClientCredentials extends CheckCredentials
{
    /**
     * Validate token credentials.
     *
     * @param \Rinvex\OAuth\Models\AccessToken $accessToken
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
     * @param \Rinvex\OAuth\Models\AccessToken $accessToken
     * @param array                            $scopes
     *
     * @throws \Rinvex\OAuth\Exceptions\MissingScopeException
     *
     * @return void
     */
    protected function validateScopes($accessToken, $scopes)
    {
        if (in_array('*', $accessToken->scopes)) {
            return;
        }

        foreach ($scopes as $scope) {
            if ($accessToken->cant($scope)) {
                throw new MissingScopeException($scope);
            }
        }
    }
}
