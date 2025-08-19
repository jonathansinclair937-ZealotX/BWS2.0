-- 006_notifications_read_at.sql (robust & portable)
-- Ensures `notifications` table exists, then adds `read_at` column + index if missing.

-- 0) Create `notifications` if it doesn't exist yet
CREATE TABLE IF NOT EXISTS notifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  business_id INT UNSIGNED NULL,
  type VARCHAR(50) NOT NULL,
  message TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  -- include read_at here for fresh installs; conditional ALTER below handles existing installs
  read_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 1) Conditionally add `read_at` column (for installs created before this file)
SET @col_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'notifications'
    AND COLUMN_NAME = 'read_at'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE notifications ADD COLUMN read_at DATETIME NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2) Conditionally add index on `read_at`
SET @idx_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'notifications'
    AND INDEX_NAME = 'idx_notifications_read'
);
SET @sql := IF(@idx_exists = 0,
  'ALTER TABLE notifications ADD INDEX idx_notifications_read (read_at)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3) Helpful indexes for lookups
SET @idx_user := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'notifications'
    AND INDEX_NAME = 'idx_notifications_user'
);
SET @sql := IF(@idx_user = 0,
  'ALTER TABLE notifications ADD INDEX idx_notifications_user (user_id)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_biz := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'notifications'
    AND INDEX_NAME = 'idx_notifications_business'
);
SET @sql := IF(@idx_biz = 0,
  'ALTER TABLE notifications ADD INDEX idx_notifications_business (business_id)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
