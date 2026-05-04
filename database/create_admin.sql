-- Quick Admin Account Setup
-- Run this if you're having login issues

USE watch_store;

-- Delete existing admin if any
DELETE FROM admins WHERE username = 'admin';

-- Insert admin account
-- Username: admin
-- Password: Admin@123
INSERT INTO admins (username, email, password, full_name) VALUES 
('admin', 'admin@watchstore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator');

SELECT 'Admin account created successfully!' as Status;
SELECT * FROM admins;
