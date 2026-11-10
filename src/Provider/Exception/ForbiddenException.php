<?php

declare(strict_types=1);

namespace Lockme\OAuth2\Client\Provider\Exception;

/**
 * 403: the token is valid, but does not allow the room or the operation.
 */
class ForbiddenException extends ClientErrorException {}
