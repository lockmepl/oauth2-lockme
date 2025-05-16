# OAuth2 Client for LockMe

This package provides LockMe OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

> **Recommendation**: For a more comprehensive integration with LockMe, we recommend using the [lustmored/lockme-sdk](https://github.com/lustmored/lockme-sdk) package, which provides a full-featured SDK for the LockMe API.

## Installation

To install, use Composer:

```
composer require lustmored/oauth2-lockme
```

## Requirements

The following versions of PHP are supported:

* PHP 5.6+
* PHP 7.0+
* PHP 8.0+

This package depends on:
- [league/oauth2-client](https://github.com/thephpleague/oauth2-client) (^2.2)
- PHP ext-json

## Usage

### Authorization Code Flow

```php
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Lockme\OAuth2\Client\Provider\Lockme;

// Initialize the provider
$provider = new Lockme([
    'clientId'     => '{lockme-client-id}',
    'clientSecret' => '{lockme-client-secret}',
    'redirectUri'  => 'https://example.com/callback-url',
]);

// If we don't have an authorization code then get one
if (!isset($_GET['code'])) {

    // Get authorization URL
    $authorizationUrl = $provider->getAuthorizationUrl([
        'scope' => 'rooms_manage' // Optional: specify scopes
    ]);

    // Get state and store it to the session
    $_SESSION['oauth2state'] = $provider->getState();

    // Redirect user to authorization URL
    header('Location: ' . $authorizationUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    try {
        // Try to get an access token using the authorization code grant
        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        // We have an access token, which we may use in authenticated
        // requests against the service provider's API.
        echo 'Access Token: ' . $accessToken->getToken() . "<br>";
        echo 'Refresh Token: ' . $accessToken->getRefreshToken() . "<br>";
        echo 'Expires: ' . $accessToken->getExpires() . "<br>";
        echo 'Already expired? ' . ($accessToken->hasExpired() ? 'Yes' : 'No') . "<br>";

        // Using the access token, get the user's details
        $resourceOwner = $provider->getResourceOwner($accessToken);

        // Show user ID
        var_dump($resourceOwner->getId());

        // Show all user data
        var_dump($resourceOwner->toArray());

    } catch (IdentityProviderException $e) {
        // Failed to get the access token or user details.
        exit($e->getMessage());
    }
}
```

### Refreshing a Token

```php
$refreshToken = $accessToken->getRefreshToken();

// Verify token has expired
if ($accessToken->hasExpired()) {
    $accessToken = $provider->getAccessToken('refresh_token', [
        'refresh_token' => $refreshToken
    ]);
}
```

### Making Authenticated API Requests

This package includes a helper method `executeRequest()` to easily make authenticated API requests:

```php
try {
    // Make an authenticated API request
    $response = $provider->executeRequest(
        'GET',                // HTTP method
        '/endpoint',          // API endpoint
        $accessToken,         // Access token
        ['param' => 'value']  // Optional request body (will be JSON encoded)
    );

    // Process the response
    var_dump($response);

} catch (IdentityProviderException $e) {
    // Handle error
    echo $e->getMessage();
}
```

## LockMe SDK and API Documentation

### LockMe SDK

For more advanced integration with LockMe, we recommend using the [lustmored/lockme-sdk](https://github.com/lustmored/lockme-sdk) package. This SDK provides a more comprehensive set of features and utilities specifically designed for the LockMe API.

To install the LockMe SDK, use Composer:

```
composer require lustmored/lockme-sdk
```

For detailed documentation and usage examples, please refer to the [SDK repository](https://github.com/lustmored/lockme-sdk).

### API Documentation

For complete API specifications and endpoint documentation, please refer to the official [LockMe API Documentation](https://apidoc.lock.me/).

### Configuring the Provider

The provider constructor accepts the following options:

```php
$provider = new Lockme([
    'clientId'        => '{lockme-client-id}',      // Required
    'clientSecret'    => '{lockme-client-secret}',  // Required
    'redirectUri'     => 'https://example.com/',    // Required
    'apiDomain'       => 'https://api.lock.me',     // Optional: custom API domain
    'version'         => 'v2.1',                    // Optional: API version
    'ignoreSslErrors' => false                      // Optional: ignore SSL errors
]);
```

## License

This package is released under the GPL-3.0-or-later License. See the bundled [LICENSE](LICENSE) file for details.
