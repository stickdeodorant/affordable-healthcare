-- Idempotent schema bootstrap for affordable-healthcare
-- Run this in MySQL 8+.
-- If your server is older and does not support ADD COLUMN IF NOT EXISTS,
-- let me know and I will provide a compatibility version using information_schema checks.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ============================================================
-- 1) CORE DATABASE (DB_NAME / MQ_DB_NAME), usually afford_leads
-- ============================================================
USE afford_leads;

CREATE TABLE IF NOT EXISTS lead_buffer (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  lead_id VARCHAR(64) NOT NULL,
  email VARCHAR(255) NULL,
  phone VARCHAR(32) NULL,
  primary_phone VARCHAR(32) NULL,
  first_name VARCHAR(100) NULL,
  last_name VARCHAR(100) NULL,
  dob DATE NULL,
  age INT NULL,
  gender VARCHAR(16) NULL,
  zip VARCHAR(12) NULL,
  city VARCHAR(120) NULL,
  state VARCHAR(16) NULL,
  src VARCHAR(128) NULL,
  campaign VARCHAR(128) NULL,
  sub_id1 VARCHAR(128) NULL,
  sub_id2 VARCHAR(128) NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  form_data_json LONGTEXT NULL,
  boberdoo_status VARCHAR(32) NOT NULL DEFAULT 'pending',
  boberdoo_response LONGTEXT NULL,
  boberdoo_lead_id VARCHAR(64) NULL,
  boberdoo_error TEXT NULL,
  boberdoo_price DECIMAL(10,2) NULL,
  boberdoo_buyer VARCHAR(255) NULL,
  resubmit_count INT UNSIGNED NOT NULL DEFAULT 0,
  last_resubmit_time DATETIME NULL,
  resubmit_history LONGTEXT NULL,
  marked_for_resubmit TINYINT(1) NOT NULL DEFAULT 0,
  response_time_ms INT NULL,
  expires_at DATETIME NULL,
  submission_time DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_lead_buffer_lead_id (lead_id),
  KEY idx_lead_buffer_created_at (created_at),
  KEY idx_lead_buffer_status (boberdoo_status),
  KEY idx_lead_buffer_expires (expires_at),
  KEY idx_lead_buffer_email (email),
  KEY idx_lead_buffer_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE lead_buffer
  ADD COLUMN IF NOT EXISTS boberdoo_response LONGTEXT NULL,
  ADD COLUMN IF NOT EXISTS boberdoo_lead_id VARCHAR(64) NULL,
  ADD COLUMN IF NOT EXISTS boberdoo_error TEXT NULL,
  ADD COLUMN IF NOT EXISTS boberdoo_price DECIMAL(10,2) NULL,
  ADD COLUMN IF NOT EXISTS boberdoo_buyer VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS resubmit_count INT UNSIGNED NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS last_resubmit_time DATETIME NULL,
  ADD COLUMN IF NOT EXISTS resubmit_history LONGTEXT NULL,
  ADD COLUMN IF NOT EXISTS marked_for_resubmit TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS response_time_ms INT NULL,
  ADD COLUMN IF NOT EXISTS primary_phone VARCHAR(32) NULL,
  ADD COLUMN IF NOT EXISTS phone VARCHAR(32) NULL,
  ADD COLUMN IF NOT EXISTS expires_at DATETIME NULL,
  ADD COLUMN IF NOT EXISTS submission_time DATETIME NULL;

CREATE INDEX IF NOT EXISTS idx_lead_buffer_status ON lead_buffer (boberdoo_status);
CREATE INDEX IF NOT EXISTS idx_lead_buffer_expires ON lead_buffer (expires_at);

CREATE TABLE IF NOT EXISTS lead_history (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  lead_id VARCHAR(64) NULL,
  email VARCHAR(255) NULL,
  phone VARCHAR(32) NULL,
  first_name VARCHAR(100) NULL,
  last_name VARCHAR(100) NULL,
  age INT NULL,
  gender VARCHAR(16) NULL,
  city VARCHAR(120) NULL,
  state VARCHAR(16) NULL,
  zip VARCHAR(12) NULL,
  source VARCHAR(128) NULL,
  campaign VARCHAR(128) NULL,
  type VARCHAR(64) NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  status VARCHAR(32) NULL,
  boberdoo_status VARCHAR(32) NULL,
  boberdoo_response_code INT NULL,
  boberdoo_lead_id VARCHAR(64) NULL,
  boberdoo_error VARCHAR(255) NULL,
  boberdoo_error_message TEXT NULL,
  boberdoo_price DECIMAL(10,2) NULL,
  boberdoo_buyer VARCHAR(255) NULL,
  is_blacklisted TINYINT(1) NOT NULL DEFAULT 0,
  response_time_ms INT NULL,
  submission_timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_lead_history_lead_id (lead_id),
  KEY idx_lead_history_submission_ts (submission_timestamp),
  KEY idx_lead_history_status (boberdoo_status),
  KEY idx_lead_history_campaign (campaign),
  KEY idx_lead_history_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE lead_history
  ADD COLUMN IF NOT EXISTS boberdoo_status VARCHAR(32) NULL,
  ADD COLUMN IF NOT EXISTS boberdoo_response_code INT NULL,
  ADD COLUMN IF NOT EXISTS boberdoo_lead_id VARCHAR(64) NULL,
  ADD COLUMN IF NOT EXISTS boberdoo_error VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS boberdoo_error_message TEXT NULL,
  ADD COLUMN IF NOT EXISTS boberdoo_price DECIMAL(10,2) NULL,
  ADD COLUMN IF NOT EXISTS boberdoo_buyer VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS response_time_ms INT NULL,
  ADD COLUMN IF NOT EXISTS submission_timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN IF NOT EXISTS status VARCHAR(32) NULL;

CREATE TABLE IF NOT EXISTS email_blacklist (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(32) NULL,
  block_until DATETIME NOT NULL,
  submission_count INT UNSIGNED NOT NULL DEFAULT 1,
  is_permanent TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_email_blacklist_email (email),
  UNIQUE KEY uq_email_blacklist_phone (phone),
  KEY idx_email_blacklist_block_until (block_until),
  KEY idx_email_blacklist_perm (is_permanent)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE email_blacklist
  ADD COLUMN IF NOT EXISTS phone VARCHAR(32) NULL,
  ADD COLUMN IF NOT EXISTS submission_count INT UNSIGNED NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS is_permanent TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS block_until DATETIME NOT NULL;

CREATE TABLE IF NOT EXISTS resubmission_queue (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  buffer_lead_id BIGINT UNSIGNED NOT NULL,
  priority ENUM('high','medium','low') NOT NULL DEFAULT 'medium',
  status ENUM('pending','queued','scheduled','processing','completed','failed') NOT NULL DEFAULT 'queued',
  scheduled_time DATETIME NULL,
  attempts INT UNSIGNED NOT NULL DEFAULT 0,
  max_attempts INT UNSIGNED NOT NULL DEFAULT 3,
  last_attempt DATETIME NULL,
  error_message TEXT NULL,
  completed_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_resubmission_queue_buffer_lead_id (buffer_lead_id),
  KEY idx_resubmission_queue_status (status),
  KEY idx_resubmission_queue_scheduled_time (scheduled_time),
  KEY idx_resubmission_queue_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE resubmission_queue
  ADD COLUMN IF NOT EXISTS max_attempts INT UNSIGNED NOT NULL DEFAULT 3,
  ADD COLUMN IF NOT EXISTS error_message TEXT NULL,
  ADD COLUMN IF NOT EXISTS completed_at DATETIME NULL,
  ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add FK only if not already present. MySQL lacks IF NOT EXISTS for FK, so guard via dynamic SQL.
SET @fk_exists := (
  SELECT COUNT(*)
  FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'resubmission_queue'
    AND CONSTRAINT_NAME = 'fk_resubmission_queue_buffer_lead'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);
SET @sql_fk := IF(@fk_exists = 0,
  'ALTER TABLE resubmission_queue ADD CONSTRAINT fk_resubmission_queue_buffer_lead FOREIGN KEY (buffer_lead_id) REFERENCES lead_buffer(id) ON DELETE CASCADE',
  'SELECT 1');
PREPARE stmt_fk FROM @sql_fk;
EXECUTE stmt_fk;
DEALLOCATE PREPARE stmt_fk;

CREATE TABLE IF NOT EXISTS admin_activity_log (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  admin_user VARCHAR(100) NULL,
  action_type VARCHAR(64) NULL,
  target_type VARCHAR(64) NULL,
  target_id VARCHAR(255) NULL,
  action_details LONGTEXT NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  status VARCHAR(32) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_admin_activity_created_at (created_at),
  KEY idx_admin_activity_user (admin_user),
  KEY idx_admin_activity_action (action_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS api_response_log (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  lead_id VARCHAR(64) NULL,
  api_name VARCHAR(64) NULL,
  response_code INT NULL,
  response_body LONGTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_api_response_log_lead_id (lead_id),
  KEY idx_api_response_log_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dashboard_stats (
  stat_name VARCHAR(64) NOT NULL,
  stat_value VARCHAR(255) NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (stat_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS system_health_log (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  check_type VARCHAR(64) NOT NULL,
  status VARCHAR(32) NOT NULL,
  details TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_system_health_created_at (created_at),
  KEY idx_system_health_type (check_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS leads (
  ID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(255) NOT NULL,
  timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ip_address VARCHAR(45) NULL,
  error TEXT NULL,
  PRIMARY KEY (ID),
  KEY idx_leads_timestamp (timestamp),
  KEY idx_leads_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bademail (
  ID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(255) NOT NULL,
  timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ip_address VARCHAR(45) NULL,
  referrer VARCHAR(512) NULL,
  city VARCHAR(120) NULL,
  state VARCHAR(16) NULL,
  zip VARCHAR(12) NULL,
  PRIMARY KEY (ID),
  KEY idx_bademail_timestamp (timestamp),
  KEY idx_bademail_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2) FORM SUBMISSIONS DATABASE (FORM_SUBMISSIONS_DB_NAME)
-- ============================================================
USE healthcareins_form_submissions;

CREATE TABLE IF NOT EXISTS individual_entries (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  primary_phone VARCHAR(32) NULL,
  email VARCHAR(255) NULL,
  city VARCHAR(120) NULL,
  state VARCHAR(16) NULL,
  zip VARCHAR(12) NULL,
  household_income INT NULL,
  household_size INT NULL,
  currently_insured VARCHAR(64) NULL,
  age INT NULL,
  dob DATE NULL,
  lead_id VARCHAR(64) NULL,
  landing_page VARCHAR(1024) NULL,
  trustedform_token VARCHAR(255) NULL,
  ip_address VARBINARY(16) NULL,
  sub_id VARCHAR(128) NULL,
  seller_company_name VARCHAR(255) NULL,
  status VARCHAR(32) NULL,
  api_response_details LONGTEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_individual_entries_created_at (created_at),
  KEY idx_individual_entries_status (status),
  KEY idx_individual_entries_email (email),
  KEY idx_individual_entries_lead_id (lead_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE individual_entries
  ADD COLUMN IF NOT EXISTS trustedform_token VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS api_response_details LONGTEXT NULL,
  ADD COLUMN IF NOT EXISTS seller_company_name VARCHAR(255) NULL;

-- ============================================================
-- 3) Sanity checks
-- ============================================================
SELECT 'lead_buffer' AS table_name, COUNT(*) AS row_count FROM afford_leads.lead_buffer
UNION ALL
SELECT 'lead_history', COUNT(*) FROM afford_leads.lead_history
UNION ALL
SELECT 'email_blacklist', COUNT(*) FROM afford_leads.email_blacklist
UNION ALL
SELECT 'resubmission_queue', COUNT(*) FROM afford_leads.resubmission_queue
UNION ALL
SELECT 'leads', COUNT(*) FROM afford_leads.leads
UNION ALL
SELECT 'bademail', COUNT(*) FROM afford_leads.bademail
UNION ALL
SELECT 'individual_entries', COUNT(*) FROM healthcareins_form_submissions.individual_entries;
