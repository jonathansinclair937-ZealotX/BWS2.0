-- 004_owners_and_notifications.sql
CREATE TABLE IF NOT EXISTS bws2_business_owners (
  id INT AUTO_INCREMENT PRIMARY KEY,
  business_id INT NOT NULL,
  username VARCHAR(255) NOT NULL,
  UNIQUE KEY uniq_owner (business_id, username),
  CONSTRAINT fk_owner_business FOREIGN KEY (business_id) REFERENCES bws2_businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bws2_notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(64) NOT NULL, -- e.g., review_pending
  recipient VARCHAR(255) NOT NULL, -- email or username
  payload JSON NOT NULL,
  status ENUM('queued','sent','failed') DEFAULT 'queued',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  sent_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
