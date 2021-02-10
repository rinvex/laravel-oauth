<?php

declare(strict_types=1);

namespace Rinvex\OAuth\Factories;

use RuntimeException;
use Nyholm\Psr7\Response;
use Illuminate\Support\Str;
use Nyholm\Psr7\ServerRequest;
use Rinvex\OAuth\Models\Client;
use Lcobucci\JWT\Parser as JwtParser;
use Illuminate\Database\Eloquent\Model;
use Rinvex\OAuth\PersonalAccessTokenResult;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\AuthorizationServer;

class PersonalAccessTokenFactory
{
    /**
     * The authorization server instance.
     *
     * @var \League\OAuth2\Server\AuthorizationServer
     */
    protected $server;

    /**
     * The JWT token parser instance.
     *
     * @var \Lcobucci\JWT\Parser
     */
    protected $jwt;

    /**
     * Create a new personal access token factory instance.
     *
     * @param \League\OAuth2\Server\AuthorizationServer $server
     * @param \Lcobucci\JWT\Parser                      $jwt
     *
     * @return void
     */
    public function __construct(AuthorizationServer $server, JwtParser $jwt)
    {
        $this->jwt = $jwt;
        $this->server = $server;
    }

    /**
     * Create a new personal access token.
     *
     * @param \Illuminate\Database\Eloquent\Model $user
     * @param string                              $name
     * @param array                               $scopes
     *
     * @return \Rinvex\OAuth\PersonalAccessTokenResult
     */
    public function make(Model $user, $name, array $scopes = [])
    {
        $response = $this->dispatchRequestToAuthorizationServer(
            $this->createRequest($this->personalAccessClient(), $user, $scopes)
        );

        $accessToken = tap($this->findAccessToken($response), function ($accessToken) use ($user, $name) {
            $accessToken->forceFill([
                'user_id' => $user->getAuthIdentifier(),
                'provider' => Str::plural($user->getMorphClass()),
                'name' => $name,
            ])->save();
        });

        return new PersonalAccessTokenResult(
            $response['access_token'],
            $accessToken
        );
    }

    /**
     * Get the personal access token client for the application.
     *
     * @throws \RuntimeException
     *
     * @return \Rinvex\OAuth\Models\Client
     */
    public function personalAccessClient()
    {
        if ($personalAccessClientId = config('rinvex.oauth.personal_access_client.id')) {
            return app('rinvex.oauth.client')->resolveRouteBinding($personalAccessClientId);
        }

        $client = app('rinvex.oauth.client')->where('grant_type', 'personal_access');

        if (! $client->exists()) {
            throw new RuntimeException('Personal access client not found. Please create one.');
        }

        return $client->orderBy('id', 'desc')->first();
    }

    /**
     * Create a request instance for the given client.
     *
     * @param \Rinvex\OAuth\Models\Client         $client
     * @param \Illuminate\Database\Eloquent\Model $user
     * @param array                               $scopes
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    protected function createRequest(Client $client, Model $user, array $scopes)
    {
        $personalAccessClientSecret = config('rinvex.oauth.personal_access_client.secret');

        return (new ServerRequest('POST', 'not-important'))->withParsedBody([
            'grant_type' => 'personal_access',
            'client_id' => $client->id,
            'client_secret' => $personalAccessClientSecret,
            'user_id' => Str::plural($user->getMorphClass()).':'.$user->getAuthIdentifier(),
            'scope' => implode(' ', $scopes),
        ]);
    }

    /**
     * Dispatch the given request to the authorization server.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    protected function dispatchRequestToAuthorizationServer(ServerRequestInterface $request)
    {
        return json_decode($this->server->respondToAccessTokenRequest(
            $request,
            new Response()
        )->getBody()->__toString(), true);
    }

    /**
     * Get the access token instance for the parsed response.
     *
     * @param array $response
     *
     * @return \Rinvex\OAuth\Models\AccessToken
     */
    protected function findAccessToken(array $response)
    {
        return app('rinvex.oauth.access_token')->where('id', $this->jwt->parse($response['access_token'])->getClaim('jti'));
    }
}
