<?php
namespace Lockme\OAuth2\Client\Provider;

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
     *
     * @var string
     */
    public $apiDomain = 'https://api.lock.me';

    /**
     * API version
     * @var string
     */
    public $version = 'v2.0';

    public function __construct($options)
    {
        if ($options['beta']) {
            $this->apiDomain = 'https://api.lock.me.spjbnteggq-6s2dfxbi5xbfm.eu.s5y.io';
        }
        parent::__construct($options);
    }

    public function getBaseAuthorizationUrl()
    {
        return $this->apiDomain.'/authorize';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->apiDomain.'/access_token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->apiDomain.'/'.$this->version.'/me';
    }

    protected function getDefaultScopes()
    {
        return [];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw LockmeIdentityProviderException::clientException($response, $data);
        }

        if (isset($data['error'])) {
            throw LockmeIdentityProviderException::oauthException($response, $data);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new LockmeUser($response);
    }

    /**
     * Generate request, execute it and return parsed response
     * @param string                  $method
     * @param string                  $url
     * @param AccessToken|string|null $token
     * @param mixed                   $body
     * @return mixed
     * @throws IdentityProviderException
     */
    public function executeRequest($method, $url, $token, $body = null)
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
