<?php

declare(strict_types=1);

namespace Lockme\OAuth2\Client\Provider\Exception;

/**
 * 401: no valid access token came with the request.
 */
class UnauthorizedException extends ClientErrorException {}
