CREATE TABLE `subscribers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `status` ENUM('active','unsubscribed') DEFAULT 'active',
  `subscribed_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);
