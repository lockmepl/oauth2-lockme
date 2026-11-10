<?php

declare(strict_types=1);

namespace Lockme\OAuth2\Client\Provider\Exception;

/**
 * A successful response that nevertheless carries an `error` field, as the OAuth endpoints may answer.
 */
class OAuthErrorException extends LockmeIdentityProviderException {}
