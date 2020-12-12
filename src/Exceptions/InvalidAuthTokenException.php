<?php

declare(strict_types=1);

namespace Rinvex\OAuth\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;

class InvalidAuthTokenException extends AuthorizationException
{
    /**
     * Create a new InvalidAuthTokenException for different auth tokens.
     *
     * @return static
     */
    public static function different()
    {
        return new static('The provided auth token for the request is different from the session auth token.');
    }
}
