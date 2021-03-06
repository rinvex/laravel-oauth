<?php

declare(strict_types=1);

namespace Rinvex\Oauth;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

class PersonalAccessTokenResult implements Arrayable, Jsonable
{
    /**
     * The access token.
     *
     * @var string
     */
    public $accessToken;

    /**
     * The token model instance.
     *
     * @var \Rinvex\Oauth\Models\AccessToken
     */
    public $token;

    /**
     * Create a new result instance.
     *
     * @param string                           $accessToken
     * @param \Rinvex\Oauth\Models\AccessToken $token
     *
     * @return void
     */
    public function __construct($accessToken, $token)
    {
        $this->token = $token;
        $this->accessToken = $accessToken;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'accessToken' => $this->accessToken,
            'token' => $this->token,
        ];
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
