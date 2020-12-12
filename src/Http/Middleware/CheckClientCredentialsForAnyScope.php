<?php

declare(strict_types=1);

namespace Rinvex\OAuth\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Rinvex\OAuth\Exceptions\MissingScopeException;

class CheckClientCredentialsForAnyScope extends CheckCredentials
{
    /**
     * Validate token credentials.
     *
     * @param  \Rinvex\OAuth\Models\AccessToken  $accessToken
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function validateCredentials($accessToken)
    {
        if (! $accessToken) {
            throw new AuthenticationException;
        }
    }

    /**
     * Validate token credentials.
     *
     * @param  \Rinvex\OAuth\Models\AccessToken  $accessToken
     * @param  array  $scopes
     * @return void
     *
     * @throws \Rinvex\OAuth\Exceptions\MissingScopeException
     */
    protected function validateScopes($accessToken, $scopes)
    {
        if (in_array('*', $accessToken->scopes)) {
            return;
        }

        foreach ($scopes as $scope) {
            if ($accessToken->can($scope)) {
                return;
            }
        }

        throw new MissingScopeException($scopes);
    }
}
