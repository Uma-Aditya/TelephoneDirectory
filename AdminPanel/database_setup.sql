-- Create and use the database
CREATE DATABASE IF NOT EXISTS admin_panel;
USE admin_panel;

-- Create the original_records table
CREATE TABLE IF NOT EXISTS original_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cpf VARCHAR(20) NOT NULL,
    name VARCHAR(100) NOT NULL,
    designation VARCHAR(100) NOT NULL,
    mobile VARCHAR(20) NOT NULL,
    section VARCHAR(100) NOT NULL,
    subsection VARCHAR(100),
    ext VARCHAR(20),
    direct VARCHAR(20),
    dob DATE,
    dor DATE,
    level VARCHAR(50),
    entry_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cpf (cpf)
);

-- Create the requests table
CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cpf VARCHAR(20) NOT NULL,
    name VARCHAR(100) NOT NULL,
    designation VARCHAR(100) NOT NULL,
    mobile VARCHAR(20) NOT NULL,
    section VARCHAR(100) NOT NULL,
    subsection VARCHAR(100),
    ext VARCHAR(20),
    direct VARCHAR(20),
    dob DATE,
    dor DATE,
    level VARCHAR(50),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_date TIMESTAMP NULL,
    processed_by VARCHAR(100),
    remarks TEXT
);

-- Insert sample data into original_records
INSERT INTO original_records 
(cpf, name, designation, mobile, section, subsection, ext, direct, dob, dor, level) 
VALUES
('3001001', 'Deepak Sharma', 'Software Engineer', '9811234567', 'IT', 'Development', '4001', '011-40001', '1992-05-15', '2052-05-15', 'E3'),
('3001002', 'Anita Patel', 'HR Executive', '9822345678', 'HR', 'Recruitment', '4002', '011-40002', '1990-08-20', '2050-08-20', 'E2'),
('3001003', 'Mohammed Khan', 'Finance Manager', '9833456789', 'Finance', 'Accounts', '4003', '011-40003', '1985-03-10', '2045-03-10', 'E4'),
('3001004', 'Ravi Kumar', 'Project Lead', '9844567890', 'Projects', 'Infrastructure', '4004', '011-40004', '1988-11-25', '2048-11-25', 'E4'),
('3001005', 'Priya Singh', 'Marketing Executive', '9855678901', 'Marketing', 'Digital', '4005', '011-40005', '1993-07-30', '2053-07-30', 'E2');

-- Insert sample requests
INSERT INTO requests 
(cpf, name, designation, mobile, section, subsection, ext, direct, dob, dor, level, status, request_date)
VALUES
('3001006', 'Sanjay Gupta', 'Senior Manager', '9866789012', 'Operations', 'Production', '4006', '011-40006', '1982-09-12', '2042-09-12', 'E5', 'pending', NOW()),
('3001007', 'Lakshmi Rao', 'Team Lead', '9877890123', 'Quality', 'Testing', '4007', '011-40007', '1987-12-05', '2047-12-05', 'E3', 'pending', NOW()),
('3001008', 'Vikram Malhotra', 'Technical Architect', '9888901234', 'Technology', 'Architecture', '4008', '011-40008', '1984-04-18', '2044-04-18', 'E4', 'pending', NOW()),
('3001009', 'Neha Reddy', 'Business Analyst', '9899012345', 'Business', 'Analysis', '4009', '011-40009', '1991-01-22', '2051-01-22', 'E3', 'pending', NOW()),
('3001010', 'Rajesh Verma', 'Support Engineer', '9800123456', 'Support', 'Technical', '4010', '011-40010', '1989-06-28', '2049-06-28', 'E2', 'pending', NOW()); 