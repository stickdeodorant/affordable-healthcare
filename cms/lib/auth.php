<?php
/**
 * Authentication, roles, and brute-force throttling for the CMS admin.
 */

/**
 * The logged-in user array (id, email, name, role) or null.
 */
function cms_current_user(): ?array {
    return $_SESSION['cms_user'] ?? null;
}

function cms_is_logged_in(): bool {
    return cms_current_user() !== null;
}

/**
 * Numeric privilege level for a role name (unknown roles => 0).
 */
function cms_role_level(string $role): int {
    return $GLOBALS['CMS_ROLES'][$role] ?? 0;
}

/**
 * True if the current user's role meets or exceeds $minRole.
 */
function cms_user_can(string $minRole): bool {
    $user = cms_current_user();
    if (!$user) {
        return false;
    }
    return cms_role_level($user['role']) >= cms_role_level($minRole);
}

/**
 * Redirect to the login page if not authenticated.
 */
function cms_require_login(): void {
    if (!cms_is_logged_in()) {
        header('Location: ' . CMS_ADMIN_PATH . '/login.php');
        exit;
    }
}

/**
 * Enforce a minimum role; 403 if the user lacks privilege.
 */
function cms_require_role(string $minRole): void {
    cms_require_login();
    if (!cms_user_can($minRole)) {
        http_response_code(403);
        echo 'Insufficient privileges.';
        exit;
    }
}

/**
 * Hash a plaintext password (bcrypt).
 */
function cms_hash_password(string $password): string {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Create or update a CMS user. Returns the user id, or false on failure.
 */
function cms_upsert_user(string $email, string $password, string $role = 'marketer', string $name = '') {
    $email = strtolower(trim($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    if (!isset($GLOBALS['CMS_ROLES'][$role])) {
        $role = 'marketer';
    }
    $hash = cms_hash_password($password);

    return cms_write(
        'INSERT INTO cms_users (email, name, password_hash, role, active)
         VALUES (?, ?, ?, ?, 1)
         ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash),
                                 role = VALUES(role),
                                 name = VALUES(name),
                                 active = 1',
        'ssss',
        [$email, $name, $hash, $role]
    );
}

/**
 * Attempt login. Returns [ok=>bool, error=>string].
 * Applies per-IP throttling and constant-time password verification.
 */
function cms_login(string $email, string $password): array {
    $ip = cms_client_ip();

    if (cms_login_is_locked($ip)) {
        return ['ok' => false, 'error' => 'Too many attempts. Try again later.'];
    }

    $email = strtolower(trim($email));
    $user = cms_select_one(
        'SELECT id, email, name, password_hash, role, active FROM cms_users WHERE email = ? LIMIT 1',
        's',
        [$email]
    );

    $valid = $user
        && (int)$user['active'] === 1
        && password_verify($password, $user['password_hash']);

    if (!$valid) {
        cms_login_register_failure($ip);
        cms_audit('login_failed', 'cms_user', $email, [], 'denied');
        return ['ok' => false, 'error' => 'Invalid email or password.'];
    }

    cms_login_reset($ip);
    session_regenerate_id(true);
    $_SESSION['cms_user'] = [
        'id'    => (int)$user['id'],
        'email' => $user['email'],
        'name'  => $user['name'],
        'role'  => $user['role'],
    ];
    cms_write('UPDATE cms_users SET last_login_at = NOW() WHERE id = ?', 'i', [(int)$user['id']]);
    cms_audit('login', 'cms_user', $user['id']);

    return ['ok' => true, 'error' => ''];
}

function cms_logout(): void {
    if (cms_is_logged_in()) {
        cms_audit('logout', 'cms_user', cms_current_user()['id'] ?? null);
    }
    unset($_SESSION['cms_user']);
    session_regenerate_id(true);
}

/**
 * True if this IP is currently locked out.
 */
function cms_login_is_locked(string $ip): bool {
    $row = cms_select_one(
        'SELECT locked_until FROM cms_login_throttle WHERE ip = ? LIMIT 1',
        's',
        [$ip]
    );
    if (!$row || empty($row['locked_until'])) {
        return false;
    }
    return strtotime($row['locked_until']) > time();
}

function cms_login_register_failure(string $ip): void {
    // Lock once attempts reach the threshold.
    cms_write(
        'INSERT INTO cms_login_throttle (ip, attempts, locked_until)
         VALUES (?, 1, NULL)
         ON DUPLICATE KEY UPDATE
            attempts = attempts + 1,
            locked_until = IF(attempts + 1 >= ?, DATE_ADD(NOW(), INTERVAL ? MINUTE), NULL)',
        'sii',
        [$ip, CMS_LOGIN_MAX_ATTEMPTS, CMS_LOGIN_LOCK_MINUTES]
    );
}

function cms_login_reset(string $ip): void {
    cms_write(
        'INSERT INTO cms_login_throttle (ip, attempts, locked_until)
         VALUES (?, 0, NULL)
         ON DUPLICATE KEY UPDATE attempts = 0, locked_until = NULL',
        's',
        [$ip]
    );
}
