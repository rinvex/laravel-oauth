<?php

declare(strict_types=1);

namespace Rinvex\Oauth\Models;

use Illuminate\Database\Eloquent\Model;
use Rinvex\Support\Traits\ValidatingTrait;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuthCode extends Model
{
    use HasFactory;
    use ValidatingTrait;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'identifier',
        'user_id',
        'user_type',
        'client_id',
        'is_revoked',
        'expires_at',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'identifier' => 'string',
        'user_id' => 'integer',
        'user_type' => 'string',
        'client_id' => 'integer',
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
        $this->setTable(config('rinvex.oauth.tables.auth_codes'));
        $this->mergeRules([
            'identifier' => 'required|string|strip_tags|max:100',
            'user_id' => 'required|integer',
            'user_type' => 'required|string|strip_tags|max:150',
            'client_id' => 'required|integer|exists:'.config('rinvex.oauth.tables.clients').',id',
            'is_revoked' => 'sometimes|boolean',
            'expires_at' => 'nullable|date',
        ]);

        parent::__construct($attributes);
    }

    /**
     * Get the client that owns the authentication code.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(config('rinvex.oauth.models.client'), 'client_id', 'id', 'client');
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
