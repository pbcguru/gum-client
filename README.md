# GUM Client

Laravel Composer package for integrating with the GUM (Global User Management) service. Provides centralized user authentication, password management, and service enrollment across multiple Laravel applications.

## Installation

Add the repository and require the package:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/pbcguru/gum-client.git"
        }
    ],
    "require": {
        "pbc/gum-client": "dev-master"
    }
}
```

Then run:

```bash
composer update pbc/gum-client
php artisan migrate
```

The package auto-discovers its service provider. A `gum_user_id` column is added to your `users` table via the included migration.

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=gum-config
```

Add these to your `.env`:

```
GUM_API_URL=https://your-gum-instance.com/api
GUM_API_KEY=your-api-key
GUM_JWT_SECRET=your-jwt-secret
GUM_VERIFY_SSL=true
GUM_SERVICE_REGISTRY=your-service-slug
```

## Usage

The `GumClient` is registered as a singleton and can be resolved from the container:

```php
use Pbc\GumClient\GumClient;

$gum = app(GumClient::class);
```

### Register or Find a User

```php
$result = $gum->registerUser('user@example.com', 'Jane', 'Doe');

// $result['gum_user_id'] — unique ID across all services
// $result['created'] — true if new user, false if existing
// $result['enrollments'] — list of services this user is enrolled in
// $result['source_service'] — the first service that registered this user
```

### Authenticate

```php
$result = $gum->authenticate('user@example.com', 'password');

// Returns JWT token + user data on success
// Returns error if invalid credentials or not enrolled in this service
```

### Decode JWT

```php
$claims = $gum->decodeToken($jwt);

// $claims->gum_user_id
// $claims->email
// $claims->enrollments
// $claims->source_service
```

### Password Management

```php
// Direct password update (e.g., during account activation)
$gum->updatePassword($gumUserId, 'new-password');

// Request a password reset token
$result = $gum->requestReset('user@example.com');
// $result['token'] — use in your app's reset email

// Submit a password reset
$gum->resetPassword($token, 'user@example.com', 'new-password');
```

### Unenroll

```php
// Remove this service's enrollment for a user
$gum->unenroll($gumUserId);
```

## How It Works

- Each application registers with GUM using a service slug
- Users are enrolled per-service — a user can be in multiple services
- Authentication checks both credentials and service enrollment
- Passwords are centralized — one password works across all enrolled services
- The first service to register a user becomes the `source_service`

## Requirements

- PHP 8.2+
- Laravel 11, 12, or 13
- GuzzleHTTP 7+
- firebase/php-jwt 6.10+ or 7+
