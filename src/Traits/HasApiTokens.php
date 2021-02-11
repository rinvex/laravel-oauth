<?php

declare(strict_types=1);

namespace Rinvex\OAuth\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Rinvex\OAuth\Factories\PersonalAccessTokenFactory;

trait HasApiTokens
{
    /**
     * The current access token for the authentication user.
     *
     * @var \Rinvex\OAuth\Models\AccessToken
     */
    protected $accessToken;

    /**
     * Get all of the user's registered OAuth clients.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function clients(): MorphMany
    {
        return $this->morphMany(config('rinvex.oauth.models.client'), 'user', 'provider', 'user_id');
    }

    /**
     * Get all of the access tokens for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function tokens(): MorphMany
    {
        return $this->morphMany(config('rinvex.oauth.models.client'), 'user', 'provider', 'user_id')->orderBy('created_at', 'desc');
    }

    /**
     * Get the current access token being used by the user.
     *
     * @return \Rinvex\OAuth\Models\AccessToken|null
     */
    public function token()
    {
        return $this->accessToken;
    }

    /**
     * Determine if the current API token has a given scope.
     *
     * @param string $scope
     *
     * @return bool
     */
    public function tokenCan($scope)
    {
        return $this->accessToken ? $this->accessToken->can($scope) : false;
    }

    /**
     * Create a new personal access token for the user.
     *
     * @param string $name
     * @param array  $scopes
     *
     * @return \Rinvex\OAuth\PersonalAccessTokenResult
     */
    public function createToken($name, array $scopes = [])
    {
        return app(PersonalAccessTokenFactory::class)->make($this, $name, $scopes);
    }

    /**
     * Set the current access token for the user.
     *
     * @param \Rinvex\OAuth\Models\AccessToken $accessToken
     *
     * @return $this
     */
    public function withAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }
}
