<?php

declare(strict_types=1);

namespace Lockme\OAuth2\Client\Provider\Exception;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;

use function is_array;
use function is_string;

/**
 * Base of every exception the provider throws for a response of the LockMe API.
 *
 * The concrete class tells what went wrong: {@see ClientErrorException} and its subclasses for a 4xx status,
 * {@see ServerErrorException} for a 5xx status and {@see OAuthErrorException} for a successful response that
 * nevertheless carries an `error` field. Catching this class catches them all.
 */
abstract class LockmeIdentityProviderException extends IdentityProviderException
{
    /**
     * Creates the exception for a response with an HTTP error status.
     *
     * The API describes a refusal in the `error` field, the OAuth token endpoint in `message`.
     *
     * @param mixed $data Parsed response body
     */
    public static function clientException(ResponseInterface $response, mixed $data): self
    {
        $message = is_array($data) ? ($data['message'] ?? $data['error'] ?? null) : null;

        return self::fromResponse($response, is_string($message) ? $message : null);
    }

    /**
     * Creates the exception for a successful response that nevertheless carries an `error` field.
     *
     * @param mixed $data Parsed response body
     */
    public static function oauthException(ResponseInterface $response, mixed $data): OAuthErrorException
    {
        $message = is_array($data) ? ($data['error'] ?? null) : null;

        return new OAuthErrorException(
            is_string($message) ? $message : $response->getReasonPhrase(),
            $response->getStatusCode(),
            (string) $response->getBody(),
        );
    }

    /**
     * Picks the exception class by the HTTP status of the response.
     *
     * @param string|null $message Falls back to the HTTP reason phrase
     */
    protected static function fromResponse(ResponseInterface $response, ?string $message = null): self
    {
        $status = $response->getStatusCode();
        $class = match ($status) {
            400 => BadRequestException::class,
            401 => UnauthorizedException::class,
            403 => ForbiddenException::class,
            404 => NotFoundException::class,
            409 => ConflictException::class,
            422 => UnprocessableEntityException::class,
            default => $status >= 500 ? ServerErrorException::class : ClientErrorException::class,
        };

        return new $class($message ?? $response->getReasonPhrase(), $status, (string) $response->getBody());
    }
}
