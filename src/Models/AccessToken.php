<?php

declare(strict_types=1);

namespace Rinvex\OAuth\Models;

use Illuminate\Database\Eloquent\Model;
use Rinvex\Support\Traits\ValidatingTrait;
use Silber\Bouncer\Database\Concerns\Authorizable;
use Silber\Bouncer\Database\Concerns\HasAbilities;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AccessToken extends Model
{
    use Authorizable;
    use HasAbilities;
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
        'identifier',
        'user_id',
        'user_type',
        'client_id',
        'name',
        'is_revoked',
        'expires_at',
        'abilities',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'identifier' => 'string',
        'user_id' => 'integer',
        'user_type' => 'string',
        'client_id' => 'integer',
        'name' => 'string',
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
            'identifier' => 'required|string|strip_tags|max:100',
            'user_id' => 'required|integer',
            'user_type' => 'required|string|strip_tags|max:150',
            'client_id' => 'required|integer|exists:'.config('rinvex.oauth.tables.clients').',id',
            'name' => 'nullable|string|strip_tags|max:150',
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
        return $this->morphTo('user', 'user_type', 'user_id', 'id');
    }

    /**
     * Get all of the refresh tokens that belong to the access token.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function refreshTokens()
    {
        return $this->hasMany(config('rinvex.oauth.models.refresh_token'), 'access_token_identifier', 'identifier');
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
