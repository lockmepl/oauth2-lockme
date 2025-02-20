<?php
namespace Lockme\OAuth2\Client\Provider;

use GuzzleHttp\Client as HttpClient;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Lockme\OAuth2\Client\Provider\Exception\LockmeIdentityProviderException;
use Psr\Http\Message\ResponseInterface;

class Lockme extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * Api domain
     */
    protected string $apiDomain = 'https://api.lock.me';

    /**
     * API version
     */
    protected string $version = 'v2.3';

    /**
     * Default scopes
     */
    protected array $scopes = [];

    public function __construct(array $options = [])
    {
        $collaborators = [];
        if(isset($options['api_domain'])) {
            $options['apiDomain'] = $options['api_domain'];
        }
        if(isset($options['ignoreSslErrors']) && $options['ignoreSslErrors']) {
            $collaborators['httpClient'] = new HttpClient(
                [
                    'verify' => false
                ]
            );
        }
        parent::__construct($options, $collaborators);
    }

    public function getBaseAuthorizationUrl(): string
    {
        return $this->apiDomain.'/authorize';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->apiDomain.'/access_token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->apiDomain.'/'.$this->version.'/me';
    }

    protected function getDefaultScopes(): array
    {
        return $this->scopes;
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            throw LockmeIdentityProviderException::clientException($response, $data);
        }

        if (isset($data['error'])) {
            throw LockmeIdentityProviderException::oauthException($response, $data);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token): LockmeUser
    {
        return new LockmeUser($response);
    }

    /**
     * Generate request, execute it and return parsed response
     *
     * @throws IdentityProviderException
     */
    public function executeRequest(string $method, string $url, AccessToken|string|null $token, mixed $body = null): mixed
    {
        $options = [];
        if ($body) {
            $options['body'] = json_encode($body);
            $options['headers']['Content-Type'] = 'application/json';
        }

        $request = $this->getAuthenticatedRequest($method, $this->apiDomain.'/'.$this->version.$url, $token, $options);

        return $this->getParsedResponse($request);
    }
}
