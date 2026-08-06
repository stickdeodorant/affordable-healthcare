<?php
/**
 * Google OAuth helpers for CMS admin login.
 */

function cms_oauth_google_configured(): bool {
    return CMS_OAUTH_GOOGLE_CLIENT_ID !== ''
        && CMS_OAUTH_GOOGLE_CLIENT_SECRET !== ''
        && CMS_OAUTH_GOOGLE_REDIRECT_URI !== '';
}

function cms_oauth_google_start_url(): string {
    $state = bin2hex(random_bytes(24));
    $_SESSION['cms_oauth_google_state'] = $state;
    $_SESSION['cms_oauth_google_state_exp'] = time() + 600;

    $query = http_build_query([
        'client_id' => CMS_OAUTH_GOOGLE_CLIENT_ID,
        'redirect_uri' => CMS_OAUTH_GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'hd' => CMS_OAUTH_ALLOWED_DOMAIN,
        'access_type' => 'online',
        'prompt' => 'select_account',
        'state' => $state,
    ]);

    return 'https://accounts.google.com/o/oauth2/v2/auth?' . $query;
}

function cms_oauth_google_callback(string $code, string $state): array {
    if (!cms_oauth_google_configured()) {
        return ['ok' => false, 'error' => 'Google OAuth is not configured.'];
    }

    $expected = $_SESSION['cms_oauth_google_state'] ?? '';
    $expires = (int)($_SESSION['cms_oauth_google_state_exp'] ?? 0);
    unset($_SESSION['cms_oauth_google_state'], $_SESSION['cms_oauth_google_state_exp']);

    if (!is_string($expected) || $expected === '' || $expires < time() || !hash_equals($expected, $state)) {
        return ['ok' => false, 'error' => 'Invalid or expired OAuth state. Please try again.'];
    }

    $token = cms_oauth_google_fetch_token($code);
    if (!$token['ok']) {
        return $token;
    }

    $profile = cms_oauth_google_fetch_profile((string)$token['access_token']);
    if (!$profile['ok']) {
        return $profile;
    }

    $email = strtolower(trim((string)($profile['email'] ?? '')));
    $name = trim((string)($profile['name'] ?? ''));
    $sub = trim((string)($profile['sub'] ?? ''));
    $verified = (bool)($profile['verified_email'] ?? false);
    $hd = strtolower(trim((string)($profile['hd'] ?? '')));

    if (!$verified) {
        return ['ok' => false, 'error' => 'Google account email is not verified.'];
    }
    if (!cms_email_allowed($email)) {
        return ['ok' => false, 'error' => 'Only @' . CMS_OAUTH_ALLOWED_DOMAIN . ' accounts may sign in.'];
    }
    if ($hd !== '' && $hd !== strtolower(CMS_OAUTH_ALLOWED_DOMAIN)) {
        return ['ok' => false, 'error' => 'Google hosted domain is not allowed.'];
    }

    $user = cms_upsert_oauth_user($email, $name, 'google', $sub);
    if (!$user || (int)($user['active'] ?? 0) !== 1) {
        return ['ok' => false, 'error' => 'User is not allowed to access CMS.'];
    }

    cms_login_user($user);
    cms_audit('login_google', 'cms_user', $user['id'], ['email' => $email, 'role' => $user['role']]);

    return ['ok' => true, 'error' => ''];
}

function cms_oauth_google_fetch_token(string $code): array {
    $payload = http_build_query([
        'code' => $code,
        'client_id' => CMS_OAUTH_GOOGLE_CLIENT_ID,
        'client_secret' => CMS_OAUTH_GOOGLE_CLIENT_SECRET,
        'redirect_uri' => CMS_OAUTH_GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code',
    ]);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $payload,
            'timeout' => 15,
            'ignore_errors' => true,
        ],
    ]);

    $raw = @file_get_contents('https://oauth2.googleapis.com/token', false, $context);
    $json = is_string($raw) ? json_decode($raw, true) : null;

    if (!is_array($json) || empty($json['access_token'])) {
        return ['ok' => false, 'error' => 'Google token exchange failed.'];
    }

    return ['ok' => true, 'access_token' => (string)$json['access_token']];
}

function cms_oauth_google_fetch_profile(string $accessToken): array {
    $url = 'https://www.googleapis.com/oauth2/v3/userinfo?access_token=' . rawurlencode($accessToken);
    $raw = @file_get_contents($url);
    $json = is_string($raw) ? json_decode($raw, true) : null;

    if (!is_array($json) || empty($json['email'])) {
        return ['ok' => false, 'error' => 'Google profile lookup failed.'];
    }

    return [
        'ok' => true,
        'sub' => (string)($json['sub'] ?? ''),
        'email' => (string)($json['email'] ?? ''),
        'verified_email' => (bool)($json['email_verified'] ?? false),
        'name' => (string)($json['name'] ?? ''),
        'hd' => (string)($json['hd'] ?? ''),
    ];
}
