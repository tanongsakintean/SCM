-- Insert Users
INSERT INTO `user` (`username`, `password`, `firstname`, `lastname`, `address`, `email`, `phone`) VALUES
('admin', '1234', 'Admin', 'User', '123 Admin St', 'admin@example.com', '0812345678'),
('staff', '1234', 'Staff', 'User', '456 Staff Rd', 'staff@example.com', '0812345679'),
('manager', '1234', 'Manager', 'User', '789 Manager Ln', 'manager@example.com', '0812345670');

-- Insert Permissions (Roles)
-- Assuming IDs 1, 2, 3 correspond to the users inserted above (AUTO_INCREMENT)
INSERT INTO `permission` (`user_id`, `permission_name`) VALUES
(1, 'Admin'),
(2, 'Staff'),
(3, 'Manager');
