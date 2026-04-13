<?php

namespace Pbc\GumClient;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class GumClient
{
    protected Client $http;
    protected string $apiKey;
    protected string $jwtSecret;
    protected string $service;

    public function __construct()
    {
        $this->http = new Client([
            'base_uri' => rtrim(config('gum.api_url'), '/') . '/',
            'timeout' => 10,
            'verify' => config('gum.verify_ssl', true),
        ]);

        $this->apiKey = config('gum.api_key');
        $this->jwtSecret = config('gum.jwt_secret');
        $this->service = config('gum.service');
    }

    /**
     * Register a user with GUM (create or find by email).
     * Returns: ['gum_user_id' => int, 'created' => bool, 'email' => string, ...]
     */
    public function registerUser(string $email, string $name, ?string $lastName = null, ?string $passwordHash = null): ?array
    {
        $data = [
            'email' => $email,
            'name' => $name,
            'last_name' => $lastName,
            'service' => $this->service,
        ];

        if ($passwordHash) {
            $data['password_hash'] = $passwordHash;
        }

        return $this->post('users', $data);
    }

    /**
     * Authenticate a user via GUM.
     * Returns: ['token' => string, 'gum_user_id' => int, ...] or null on failure.
     */
    public function authenticate(string $email, string $password): ?array
    {
        return $this->post('auth/login', [
            'email' => $email,
            'password' => $password,
            'service' => $this->service,
        ]);
    }

    /**
     * Decode a JWT token and return the claims.
     */
    public function decodeToken(string $token): ?object
    {
        try {
            return JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Update a user's password on GUM (for activation flows).
     */
    public function updatePassword(int $gumUserId, string $newPassword): ?array
    {
        return $this->post('password/update', [
            'gum_user_id' => $gumUserId,
            'password' => $newPassword,
        ]);
    }

    /**
     * Request a password reset token from GUM.
     * Returns: ['token' => string, 'email' => string]
     */
    public function requestReset(string $email): ?array
    {
        return $this->post('password/reset-request', [
            'email' => $email,
        ]);
    }

    /**
     * Submit a password reset to GUM.
     */
    public function resetPassword(string $token, string $email, string $newPassword): ?array
    {
        return $this->post('password/reset', [
            'token' => $token,
            'email' => $email,
            'password' => $newPassword,
        ]);
    }

    /**
     * Get a user by GUM ID.
     */
    public function getUser(int $gumUserId): ?array
    {
        return $this->get("users/{$gumUserId}");
    }

    /**
     * Update a user on GUM (name, email, active).
     */
    public function updateUser(int $gumUserId, array $data): ?array
    {
        return $this->put("users/{$gumUserId}", $data);
    }

    protected function post(string $endpoint, array $data): ?array
    {
        try {
            $response = $this->http->post("v1/{$endpoint}", [
                'json' => $data,
                'headers' => [
                    'X-API-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $body = json_decode($e->getResponse()->getBody()->getContents(), true);
                return $body;
            }
            return null;
        }
    }

    protected function get(string $endpoint): ?array
    {
        try {
            $response = $this->http->get("v1/{$endpoint}", [
                'headers' => [
                    'X-API-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                return json_decode($e->getResponse()->getBody()->getContents(), true);
            }
            return null;
        }
    }

    protected function put(string $endpoint, array $data): ?array
    {
        try {
            $response = $this->http->put("v1/{$endpoint}", [
                'json' => $data,
                'headers' => [
                    'X-API-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                return json_decode($e->getResponse()->getBody()->getContents(), true);
            }
            return null;
        }
    }
}
