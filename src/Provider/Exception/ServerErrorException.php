<?php

declare(strict_types=1);

namespace Lockme\OAuth2\Client\Provider\Exception;

/**
 * The API failed with a 5xx status. Up to API version 2.6 every refusal is reported this way, since 2.7 only a real server error.
 */
class ServerErrorException extends LockmeIdentityProviderException {}
