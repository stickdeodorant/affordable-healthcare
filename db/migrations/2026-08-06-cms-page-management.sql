-- CMS / Page Management System schema
-- Idempotent bootstrap. Run in MySQL 8+ on the core database (afford_leads).
-- The application also self-heals this schema on first admin load via cms/lib/db.php
-- (cms_ensure_schema), so running this file manually is optional.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

USE afford_leads;

-- Managed pages -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cms_pages (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Revision history (snapshot on every save; enables rollback) ----------------
CREATE TABLE IF NOT EXISTS cms_page_revisions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  page_id BIGINT UNSIGNED NOT NULL,
  snapshot_json LONGTEXT NOT NULL,
  editor VARCHAR(100) NULL,
  note VARCHAR(255) NOT NULL DEFAULT '',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_cms_page_revisions_page (page_id),
  KEY idx_cms_page_revisions_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin/marketer accounts ----------------------------------------------------
CREATE TABLE IF NOT EXISTS cms_users (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE cms_users ADD COLUMN IF NOT EXISTS oauth_provider VARCHAR(32) NULL AFTER password_hash;
ALTER TABLE cms_users ADD COLUMN IF NOT EXISTS oauth_sub VARCHAR(191) NULL AFTER oauth_provider;
ALTER TABLE cms_users ADD UNIQUE KEY IF NOT EXISTS uq_cms_users_oauth (oauth_provider, oauth_sub);

-- Redirects (301s for slug changes / archived pages) -------------------------
CREATE TABLE IF NOT EXISTS cms_redirects (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  from_path VARCHAR(255) NOT NULL,
  to_path VARCHAR(255) NOT NULL,
  code SMALLINT UNSIGNED NOT NULL DEFAULT 301,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cms_redirects_from (from_path)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login throttle (brute-force protection) ------------------------------------
CREATE TABLE IF NOT EXISTS cms_login_throttle (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ip VARCHAR(45) NOT NULL,
  attempts INT UNSIGNED NOT NULL DEFAULT 0,
  locked_until DATETIME NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cms_login_throttle_ip (ip)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit trail (shared with the wider app where present) ----------------------
CREATE TABLE IF NOT EXISTS admin_activity_log (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
