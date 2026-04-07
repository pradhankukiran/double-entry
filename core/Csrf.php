<?php

declare(strict_types=1);

namespace DoubleE\Core;

class Csrf
{
    private const TOKEN_KEY = '_csrf_token';

    /**
     * Generate or retrieve the current CSRF token.
     */
    public static function token(): string
    {
        if (!Session::has(self::TOKEN_KEY)) {
            Session::set(self::TOKEN_KEY, bin2hex(random_bytes(32)));
        }

        return Session::get(self::TOKEN_KEY);
    }

    /**
     * Generate an HTML hidden input field with the CSRF token.
     */
    public static function field(): string
    {
        $token = self::token();
        return '<input type="hidden" name="_csrf_token" value="' . View::e($token) . '">';
    }

    /**
     * Validate a submitted CSRF token.
     */
    public static function validate(?string $submittedToken): bool
    {
        $storedToken = Session::get(self::TOKEN_KEY);

        if ($storedToken === null || $submittedToken === null) {
            return false;
        }

        return hash_equals($storedToken, $submittedToken);
    }

    /**
     * Validate the CSRF token from the current POST request.
     * Throws an exception if invalid.
     */
    public static function check(): void
    {
        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (!self::validate($token)) {
            http_response_code(403);
            throw new \RuntimeException('CSRF token validation failed');
        }
    }

    /**
     * Regenerate the CSRF token (call after successful form submission).
     */
    public static function regenerate(): void
    {
        Session::set(self::TOKEN_KEY, bin2hex(random_bytes(32)));
    }
}
