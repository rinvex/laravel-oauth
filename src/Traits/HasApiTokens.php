<?php

declare(strict_types=1);

namespace Rinvex\Oauth\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Rinvex\Oauth\Factories\PersonalAccessTokenFactory;

trait HasApiTokens
{
    /**
     * The current access token for the authentication user.
     *
     * @var \Rinvex\Oauth\Models\AccessToken
     */
    protected $accessToken;

    /**
     * Get all of the user's registered OAuth clients.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function clients(): MorphMany
    {
        return $this->morphMany(config('rinvex.oauth.models.client'), 'user', 'user_type', 'user_id');
    }

    /**
     * Get all of the access tokens for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function tokens(): MorphMany
    {
        return $this->morphMany(config('rinvex.oauth.models.client'), 'user', 'user_type', 'user_id')->orderBy('created_at', 'desc');
    }

    /**
     * Get the current access token being used by the user.
     *
     * @return \Rinvex\Oauth\Models\AccessToken|null
     */
    public function token()
    {
        return $this->accessToken;
    }

    /**
     * Create a new personal access token for the user.
     *
     * @param string $name
     * @param array  $scopes
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return \Rinvex\Oauth\PersonalAccessTokenResult
     */
    public function createToken($name, array $scopes = [])
    {
        return app(PersonalAccessTokenFactory::class)->make($this, $name, $scopes);
    }

    /**
     * Set the current access token for the user.
     *
     * @param \Rinvex\Oauth\Models\AccessToken $accessToken
     *
     * @return $this
     */
    public function withAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }
}
