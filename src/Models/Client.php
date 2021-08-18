<?php

declare(strict_types=1);

namespace Rinvex\Oauth\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Rinvex\Support\Traits\HasTranslations;
use Rinvex\Support\Traits\ValidatingTrait;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Client extends Model
{
    use HasTranslations;
    use ValidatingTrait;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'secret',
    ];

    /**
     * The temporary plain-text client secret.
     *
     * @var string|null
     */
    protected $plainSecret;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'user_id',
        'user_type',
        'name',
        'secret',
        'redirect',
        'grant_type',
        'is_revoked',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'user_id' => 'integer',
        'user_type' => 'string',
        'name' => 'string',
        'secret' => 'string',
        'redirect' => 'string',
        'grant_type' => 'string',
        'is_revoked' => 'boolean',
    ];

    /**
     * {@inheritdoc}
     */
    protected $observables = [
        'validating',
        'validated',
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    public $translatable = [
        'name',
    ];

    /**
     * The default rules that the model will validate against.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Whether the model should throw a
     * ValidationException if it fails validation.
     *
     * @var bool
     */
    protected $throwValidationExceptions = true;

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->setTable(config('rinvex.oauth.tables.clients'));
        $this->mergeRules([
            'user_id' => 'required|integer',
            'user_type' => 'required|string|strip_tags|max:150',
            'name' => 'required|string|strip_tags|max:150',
            'secret' => 'nullable|string|max:100',
            'redirect' => 'required|url|max:1500',
            'grant_type' => 'required|string|strip_tags|max:100',
            'is_revoked' => 'sometimes|boolean',
        ]);

        parent::__construct($attributes);
    }

    /**
     * Get the user that the client belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function user(): MorphTo
    {
        return $this->morphTo('user', 'user_type', 'user_id', 'id');
    }

    /**
     * Get all of the authentication codes for the client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function authCodes(): HasMany
    {
        return $this->hasMany(config('rinvex.oauth.models.auth_code'), 'client_id', 'id');
    }

    /**
     * Get all of the tokens that belong to the client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accessTokens(): HasMany
    {
        return $this->hasMany(config('rinvex.oauth.models.access_token'), 'client_id', 'id');
    }

    /**
     * Get a valid token instance for the given user and client.
     *
     * @param \Illuminate\Database\Eloquent\Model $user
     *
     * @return \Rinvex\Oauth\Models\AccessToken|null
     */
    public function getValidToken($user)
    {
        return $this->accessTokens()
                    ->where('user_id', $user->getAuthIdentifier())
                    ->where('user_type', $user->getMorphClass())
                    ->where('is_revoked', false)
                    ->where('expires_at', '>', Carbon::now())
                    ->first();
    }

    /**
     * Find a valid token for the given user and client.
     *
     * @param \Illuminate\Database\Eloquent\Model $user
     *
     * @return \Rinvex\Oauth\Models\AccessToken|null
     */
    public function findValidToken($user)
    {
        return $this->accessTokens()
                    ->where('user_id', $user->getAuthIdentifier())
                    ->where('user_type', $user->getMorphClass())
                    ->where('is_revoked', false)
                    ->where('expires_at', '>', Carbon::now())
                    ->latest('expires_at')
                    ->first();
    }

    /**
     * The temporary non-hashed client secret.
     *
     * This is only available once during the request that created the client.
     *
     * @return string|null
     */
    public function getPlainSecretAttribute()
    {
        return $this->plainSecret;
    }

    /**
     * Set the value of the secret attribute.
     *
     * @param string|null $value
     *
     * @return void
     */
    public function setSecretAttribute($value)
    {
        $this->plainSecret = $value;

        if (is_null($value)) {
            $this->attributes['secret'] = $value;

            return;
        }

        $this->attributes['secret'] = password_hash($value, PASSWORD_BCRYPT);
    }

    /**
     * Determine if the client is a "first party" client.
     *
     * @return bool
     */
    public function firstParty()
    {
        return in_array($this->grant_type, ['personal_access', 'password']);
    }

    /**
     * Determine if the client should skip the authorization prompt.
     *
     * @return bool
     */
    public function skipsAuthorization()
    {
        return false;
    }

    /**
     * Determine if the client is a confidential client.
     *
     * @return bool
     */
    public function isConfidential()
    {
        return ! empty($this->secret);
    }

    /**
     * Revoke current client and its tokens.
     *
     * @return void
     */
    public function revoke()
    {
        $this->accessTokens()->update(['is_revoked' => true]);
        $this->forceFill(['is_revoked' => true])->save();
    }
}
