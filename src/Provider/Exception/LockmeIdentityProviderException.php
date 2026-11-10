<?php

declare(strict_types=1);

namespace Lockme\OAuth2\Client\Provider\Exception;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;

use function is_array;
use function is_string;

class LockmeIdentityProviderException extends IdentityProviderException
{
    /**
     * Creates an exception from a response with an HTTP error status.
     *
     * The API describes a refusal in the `error` field, the OAuth token endpoint in `message`.
     *
     * @param mixed $data Parsed response body
     */
    public static function clientException(ResponseInterface $response, mixed $data): self
    {
        $message = is_array($data) ? ($data['message'] ?? $data['error'] ?? null) : null;

        return static::fromResponse($response, is_string($message) ? $message : null);
    }

    /**
     * Creates an exception from a successful response that nevertheless carries an `error` field.
     *
     * @param mixed $data Parsed response body
     */
    public static function oauthException(ResponseInterface $response, mixed $data): self
    {
        $message = is_array($data) ? ($data['error'] ?? null) : null;

        return static::fromResponse($response, is_string($message) ? $message : null);
    }

    /**
     * @param string|null $message Falls back to the HTTP reason phrase
     */
    protected static function fromResponse(ResponseInterface $response, ?string $message = null): self
    {
        return new self($message ?? $response->getReasonPhrase(), $response->getStatusCode(), (string) $response->getBody());
    }
}
