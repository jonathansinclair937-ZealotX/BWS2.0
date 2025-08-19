-- 005_users_email.sql (portable MySQL/MariaDB)
-- Adds an email column and a unique index to the existing `users` table.
-- Safe to run on fresh DBs; if re-run it may raise "duplicate" errors which we'll ignore in the migrator.

ALTER TABLE users
  ADD COLUMN email VARCHAR(255) NULL,
  ADD UNIQUE KEY uniq_email (email);


-- Seed demo emails for scaffold users (safe to ignore if rows already exist)
INSERT INTO bws2_users (username, email, role) VALUES
('demo','demo@example.com','Client'),
('business','owner@example.com','Business'),
('initiator','initiator@example.com','Initiator'),
('admin','admin@example.com','Admin')
ON DUPLICATE KEY UPDATE email=VALUES(email), role=VALUES(role);
