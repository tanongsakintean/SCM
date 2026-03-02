-- Insert Roles
INSERT INTO `roles` (`role_name`, `role_label`, `is_default`) VALUES
('Admin', 'Administrator', 0),
('Staff', 'General Staff', 1),
('Manager', 'Manager', 0);

-- Insert Users (Passwords are just examples, should be hashed in real app)
INSERT INTO `user` (`username`, `password`, `firstname`, `lastname`, `address`, `email`, `phone`) VALUES
('admin', '1234', 'John', 'Admin', '123 Admin St', 'admin@example.com', '0811111111'),
('staff1', '1234', 'Jane', 'Staff', '456 Staff Rd', 'staff1@example.com', '0822222222'),
('manager1', '1234', 'Mike', 'Manager', '789 Manager Ln', 'manager1@example.com', '0833333333');

-- Insert Role Permissions
INSERT INTO `role_permissions` (`role_id`, `permission_key`) VALUES
(1, 'manage_users'), (1, 'manage_roles'), (1, 'manage_orders'),
(2, 'view_orders'), (2, 'create_orders'),
(3, 'view_orders'), (3, 'approve_orders'), (3, 'view_reports');

-- Insert Categories
INSERT INTO `category` (`category_name`) VALUES
('SMS Bundle'),
('Marketing Service'),
('System Maintenance');

-- Insert Agents
INSERT INTO `agent` (`agent_name`, `agent_phone`, `agent_email`) VALUES
('ProSMS', '021111111', 'sales@prosms.com'),
('AlphaCom', '022222223', 'contact@alphacom.com'),
('ThaiBulkSMS', '023333333', 'support@thaibulksms.com');

-- Insert Customers
INSERT INTO `customer` (`customer_name`, `customer_phone`, `customer_email`, `credit_balance`) VALUES
('ABC Co., Ltd.', '021234567', 'contact@abc.co.th', 5000),
('XYZ Co., Ltd.', '029876543', 'info@xyz.co.th', 12000),
('John Doe', '0899999999', 'johndoe@gmail.com', 500);

-- Insert Credit Settings
INSERT INTO `credit_setting` (`credit_balance`, `credit_min`, `credit_date`, `user_id`, `notify_channels`) VALUES
(50000, 5000, '2026-03-01', 1, 'email,dashboard'),
(20000, 1000, '2026-03-01', 2, 'dashboard');

-- Insert Purchase Credit (Orders)
INSERT INTO `purchase_credit` (`order_number`, `user_id`, `agent_id`, `category_id`, `order_date`, `expected_date`, `order_quantity`, `order_status`, `order_note`) VALUES
('ORD-20260301-001', 2, 1, 1, '2026-03-01', '2026-03-05', 10000, 'Pending', 'Monthly SMS quota'),
('ORD-20260301-002', 3, 2, 2, '2026-03-01', '2026-03-10', 50000, 'Approved', 'Marketing campaign'),
('ORD-20260301-003', 1, 3, 1, '2026-03-02', '2026-03-02', 2000, 'Rejected', 'Duplicate order');

-- Insert Approves
INSERT INTO `approve` (`order_id`, `user_id`, `approval_status`, `approval_date`, `approval_note`) VALUES
(2, 3, 'Approved', '2026-03-01', 'Approved within budget'),
(3, 1, 'Rejected', '2026-03-02', 'Rejected as it is redundant');

-- Insert Sales
INSERT INTO `sale` (`sale_date`, `sale_amount`, `sale_price`, `sale_credit`, `user_id`, `customer_id`) VALUES
('2026-03-01', 5000.00, 5000.00, 5000, 2, 1),
('2026-03-02', 1200.00, 1200.00, 1200, 2, 3);

-- Insert System Logs
INSERT INTO `system_log` (`user_id`, `action`, `details`, `ip_address`) VALUES
(1, 'Login', 'Admin logged in successfully', '127.0.0.1'),
(2, 'Create Order', 'Created order ORD-20260301-001', '192.168.1.105'),
(3, 'Approve Order', 'Approved order ORD-20260301-002', '192.168.1.106');
