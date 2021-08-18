<?php

declare(strict_types=1);

namespace Rinvex\Oauth\Models;

use Illuminate\Database\Eloquent\Model;
use Rinvex\Support\Traits\ValidatingTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefreshToken extends Model
{
    use ValidatingTrait;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'identifier',
        'access_token_identifier',
        'is_revoked',
        'expires_at',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'identifier' => 'string',
        'access_token_identifier' => 'string',
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
        $this->setTable(config('rinvex.oauth.tables.refresh_tokens'));
        $this->mergeRules([
            'identifier' => 'required|string|strip_tags|max:100',
            'access_token_identifier' => 'required|string|max:100',
            'is_revoked' => 'sometimes|boolean',
            'expires_at' => 'nullable|date',
        ]);

        parent::__construct($attributes);
    }

    /**
     * Get the access token that the refresh token belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accessToken(): BelongsTo
    {
        return $this->belongsTo(config('rinvex.oauth.models.access_token'));
    }

    /**
     * Revoke the token instance.
     *
     * @return bool
     */
    public function revoke()
    {
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

    /**
     * Determine if the token is revoked.
     *
     * @return bool
     */
    public function isRevoked()
    {
        return $this->is_revoked;
    }
}
