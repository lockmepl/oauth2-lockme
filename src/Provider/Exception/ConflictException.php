<?php

declare(strict_types=1);

namespace Lockme\OAuth2\Client\Provider\Exception;

/**
 * 409: the request clashes with the current state, e.g. the hour was booked meanwhile.
 */
class ConflictException extends ClientErrorException {}
