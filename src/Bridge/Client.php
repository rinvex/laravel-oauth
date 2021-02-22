<?php

declare(strict_types=1);

namespace Rinvex\OAuth\Bridge;

use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class Client implements ClientEntityInterface
{
    use ClientTrait;

    /**
     * The client identifier.
     *
     * @var string
     */
    protected $identifier;

    /**
     * The client's user type.
     *
     * @var string
     */
    public $userType;

    /**
     * Create a new client instance.
     *
     * @param string      $identifier
     * @param string      $name
     * @param string      $redirectUri
     * @param string      $userType
     * @param bool        $isConfidential
     *
     * @return void
     */
    public function __construct($identifier, $name, $redirectUri, $userType, $isConfidential = false)
    {
        $this->setIdentifier((string) $identifier);

        $this->name = $name;
        $this->userType = $userType;
        $this->isConfidential = $isConfidential;
        $this->redirectUri = explode(',', $redirectUri);
    }

    /**
     * Get the client's identifier.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return (string) $this->identifier;
    }

    /**
     * Set the client's identifier.
     *
     * @param string $identifier
     *
     * @return void
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }
}
