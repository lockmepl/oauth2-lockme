# Changelog

All notable changes to this package are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the package adheres to
[Semantic Versioning](https://semver.org/).

## [1.6.0] - 2026-11-10

### Added

- Status-specific exceptions, all extending `LockmeIdentityProviderException`: `ClientErrorException` for any
  4xx status with `BadRequestException` (400), `UnauthorizedException` (401), `ForbiddenException` (403),
  `NotFoundException` (404), `ConflictException` (409) and `UnprocessableEntityException` (422),
  `ServerErrorException` for 5xx and `OAuthErrorException` for a successful response carrying an `error` field.
- Second constructor argument `$collaborators`, passed on to `League\OAuth2\Client\Provider\AbstractProvider`,
  so an HTTP client can be injected the League way.
- `ignoreSslErrors` forwards the `proxy` and `timeout` options to the HTTP client it builds.
- PHP CS Fixer and PHPStan (level max) configuration with `composer check` and `composer cs:fix` scripts.
- This changelog.

### Changed

- Default API version is 2.7. Since 2.7 the API answers a refused request with a 4xx status instead of 500,
  see the [API documentation](https://apidoc.lock.me/).
- The exception message carries the API's description of the error (the `error` field) instead of the HTTP
  reason phrase. The `message` field of the OAuth token endpoint keeps precedence.
- `LockmeIdentityProviderException` is abstract; its factories return the concrete subclass for the HTTP status.
  Catching it, or `IdentityProviderException`, keeps working.
- `executeRequest()` sends a body whenever it is not `null`, so an empty array goes out as `[]`.
- `executeRequest()` throws `JsonException` when the body cannot be encoded instead of sending `false`.
- `LockmeUser::getId()` returns `?int`; a non-numeric `userid` yields `null`.
- `LockmeUser::$response` is `private readonly` instead of `protected`.
- Strict types, full parameter and return types and PER-CS 2.0 code style throughout.
- `guzzlehttp/guzzle` (`^7.8 || ^8.0`) is declared as a direct dependency.
- README documents the PHP 8.2 requirement, the exception hierarchy and the development tooling.

## [1.5.0] - 2025-09-09

Last release before this changelog was introduced.

[1.6.0]: https://github.com/lockmepl/oauth2-lockme/compare/1.5.0...1.6.0
[1.5.0]: https://github.com/lockmepl/oauth2-lockme/releases/tag/1.5.0
