<?php

declare(strict_types=1);

namespace Rinvex\OAuth\Models;

use Illuminate\Database\Eloquent\Model;
use Rinvex\Support\Traits\ValidatingTrait;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuthCode extends Model
{
    use ValidatingTrait;

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
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'id',
        'user_id',
        'user_type',
        'client_id',
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
        'user_type' => 'string',
        'client_id' => 'integer',
        'scopes' => 'array',
        'is_revoked' => 'boolean',
        'expires_at' => 'datetime',
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

        $this->setTable(config('rinvex.oauth.tables.auth_codes'));
        $this->setRules([
            'id' => 'required|string|strip_tags|max:100',
            'user_id' => 'required|integer',
            'user_type' => 'required|string|strip_tags|max:150',
            'client_id' => 'required|integer|exists:'.config('rinvex.oauth.tables.clients').',id',
            'scopes' => 'nullable|array',
            'is_revoked' => 'sometimes|boolean',
            'expires_at' => 'nullable|date',
        ]);
    }

    /**
     * Get the client that owns the authentication code.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(config('rinvex.oauth.models.client'));
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
}
