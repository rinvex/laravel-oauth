<?php

declare(strict_types=1);

namespace Rinvex\OAuth\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Rinvex\OAuth\Exceptions\MissingScopeException;

class CheckScopes
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param mixed                    ...$scopes
     *
     * @throws \Illuminate\Auth\AuthenticationException|\Rinvex\OAuth\Exceptions\MissingScopeException
     *
     * @return \Illuminate\Http\Response
     */
    public function handle($request, $next, ...$scopes)
    {
        if (! $request->user() || ! $request->user()->token()) {
            throw new AuthenticationException();
        }

        foreach ($scopes as $scope) {
            if (! $request->user()->tokenCan($scope)) {
                throw new MissingScopeException($scope);
            }
        }

        return $next($request);
    }
}
