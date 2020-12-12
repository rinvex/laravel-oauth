<?php

declare(strict_types=1);

namespace Rinvex\OAuth\Models;

use Illuminate\Database\Eloquent\Model;
use Rinvex\Support\Traits\ValidatingTrait;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AccessToken extends Model
{
    use ValidatingTrait;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'id',
        'user_id',
        'provider',
        'client_id',
        'name',
        'scopes',
        'is_revoked',
        'expires_at',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'id' => 'string',
        'user_id' => 'integer',
        'provider' => 'string',
        'client_id' => 'integer',
        'name' => 'string',
        'scopes' => 'array',
        'is_revoked' => 'boolean',
        'expires_at' => 'date',
    ];

    /**
     * {@inheritdoc}
     */
    protected $observables = [
        'validating',
        'validated',
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
        parent::__construct($attributes);

        $this->setTable(config('rinvex.oauth.tables.access_tokens'));
        $this->setRules([
            'id' => 'required|string|strip_tags|max:100',
            'user_id' => 'required|integer',
            'provider' => 'required|string|strip_tags|max:150',
            'client_id' => 'required|integer|exists:'.config('rinvex.oauth.tables.clients').',id',
            'name' => 'nullable|string|strip_tags|max:150',
            'scopes' => 'nullable|array',
            'is_revoked' => 'sometimes|boolean',
            'expires_at' => 'nullable|date',
        ]);
    }

    /**
     * Get the client that the token belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(config('rinvex.oauth.models.client'));
    }

    /**
     * Get the user that the token belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function user(): MorphTo
    {
        return $this->morphTo('user', 'provider', 'user_id', 'id');
    }

    /**
     * Get all of the refresh tokens that belong to the access token.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function refreshTokens()
    {
        return $this->hasMany(config('rinvex.oauth.models.refresh_token'), 'access_token_id', 'id');
    }

    /**
     * Determine if the token has a given scope.
     *
     * @param  string  $scope
     * @return bool
     */
    public function can($scope)
    {
        if (in_array('*', $this->scopes)) {
            return true;
        }

        $scopes = config('rinvex.oauth.with_inherited_scopes')
            ? $this->resolveInheritedScopes($scope)
            : [$scope];

        foreach ($scopes as $scope) {
            if (array_key_exists($scope, array_flip($this->scopes))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve all possible scopes.
     *
     * @param  string  $scope
     * @return array
     */
    protected function resolveInheritedScopes($scope)
    {
        $parts = explode(':', $scope);

        $partsCount = count($parts);

        $scopes = [];

        for ($i = 1; $i <= $partsCount; $i++) {
            $scopes[] = implode(':', array_slice($parts, 0, $i));
        }

        return $scopes;
    }

    /**
     * Determine if the token is missing a given scope.
     *
     * @param  string  $scope
     * @return bool
     */
    public function cant($scope)
    {
        return ! $this->can($scope);
    }

    /**
     * Revoke the token instance.
     *
     * @return bool
     */
    public function revoke()
    {
        $this->refreshTokens()->update(['is_revoked' => true]);
        return $this->forceFill(['is_revoked' => true])->save();
    }

    /**
     * Determine if the token is a transient JWT token.
     *
     * @return bool
     */
    public function transient()
    {
        return false;
    }
}
