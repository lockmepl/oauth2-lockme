<?php

declare(strict_types=1);

namespace Lockme\OAuth2\Client\Provider;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Lockme\OAuth2\Client\Provider\Exception\LockmeIdentityProviderException;
use Psr\Http\Message\ResponseInterface;
use JsonException;
use UnexpectedValueException;

use function is_array;
use function is_float;
use function is_int;
use function is_string;

use const JSON_THROW_ON_ERROR;

class Lockme extends AbstractProvider
{
    use BearerAuthorizationTrait;

    protected string $apiDomain = 'https://api.lock.me';

    /**
     * API version, used as the first segment of every API URL.
     */
    protected string $version = 'v2.7';

    /**
     * Scopes requested when the authorization URL names none.
     *
     * @var list<string>
     */
    protected array $scopes = [];

    /**
     * Besides the options of {@see AbstractProvider} the provider accepts:
     *  - `apiDomain` (or legacy `api_domain`): the API host, defaults to https://api.lock.me
     *  - `version`: the API version, defaults to v2.7
     *  - `scopes`: default scopes
     *  - `ignoreSslErrors`: disable SSL verification of the HTTP client (also without a proxy)
     *
     * @param array<string, mixed> $options
     * @param array<string, mixed> $collaborators
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        if (isset($options['api_domain'])) {
            $options['apiDomain'] = $options['api_domain'];
        }

        if (!empty($options['ignoreSslErrors']) && !isset($collaborators['httpClient'])) {
            $collaborators['httpClient'] = new HttpClient($this->insecureClientConfig($options));
        }

        parent::__construct($options, $collaborators);
    }

    public function getBaseAuthorizationUrl(): string
    {
        return $this->apiDomain . '/authorize';
    }

    /**
     * @param array<string, mixed> $params
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->apiDomain . '/access_token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->apiDomain . '/' . $this->version . '/me';
    }

    /**
     * @return list<string>
     */
    protected function getDefaultScopes(): array
    {
        return $this->scopes;
    }

    /**
     * @param mixed $data Parsed response body
     *
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            throw LockmeIdentityProviderException::clientException($response, $data);
        }

        if (is_array($data) && isset($data['error'])) {
            throw LockmeIdentityProviderException::oauthException($response, $data);
        }
    }

    /**
     * @param array<string, mixed> $response
     */
    protected function createResourceOwner(array $response, AccessToken $token): LockmeUser
    {
        return new LockmeUser($response);
    }

    /**
     * Executes an authenticated API request and returns the parsed response body.
     *
     * @param string $url  API path, relative to the versioned API root, e.g. `/rooms`
     * @param mixed  $body Sent JSON encoded when not null
     *
     * @throws IdentityProviderException When the API refuses the request
     * @throws GuzzleException           When the request cannot be sent
     * @throws UnexpectedValueException  When the response body is not valid JSON
     * @throws JsonException             When the body cannot be JSON encoded
     */
    public function executeRequest(string $method, string $url, AccessToken|string|null $token, mixed $body = null): mixed
    {
        $options = [];
        if (null !== $body) {
            $options = [
                'body' => json_encode($body, JSON_THROW_ON_ERROR),
                'headers' => ['Content-Type' => 'application/json'],
            ];
        }

        $request = $this->getAuthenticatedRequest($method, $this->apiDomain . '/' . $this->version . $url, $token, $options);

        return $this->getParsedResponse($request);
    }

    /**
     * Guzzle config for `ignoreSslErrors`: {@see AbstractProvider} only passes `verify` on to the HTTP client
     * together with a proxy, so the client is built here, keeping the options the provider would forward.
     *
     * @param array<string, mixed> $options
     *
     * @return array{verify: false, proxy?: string, timeout?: int|float}
     */
    private function insecureClientConfig(array $options): array
    {
        $config = [RequestOptions::VERIFY => false];
        if (is_string($options['proxy'] ?? null)) {
            $config[RequestOptions::PROXY] = $options['proxy'];
        }
        if (is_int($options['timeout'] ?? null) || is_float($options['timeout'] ?? null)) {
            $config[RequestOptions::TIMEOUT] = $options['timeout'];
        }

        return $config;
    }
}
