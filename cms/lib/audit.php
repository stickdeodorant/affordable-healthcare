<?php
/**
 * Audit trail. Reuses the existing admin_activity_log table.
 */

/**
 * Best-effort client IP resolution behind common proxies.
 */
function cms_client_ip(): string {
    $keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '0.0.0.0';
}

/**
 * Record an admin action. $details is JSON-encoded. Never throws.
 */
function cms_audit(string $actionType, string $targetType = '', $targetId = null, array $details = [], string $status = 'ok'): void {
    $user = cms_current_user();
    $adminUser = $user['email'] ?? 'anonymous';
    $detailsJson = $details ? json_encode($details, JSON_UNESCAPED_SLASHES) : null;
    $ip = cms_client_ip();
    $ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 512);
    $targetId = $targetId === null ? null : (string)$targetId;

    // Auditing must never break a request; swallow storage errors.
    try {
        cms_write(
            'INSERT INTO admin_activity_log
                (admin_user, action_type, target_type, target_id, action_details, ip_address, user_agent, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            'ssssssss',
            [$adminUser, $actionType, $targetType, $targetId, $detailsJson, $ip, $ua, $status]
        );
    } catch (\Throwable $e) {
        error_log('cms_audit failed: ' . $e->getMessage());
    }
}
