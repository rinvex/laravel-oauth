<?php

declare(strict_types=1);

namespace Rinvex\OAuth\Grants;

use DateInterval;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;

class PersonalAccessGrant extends AbstractGrant
{
    /**
     * Respond to an access token request.
     *
     * @param ServerRequestInterface $request
     * @param ResponseTypeInterface  $responseType
     * @param DateInterval           $accessTokenTTL
     *
     * @throws OAuthServerException
     *
     * @return ResponseTypeInterface
     */
    public function respondToAccessTokenRequest(ServerRequestInterface $request, ResponseTypeInterface $responseType, DateInterval $accessTokenTTL)
    {
        // Validate request
        $this->validateUser($request);
        $client = $this->validateClient($request);
        $scopes = $this->validateScopes($this->getRequestParameter('scope', $request));

        // Finalize the requested scopes
        $scopes = $this->scopeRepository->finalizeScopes($scopes, $this->getIdentifier(), $client);

        // Issue and persist access token
        $accessToken = $this->issueAccessToken(
            $accessTokenTTL,
            $client,
            $this->getRequestParameter('user_id', $request),
            $scopes
        );

        // Inject access token into response type
        $responseType->setAccessToken($accessToken);

        return $responseType;
    }

    /**
     * Validate the authorization code user.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @throws \League\OAuth2\Server\Exception\OAuthServerException
     */
    protected function validateUser(ServerRequestInterface $request)
    {
        [$userType, $userId] = explode(':', $this->getRequestParameter('user_id', $request));

        if ($userType !== request()->user()->getMorphClass() || $userId !== request()->user()->getRouteKey()) {
            throw OAuthServerException::invalidRequest('user_id', 'This action is not authorized to this user');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'personal_access';
    }
}
