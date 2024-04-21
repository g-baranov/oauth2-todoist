<?php

namespace GBaranov\OAuth2Todoist\Provider;

use League\OAuth2\Client\Exception\HostedDomainException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

/**
 * @link https://developer.todoist.com/guides/#authorization
 */
class Todoist extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * @var string If set, this will be sent to google as the "access_type" parameter.
     */
    protected $accessType;

    /**
     * @var string If set, this will be sent to google as the "hd" parameter.
     */
    protected $hostedDomain;

    /**
     * @var string If set, this will be sent to google as the "prompt" parameter.
     */
    protected $prompt;

    /**
     * @var array List of scopes that will be used for authentication.
     */
    protected $scopes = [];

    public function getBaseAuthorizationUrl(): string
    {
        return 'https://todoist.com/oauth/authorize';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://todoist.com/oauth/access_token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        // 'get user' method doesn't exist. Will use 'get projects' method to check the auth.
        return "https://api.todoist.com/rest/v2/projects";
    }

    protected function getDefaultScopes(): array
    {
        // "openid" MUST be the first scope in the list.
        return [
            'data:read_write',
        ];
    }

    protected function getScopeSeparator(): string
    {
        return ',';
    }

    protected function createResourceOwner(array $response, AccessToken $token): TodoistUser
    {
        return new TodoistUser($response); // MOCK USER
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() == 200) {
            return;
        }

        throw new IdentityProviderException(
            json_encode($data),
            $response->getStatusCode(),
            (string) $response->getBody()
        );
    }
}