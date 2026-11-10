<?php

declare(strict_types=1);

namespace Lockme\OAuth2\Client\Provider\Exception;

/**
 * 422: the request is well formed, but asks for something that cannot be done, e.g. a date in the past.
 */
class UnprocessableEntityException extends ClientErrorException {}
