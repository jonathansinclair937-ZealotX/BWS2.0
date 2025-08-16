-- 006_notifications_read_at.sql
ALTER TABLE bws2_notifications
  ADD COLUMN IF NOT EXISTS read_at DATETIME NULL,
  ADD INDEX IF NOT EXISTS idx_notifications_read (read_at);
