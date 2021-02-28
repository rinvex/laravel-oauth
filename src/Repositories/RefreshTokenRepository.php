<?php

declare(strict_types=1);

namespace Rinvex\OAuth\Repositories;

use Rinvex\OAuth\Bridge\RefreshToken;
use Illuminate\Contracts\Events\Dispatcher;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
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
    public function getNewRefreshToken()
    {
        return new RefreshToken();
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        app('rinvex.oauth.refresh_token')->create([
            'identifier' => $refreshTokenEntity->getIdentifier(),
            'access_token_identifier' => $refreshTokenEntity->getAccessToken()->getIdentifier(),
            'is_revoked' => false,
            'expires_at' => $refreshTokenEntity->getExpiryDateTime(),
        ]);
    }

    /**
     * Revokes the refresh token.
     *
     * @param string $identifier
     *
     * @return mixed
     */
    public function revokeRefreshToken($identifier)
    {
        return app('rinvex.oauth.refresh_token')->where('identifier', $identifier)->update(['is_revoked' => true]);
    }

    /**
     * Checks if the refresh token has been revoked.
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function isRefreshTokenRevoked($identifier)
    {
        if ($refreshToken = app('rinvex.oauth.refresh_token')->where('identifier', $identifier)->first()) {
            return $refreshToken->is_revoked;
        }

        return true;
    }
}
