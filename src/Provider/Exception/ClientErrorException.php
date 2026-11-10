<?php

declare(strict_types=1);

namespace Lockme\OAuth2\Client\Provider\Exception;

/**
 * The API refused the request with a 4xx status. A subclass names the status when the API documents it.
 */
class ClientErrorException extends LockmeIdentityProviderException {}
