<?php
/**
 * CSRF protection for admin POST requests. Requires an active session.
 */

/**
 * Return the current session CSRF token, generating one if needed.
 */
function cms_csrf_token(): string {
    if (empty($_SESSION['cms_csrf'])) {
        $_SESSION['cms_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['cms_csrf'];
}

/**
 * Hidden input markup to embed in admin forms.
 */
function cms_csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . cms_e(cms_csrf_token()) . '">';
}

/**
 * Constant-time verification of a submitted token.
 */
function cms_csrf_verify(?string $token): bool {
    if (empty($_SESSION['cms_csrf']) || !is_string($token) || $token === '') {
        return false;
    }
    return hash_equals($_SESSION['cms_csrf'], $token);
}

/**
 * Guard for state-changing endpoints: verifies POST + token, else 400s.
 */
function cms_csrf_require(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !cms_csrf_verify($_POST['_csrf'] ?? null)) {
        http_response_code(400);
        echo 'Invalid or missing CSRF token.';
        exit;
    }
}
