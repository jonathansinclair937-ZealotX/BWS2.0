-- 005_users_email.sql (portable MySQL/MariaDB)
-- Ensures a `users` table exists, then adds `email` with a unique index.
-- Safe to re-run with our migration runner (duplicate errors are ignored).

-- 1) Create a minimal users table if it doesn't exist
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NULL,
  password_hash VARCHAR(255) NULL,
  role VARCHAR(50) NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2) Add email column and unique index (may already exist; runner will ignore duplicates)
ALTER TABLE users ADD COLUMN email VARCHAR(255) NULL;
ALTER TABLE users ADD UNIQUE KEY uniq_email (email);

-- 3) (Optional) Seed demo users; harmless if they already exist
INSERT INTO users (username, email, role)
VALUES
  ('demo','demo@example.com','Client'),
  ('business','owner@example.com','Business'),
  ('initiator','initiator@example.com','Initiator'),
  ('admin','admin@example.com','Admin')
ON DUPLICATE KEY UPDATE email=VALUES(email), role=VALUES(role);
