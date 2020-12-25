<?php

declare(strict_types=1);

namespace Rinvex\OAuth\Repositories;

use RuntimeException;
use Illuminate\Support\Str;
use Rinvex\OAuth\Bridge\User;
use Illuminate\Contracts\Hashing\Hasher;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    /**
     * The hasher implementation.
     *
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    protected $hasher;

    /**
     * Create a new repository instance.
     *
     * @param \Illuminate\Contracts\Hashing\Hasher $hasher
     *
     * @return void
     */
    public function __construct(Hasher $hasher)
    {
        $this->hasher = $hasher;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserEntityByUserCredentials($username, $password, $grantType, ClientEntityInterface $clientEntity)
    {
        if (is_null($model = config('auth.providers.'.Str::plural($clientEntity->provider).'.model'))) {
            throw new RuntimeException('Unable to determine authentication model from configuration.');
        }

        if (method_exists($model, 'findAndValidateForOAuth')) {
            $user = (new $model())->findAndValidateForOAuth($username, $password);

            if (! $user) {
                return;
            }

            return new User($user->getAuthIdentifier());
        }

        if (method_exists($model, 'findForOAuth')) {
            $user = (new $model())->findForOAuth($username);
        } else {
            $user = (new $model())->where('email', $username)->first();
        }

        if (! $user) {
            return;
        } elseif (method_exists($user, 'validateForOAuthPasswordGrant')) {
            if (! $user->validateForOAuthPasswordGrant($password)) {
                return;
            }
        } elseif (! $this->hasher->check($password, $user->getAuthPassword())) {
            return;
        }

        return new User($user->getAuthIdentifier());
    }
}
