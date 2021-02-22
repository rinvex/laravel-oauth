<?php

declare(strict_types=1);

namespace Rinvex\OAuth\Repositories;

use DateTime;
use Rinvex\OAuth\Bridge\AccessToken;
use Illuminate\Contracts\Events\Dispatcher;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * Create a new repository instance.
     *
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     *
     * @return void
     */
    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        return new AccessToken($userIdentifier, $scopes, $clientEntity);
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        $clientId = $accessTokenEntity->getClient()->getIdentifier();
        [$userType, $userId] = explode(':', $accessTokenEntity->getUserIdentifier());

        $userId = method_exists($user = app('cortex.auth.'.$userType), 'unhashId') ? $user->unhashId($userId) : $userId;
        $clientId = method_exists($client = app('rinvex.oauth.client'), 'unhashId') ? $client->unhashId($clientId) : $clientId;
        app('rinvex.oauth.access_token')->create([
            'id' => $accessTokenEntity->getIdentifier(),
            'user_id' => $userId,
            'scopes' => $accessTokenEntity->getScopes(),
            'user_type' => $userType,
            'client_id' => $clientId,
            'is_revoked' => false,
            'created_at' => new DateTime(),
            'updated_at' => new DateTime(),
            'expires_at' => $accessTokenEntity->getExpiryDateTime(),
        ]);
    }

    /**
     * Revoke an access token.
     *
     * @param string $accessTokenId
     *
     * @return mixed
     */
    public function revokeAccessToken($accessTokenId)
    {
        app('rinvex.oauth.access_token')->where('id', $accessTokenId)->update(['is_revoked' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function isAccessTokenRevoked($accessTokenId)
    {
        if ($accessToken = app('rinvex.oauth.access_token')->where('id', $accessTokenId)->first()) {
            return $accessToken->is_revoked;
        }

        return true;
    }
}
