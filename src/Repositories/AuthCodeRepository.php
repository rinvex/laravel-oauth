<?php

declare(strict_types=1);

namespace Rinvex\Oauth\Repositories;

use Rinvex\Oauth\Bridge\AuthCode;
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
        [$userType, $userId] = explode(':', $authCodeEntity->getUserIdentifier());

        $userId = method_exists($user = app('cortex.auth.'.$userType), 'unhashId') ? $user->unhashId($userId) : $userId;
        $clientId = method_exists($client = app('rinvex.oauth.client'), 'unhashId') ? $client->unhashId($clientId) : $clientId;

        app('rinvex.oauth.auth_code')->create([
            'identifier' => $authCodeEntity->getIdentifier(),
            'user_id' => $userId,
            'user_type' => $userType,
            'client_id' => $clientId,
            'is_revoked' => false,
            'expires_at' => $authCodeEntity->getExpiryDateTime(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAuthCode($codeId)
    {
        app('rinvex.oauth.auth_code')->where('identifier', $codeId)->update(['is_revoked' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthCodeRevoked($codeId)
    {
        return app('rinvex.oauth.auth_code')->where('identifier', $codeId)->where('is_revoked', true)->exists();
    }
}
