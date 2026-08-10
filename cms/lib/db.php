<?php
/**
 * CMS database access + self-healing schema bootstrap.
 */

require_once dirname(__DIR__, 2) . '/inc/env.php';

/**
 * Shared mysqli connection for the CMS. Reuses the app's env-based config.
 */
function cms_db() {
    static $conn = null;
    if ($conn instanceof mysqli) {
        return $conn;
    }
    $conn = get_db_connection();
    $conn->set_charset('utf8mb4');
    cms_ensure_schema($conn);
    return $conn;
}

/**
 * Create CMS tables if they are missing. Cheap: skips entirely once cms_pages exists.
 */
function cms_ensure_schema(mysqli $conn) {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    // Skip only when BOTH the page store and the audit log already exist.
    $hasPages = ($r = $conn->query("SHOW TABLES LIKE 'cms_pages'")) && $r->num_rows > 0;
    $hasAudit = ($r = $conn->query("SHOW TABLES LIKE 'admin_activity_log'")) && $r->num_rows > 0;
    if ($hasPages && $hasAudit) {
        return;
    }

    $statements = [
        "CREATE TABLE IF NOT EXISTS cms_pages (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            slug VARCHAR(191) NOT NULL,
            title VARCHAR(255) NOT NULL DEFAULT '',
            meta_description VARCHAR(320) NOT NULL DEFAULT '',
            canonical VARCHAR(255) NOT NULL DEFAULT '',
            og_image VARCHAR(255) NOT NULL DEFAULT '',
            template VARCHAR(64) NOT NULL DEFAULT 'default',
            theme VARCHAR(64) NOT NULL DEFAULT 'default',
            status ENUM('draft','review','published','archived') NOT NULL DEFAULT 'draft',
            hero_headline VARCHAR(255) NOT NULL DEFAULT '',
            hero_subtitle VARCHAR(255) NOT NULL DEFAULT '',
            cta_text VARCHAR(120) NOT NULL DEFAULT '',
            cta_href VARCHAR(255) NOT NULL DEFAULT '',
            body_json LONGTEXT NULL,
            experiment_defaults TEXT NULL,
            redirect_to VARCHAR(255) NOT NULL DEFAULT '',
            created_by VARCHAR(100) NULL,
            updated_by VARCHAR(100) NULL,
            published_at DATETIME NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_cms_pages_slug (slug),
            KEY idx_cms_pages_status (status),
            KEY idx_cms_pages_updated_at (updated_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS cms_page_revisions (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            page_id BIGINT UNSIGNED NOT NULL,
            snapshot_json LONGTEXT NOT NULL,
            editor VARCHAR(100) NULL,
            note VARCHAR(255) NOT NULL DEFAULT '',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_cms_page_revisions_page (page_id),
            KEY idx_cms_page_revisions_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS cms_users (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            email VARCHAR(191) NOT NULL,
            name VARCHAR(120) NOT NULL DEFAULT '',
            password_hash VARCHAR(255) NOT NULL,
            oauth_provider VARCHAR(32) NULL,
            oauth_sub VARCHAR(191) NULL,
            role ENUM('marketer','reviewer','admin') NOT NULL DEFAULT 'marketer',
            active TINYINT(1) NOT NULL DEFAULT 1,
            last_login_at DATETIME NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_cms_users_email (email),
            UNIQUE KEY uq_cms_users_oauth (oauth_provider, oauth_sub),
            KEY idx_cms_users_active (active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS cms_redirects (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            from_path VARCHAR(255) NOT NULL,
            to_path VARCHAR(255) NOT NULL,
            code SMALLINT UNSIGNED NOT NULL DEFAULT 301,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_cms_redirects_from (from_path)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS cms_login_throttle (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            ip VARCHAR(45) NOT NULL,
            attempts INT UNSIGNED NOT NULL DEFAULT 0,
            locked_until DATETIME NULL,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_cms_login_throttle_ip (ip)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // Audit trail (shared with the wider app where present; created here for fresh DBs).
        "CREATE TABLE IF NOT EXISTS admin_activity_log (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            admin_user VARCHAR(191) NOT NULL DEFAULT '',
            action_type VARCHAR(64) NOT NULL DEFAULT '',
            target_type VARCHAR(64) NOT NULL DEFAULT '',
            target_id VARCHAR(100) NULL,
            action_details TEXT NULL,
            ip_address VARCHAR(45) NOT NULL DEFAULT '',
            user_agent VARCHAR(512) NOT NULL DEFAULT '',
            status VARCHAR(32) NOT NULL DEFAULT 'ok',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_admin_activity_log_action (action_type),
            KEY idx_admin_activity_log_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "ALTER TABLE cms_users ADD COLUMN IF NOT EXISTS oauth_provider VARCHAR(32) NULL AFTER password_hash",
        "ALTER TABLE cms_users ADD COLUMN IF NOT EXISTS oauth_sub VARCHAR(191) NULL AFTER oauth_provider",
        "ALTER TABLE cms_users ADD UNIQUE KEY IF NOT EXISTS uq_cms_users_oauth (oauth_provider, oauth_sub)",
    ];

    foreach ($statements as $sql) {
        if (!$conn->query($sql)) {
            error_log('CMS schema bootstrap failed: ' . $conn->error);
            break;
        }
    }
}

/**
 * Run a prepared SELECT and return all rows as associative arrays.
 * $types is a mysqli bind type string (e.g. 'si'); omit for no params.
 */
function cms_select(string $sql, string $types = '', array $params = []): array {
    $conn = cms_db();
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('cms_select prepare failed: ' . $conn->error);
        return [];
    }
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}

/**
 * Run a prepared SELECT and return the first row or null.
 */
function cms_select_one(string $sql, string $types = '', array $params = []): ?array {
    $rows = cms_select($sql, $types, $params);
    return $rows[0] ?? null;
}

/**
 * Run a prepared INSERT/UPDATE/DELETE. Returns insert id on INSERT, or affected rows,
 * or false on failure.
 */
function cms_write(string $sql, string $types = '', array $params = []) {
    $conn = cms_db();
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('cms_write prepare failed: ' . $conn->error);
        return false;
    }
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        error_log('cms_write execute failed: ' . $stmt->error);
        $stmt->close();
        return false;
    }
    $insertId = $stmt->insert_id;
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $insertId > 0 ? $insertId : $affected;
}
