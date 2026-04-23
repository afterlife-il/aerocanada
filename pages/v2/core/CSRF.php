<?php
/**
 * AeroCanada v2 — CSRF Protection
 * Token-based CSRF prevention for all form submissions.
 */

namespace AeroCanada\Core;

class CSRF
{
    private const TOKEN_NAME = 'csrf_token';
    private const TOKEN_TTL  = 3600; // 1 hour

    /**
     * Generate or retrieve CSRF token.
     */
    public static function token(): string
    {
        if (empty($_SESSION[self::TOKEN_NAME]) ||
            empty($_SESSION['csrf_time']) ||
            (time() - $_SESSION['csrf_time']) > self::TOKEN_TTL) {
            $_SESSION[self::TOKEN_NAME] = bin2hex(random_bytes(32));
            $_SESSION['csrf_time']      = time();
        }
        return $_SESSION[self::TOKEN_NAME];
    }

    /**
     * Output hidden input field.
     */
    public static function field(): string
    {
        return '<input type="hidden" name="' . self::TOKEN_NAME . '" value="' . self::token() . '">';
    }

    /**
     * Output meta tag (for AJAX requests).
     */
    public static function meta(): string
    {
        return '<meta name="csrf-token" content="' . self::token() . '">';
    }

    /**
     * Verify submitted token. Aborts on failure.
     */
    public static function verify(): void
    {
        $submitted = $_POST[self::TOKEN_NAME]
            ?? $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? '';

        if (empty($submitted) || !self::isValid($submitted)) {
            http_response_code(403);
            die('CSRF token validation failed. Please refresh the page and try again.');
        }
    }

    /**
     * Check if token is valid without aborting.
     */
    public static function isValid(string $token): bool
    {
        return !empty($_SESSION[self::TOKEN_NAME]) &&
               hash_equals($_SESSION[self::TOKEN_NAME], $token);
    }
}
