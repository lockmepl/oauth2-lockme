<?php
namespace Lockme\OAuth2\Client\Provider;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Lockme\OAuth2\Client\Provider\Exception\LockmeIdentityProviderException;
use Psr\Http\Message\ResponseInterface;

class Lockme extends AbstractProvider{
  use BearerAuthorizationTrait;

  /**
   * Api domain
   *
   * @var string
   */
  public $apiDomain = "https://api.lockme.pl";

  /**
   * API version
   * @var string
   */
  public $version = "v2.0";

  public function __construct($options){
    if($options['beta']){
      $this->apiDomain = "https://api.beta.lockme.pl";
    }
    parent::__construct($options);
  }

  public function getBaseAuthorizationUrl(): string{
    return $this->apiDomain."/authorize";
  }

  public function getBaseAccessTokenUrl(array $params): string{
    return $this->apiDomain."/access_token";
  }

  public function getResourceOwnerDetailsUrl(AccessToken $token): string{
    return $this->apiDomain.'/me';
  }

  protected function getDefaultScopes(): array{
    return [];
  }

  protected function checkResponse(ResponseInterface $response, $data): void{
    if ($response->getStatusCode() >= 400) {
      throw LockmeIdentityProviderException::clientException($response, $data);
    } elseif (isset($data['error'])) {
      throw LockmeIdentityProviderException::oauthException($response, $data);
    }
  }

  protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface{
    return new LockmeUser($response);
  }

  /**
   * Generate request, execute it and return parsed response
   * @param  string $method
   * @param  string $url
   * @param  AccessToken|string|null $token
   * @param  mixed  $body
   * @return mixed
   */
  public function executeRequest($method, $url, $token, $body = null){
    $options = [];
    if($body){
      $options['body'] = json_encode($body);
      $options['headers']['Content-Type'] = 'application/json';
    }

    $request = $this->getAuthenticatedRequest($method, $this->apiDomain.$url, $token, $options);

    return $this->getParsedResponse($request);
  }
}
