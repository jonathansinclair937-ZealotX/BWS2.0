-- 005_users_email.sql
ALTER TABLE bws2_users
  ADD COLUMN IF NOT EXISTS email VARCHAR(255) NULL,
  ADD UNIQUE INDEX IF NOT EXISTS uniq_email (email);

-- Seed demo emails for scaffold users (safe to ignore if rows already exist)
INSERT INTO bws2_users (username, email, role) VALUES
('demo','demo@example.com','Client'),
('business','owner@example.com','Business'),
('initiator','initiator@example.com','Initiator'),
('admin','admin@example.com','Admin')
ON DUPLICATE KEY UPDATE email=VALUES(email), role=VALUES(role);
