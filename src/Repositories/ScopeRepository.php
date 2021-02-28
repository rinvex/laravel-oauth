<?php

declare(strict_types=1);

namespace Rinvex\OAuth\Repositories;

use Rinvex\OAuth\Bridge\Scope;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class ScopeRepository implements ScopeRepositoryInterface
{
    /**
     * Return information about a scope.
     *
     * @param string $identifier The scope identifier
     *
     * @return \League\OAuth2\Server\Entities\ScopeEntityInterface|null
     */
    public function getScopeEntityByIdentifier($identifier)
    {
        if (app('cortex.auth.ability')->resolveRouteBinding($identifier)) {
            return new Scope($identifier);
        }
    }

    /**
     * Given a client, grant type and optional user identifier validate the set of scopes requested are valid and optionally
     * append additional scopes or remove requested scopes.
     *
     * @param \League\OAuth2\Server\Entities\ScopeEntityInterface[] $scopes
     * @param string                                                $grantType
     * @param ClientEntityInterface                                 $clientEntity
     * @param null|string                                           $userIdentifier
     *
     * @return \League\OAuth2\Server\Entities\ScopeEntityInterface[]
     */
    public function finalizeScopes(array $scopes, $grantType, ClientEntityInterface $clientEntity, $userIdentifier = null)
    {
        $abilityIds = app('cortex.auth.ability')->all()->pluck('id');

        return collect($scopes)->filter(function ($scope) use ($abilityIds) {
            return $abilityIds->contains(app('cortex.auth.ability')->unhashId($scope->getIdentifier()));
        })->all();
    }
}
