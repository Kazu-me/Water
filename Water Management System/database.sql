CREATE DATABASE water_billing_system;
USE water_billing_system;

ALTER TABLE customer ADD COLUMN disconnected TINYINT(1) DEFAULT 0;

ALTER TABLE user ADD COLUMN profile_pic VARCHAR(255) DEFAULT NULL;

-- Users table
CREATE TABLE user (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(100) NOT NULL,
    role VARCHAR(50) NOT NULL
);

-- Categories table
CREATE TABLE category (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) UNIQUE NOT NULL,
    rate DECIMAL(10,2) NOT NULL
);

-- Customers table
CREATE TABLE customer (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    last_name VARCHAR(100) NOT NULL,
    suffix VARCHAR(10),
    gender VARCHAR(10) NOT NULL,
    date_of_birth DATE NOT NULL,
    purok VARCHAR(100) NOT NULL,
    place_of_birth VARCHAR(100) NOT NULL,
    civil_status VARCHAR(20) NOT NULL,
    phone_number VARCHAR(20),
    category VARCHAR(100) NOT NULL,
    water_reading DECIMAL(10,4) NOT NULL,
    latest_reading_date DATE NOT NULL,
    del_status VARCHAR(20) DEFAULT NULL
);

-- Billing table
CREATE TABLE billing (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    user VARCHAR(100) NOT NULL,
    previous_reading DECIMAL(10,4) NOT NULL,
    current_reading DECIMAL(10,4) NOT NULL,
    previous_reading_date DATE NOT NULL,
    reading_date DATE NOT NULL,
    rate DECIMAL(10,2) NOT NULL,
    total_reading DECIMAL(10,4) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    due_date DATE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Pending',
    category VARCHAR(100) NOT NULL,
    balance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (customer_id) REFERENCES customer(id)
);

-- Payment table
CREATE TABLE payment (
    id INT PRIMARY KEY AUTO_INCREMENT,
    billing_id INT NOT NULL,
    customer_id INT NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    user VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (billing_id) REFERENCES billing(id),
    FOREIGN KEY (customer_id) REFERENCES customer(id)
);

-- Create default admin user
INSERT INTO user (username, password, role) VALUES ('admin', 'admin123', 'Administrator');

-- After creating the tables, add default categories
INSERT INTO category (category_name, rate) VALUES 
('Residential', 15.00),
('Commercial', 25.00),
('Industrial', 35.00),
('Institutional', 20.00); 
USE water_billing_system;

ALTER TABLE payment
ADD CONSTRAINT fk_billing
FOREIGN KEY (billing_id) REFERENCES billing(id)
ON DELETE CASCADE;

ALTER TABLE billing ADD COLUMN balance FLOAT DEFAULT 0;