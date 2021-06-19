<?php

declare(strict_types=1);

namespace Rinvex\Oauth;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class OAuthUserProvider implements UserProvider
{
    /**
     * The user provider instance.
     *
     * @var \Illuminate\Contracts\Auth\UserProvider
     */
    protected $provider;

    /**
     * The user provider name.
     *
     * @var string
     */
    protected $userType;

    /**
     * Create a new user provider.
     *
     * @param \Illuminate\Contracts\Auth\UserProvider $provider
     * @param string                                  $userType
     *
     * @return void
     */
    public function __construct(UserProvider $provider, $userType)
    {
        $this->provider = $provider;
        $this->userType = $userType;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveById($identifier)
    {
        return $this->provider->retrieveById($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveByToken($identifier, $rememberToken)
    {
        return $this->provider->retrieveByToken($identifier, $rememberToken);
    }

    /**
     * {@inheritdoc}
     */
    public function updateRememberToken(Authenticatable $user, $rememberToken)
    {
        $this->provider->updateRememberToken($user, $rememberToken);
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveByCredentials(array $credentials)
    {
        return $this->provider->retrieveByCredentials($credentials);
    }

    /**
     * {@inheritdoc}
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $this->provider->validateCredentials($user, $credentials);
    }

    /**
     * Get the name of the user type.
     *
     * @return string
     */
    public function getUserType()
    {
        return $this->userType;
    }
}
