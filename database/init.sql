CREATE TABLE IF NOT EXISTS `user` (
  `user_id` INT(10) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(45) NOT NULL,
  `password` VARCHAR(45) NOT NULL,
  `firstname` VARCHAR(45) NOT NULL,
  `lastname` VARCHAR(45) NOT NULL,
  `address` VARCHAR(100) NULL,
  `email` VARCHAR(45) NULL,
  `phone` VARCHAR(10) NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `permission` (
  `permission_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) NOT NULL,
  `permission_name` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`permission_id`),
  FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `customer` (
  `customer_id` INT(10) NOT NULL AUTO_INCREMENT,
  `customer_name` VARCHAR(45) NOT NULL,
  `customer_phone` VARCHAR(10) NULL,
  `customer_email` VARCHAR(45) NULL,
  `credit_balance` INT(11) DEFAULT 0,
  PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `credit_setting` (
  `credit_id` INT(10) NOT NULL AUTO_INCREMENT,
  `credit_balance` INT(10) NOT NULL DEFAULT 0,
  `credit_min` INT(10) NOT NULL DEFAULT 0,
  `credit_date` DATE NULL,
  `user_id` INT(10) NOT NULL,
  `notify_channels` VARCHAR(255) DEFAULT 'dashboard',
  PRIMARY KEY (`credit_id`),
  FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `category` (
  `category_id` INT(10) NOT NULL AUTO_INCREMENT,
  `category_name` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `agent` (
  `agent_id` INT(10) NOT NULL AUTO_INCREMENT,
  `agent_name` VARCHAR(45) NOT NULL,
  `agent_phone` VARCHAR(10) NULL,
  `agent_email` VARCHAR(45) NULL,
  PRIMARY KEY (`agent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `purchase_credit` (
  `order_id` INT(10) NOT NULL AUTO_INCREMENT,
  `order_number` VARCHAR(20) DEFAULT NULL,
  `user_id` INT(10) NOT NULL,
  `agent_id` INT(10) NOT NULL,
  `category_id` INT(10) NOT NULL,
  `order_date` DATE NULL,
  `expected_date` DATE NULL,
  `order_quantity` INT(10) NOT NULL DEFAULT 0,
  `order_status` VARCHAR(45) NOT NULL,
  `order_attachment` VARCHAR(255) NULL,
  `order_note` TEXT NULL,
  `receipt_proof` VARCHAR(255) NULL,
  `received_at` DATETIME NULL,
  `received_by` INT(10) NULL,
  PRIMARY KEY (`order_id`),
  FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`agent_id`) REFERENCES `agent` (`agent_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `sale` (
  `sale_id` INT(10) NOT NULL AUTO_INCREMENT,
  `sale_date` DATE NULL,
  `sale_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `sale_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `sale_credit` INT(10) NOT NULL DEFAULT 0,
  `user_id` INT(10) NOT NULL,
  `customer_id` INT(10) NOT NULL,
  PRIMARY KEY (`sale_id`),
  FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `report` (
  `report_id` INT(10) NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) NOT NULL,
  `report_name` VARCHAR(45) NOT NULL,
  `report_datecreate` DATE NULL,
  `report_filetype` VARCHAR(45) NULL,
  `report_note` VARCHAR(100) NULL,
  PRIMARY KEY (`report_id`),
  FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `approve` (
  `approval_id` INT(10) NOT NULL AUTO_INCREMENT,
  `order_id` INT(10) NOT NULL,
  `user_id` INT(10) NOT NULL,
  `approval_status` ENUM('Approved', 'Rejected') NOT NULL,
  `approval_date` DATE NOT NULL,
  `approval_note` TEXT NULL,
  PRIMARY KEY (`approval_id`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`),
  FOREIGN KEY (`order_id`) REFERENCES `purchase_credit` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `system_log` (
  `log_id` INT(10) NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) NOT NULL,
  `action` VARCHAR(100) NOT NULL,
  `details` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` INT(11) NOT NULL AUTO_INCREMENT,
  `role_name` VARCHAR(255) NOT NULL,
  `role_label` VARCHAR(255) NULL,
  `is_default` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
  `role_id` INT(11) NOT NULL,
  `permission_key` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_role_id` (`role_id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`role_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
