<?php

declare(strict_types=1);

namespace Lockme\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

use function is_int;
use function is_string;

/**
 * The LockMe user the access token belongs to, as returned by the `/me` endpoint.
 */
class LockmeUser implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    /**
     * @param array<string, mixed> $response Raw `/me` response
     */
    public function __construct(
        private readonly array $response = [],
    ) {}

    public function getId(): ?int
    {
        $id = $this->getValueByKey($this->response, 'userid');
        if (is_int($id)) {
            return $id;
        }

        return is_string($id) && ctype_digit($id) ? (int) $id : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->response;
    }
}
