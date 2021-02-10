<?php

declare(strict_types=1);

namespace Rinvex\OAuth\Repositories;

use Illuminate\Support\Str;
use Rinvex\OAuth\Bridge\AuthCode;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getNewAuthCode()
    {
        return new AuthCode();
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        $clientId = $authCodeEntity->getClient()->getIdentifier();
        [$provider, $userId] = explode(':', $authCodeEntity->getUserIdentifier());

        app('rinvex.oauth.auth_code')->create([
            'id' => $authCodeEntity->getIdentifier(),
            'user_id' => $userId,
            'provider' => $provider,
            'client_id' => app('rinvex.oauth.client')->resolveRouteBinding($clientId)->getKey(),
            'scopes' => $authCodeEntity->getScopes(),
            'is_revoked' => false,
            'expires_at' => $authCodeEntity->getExpiryDateTime(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAuthCode($codeId)
    {
        app('rinvex.oauth.auth_code')->where('id', $codeId)->update(['is_revoked' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthCodeRevoked($codeId)
    {
        return app('rinvex.oauth.auth_code')->where('id', $codeId)->where('is_revoked', true)->exists();
    }
}
