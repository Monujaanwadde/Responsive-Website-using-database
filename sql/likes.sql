-- For likes
CREATE TABLE user_activity (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  image_id INT NOT NULL,
  liked TINYINT(1) DEFAULT 0,
  UNIQUE KEY (user_id, image_id)
);

-- For views
CREATE TABLE image_views (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  image_id INT NOT NULL,
  viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
