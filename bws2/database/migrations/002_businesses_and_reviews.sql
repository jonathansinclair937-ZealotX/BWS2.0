-- 002_businesses_and_reviews.sql
CREATE TABLE IF NOT EXISTS bws2_businesses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  logo VARCHAR(512) NULL,
  header VARCHAR(512) NULL,
  description TEXT NULL,
  black_ownership_pct TINYINT UNSIGNED DEFAULT 0,
  rating_avg DECIMAL(3,2) DEFAULT 0.0,
  rating_count INT DEFAULT 0,
  boosted TINYINT(1) DEFAULT 0,
  lat DECIMAL(9,6) NULL,
  lon DECIMAL(9,6) NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bws2_reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  business_id INT NOT NULL,
  username VARCHAR(255) NOT NULL,
  rating TINYINT NOT NULL,
  text VARCHAR(512) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reviews_business FOREIGN KEY (business_id) REFERENCES bws2_businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO bws2_businesses (name, logo, header, description, black_ownership_pct, rating_avg, rating_count, boosted, lat, lon)
VALUES
('Onyx Coffee', '/apps/web/assets/sample1.png', '/apps/web/assets/header1.jpg', 'Specialty coffee.', 100, 4.80, 123, 1, 40.7128, -74.0060),
('Kemet Books', '/apps/web/assets/sample2.png', '/apps/web/assets/header2.jpg', 'Books & culture.', 100, 4.60, 85, 0, 40.7328, -74.0160),
('Umoja Fitness', '/apps/web/assets/sample3.png', '/apps/web/assets/header3.jpg', 'Community gym.', 51, 4.90, 64, 0, 40.7228, -74.0010)
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO bws2_reviews (business_id, username, rating, text) VALUES
(1, 'ada', 5, 'Amazing coffee and atmosphere'),
(2, 'grace', 4, 'Great selection of African authors'),
(3, 'linus', 5, 'Supportive community gym');
