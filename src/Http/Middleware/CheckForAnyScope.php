<?php

declare(strict_types=1);

namespace Rinvex\Oauth\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Rinvex\Oauth\Exceptions\MissingScopeException;

class CheckForAnyScope
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param mixed                    ...$scopes
     *
     * @throws \Illuminate\Auth\AuthenticationException|\Rinvex\Oauth\Exceptions\MissingScopeException
     *
     * @return \Illuminate\Http\Response
     */
    public function handle($request, $next, ...$scopes)
    {
        if (! $request->user() || ! $request->user()->token()) {
            throw new AuthenticationException();
        }

        foreach ($scopes as $scope) {
            if ($request->user()->token()->abilities->map->getRouteKey()->contains($scope)) {
                return $next($request);
            }
        }

        throw new MissingScopeException($scopes);
    }
}
