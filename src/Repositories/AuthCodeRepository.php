<?php

declare(strict_types=1);

namespace Rinvex\OAuth\Repositories;

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
        $user = explode(':', $authCodeEntity->getUserIdentifier());

        app('rinvex.oauth.auth_code')->create([
            'id' => $authCodeEntity->getIdentifier(),
            'user_id' => $user[1],
            'provider' => $user[0],
            'client_id' => $authCodeEntity->getClient()->getIdentifier(),
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
