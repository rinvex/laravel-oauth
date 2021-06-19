<?php

declare(strict_types=1);

namespace Rinvex\Oauth\Bridge;

use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\AuthCodeTrait;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

class AuthCode implements AuthCodeEntityInterface
{
    use EntityTrait;
    use AuthCodeTrait;
    use TokenEntityTrait;
}
