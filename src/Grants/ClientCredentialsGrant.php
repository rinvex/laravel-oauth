<?php

declare(strict_types=1);

namespace Rinvex\OAuth\Grants;

use DateInterval;
use League\OAuth2\Server\RequestEvent;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;

class ClientCredentialsGrant extends AbstractGrant
{
    /**
     * {@inheritdoc}
     */
    public function respondToAccessTokenRequest(ServerRequestInterface $request, ResponseTypeInterface $responseType, DateInterval $accessTokenTTL)
    {
        [$clientId] = $this->getClientCredentials($request);

        $client = $this->getClientEntityOrFail($clientId, $request);

        if (!$client->isConfidential()) {
            $this->getEmitter()->emit(new RequestEvent(RequestEvent::CLIENT_AUTHENTICATION_FAILED, $request));

            throw OAuthServerException::invalidClient($request);
        }

        // Validate request
        $this->validateClient($request);

        $scopes = $this->validateScopes($this->getRequestParameter('scope', $request, $this->defaultScope));

        // Finalize the requested scopes
        $finalizedScopes = $this->scopeRepository->finalizeScopes($scopes, $this->getIdentifier(), $client);

        // Issue and persist access token
        $accessToken = $this->issueAccessToken($accessTokenTTL, $client, $this->getRequestParameter('user_id', $request), $finalizedScopes);

        // Send event to emitter
        $this->getEmitter()->emit(new RequestEvent(RequestEvent::ACCESS_TOKEN_ISSUED, $request));

        // Inject access token into response type
        $responseType->setAccessToken($accessToken);

        return $responseType;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'client_credentials';
    }
}
