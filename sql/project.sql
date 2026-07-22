CREATE DATABASE IF NOT EXISTS inventory_management_system;

USE inventory_management_system;

-- 1. Users
CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50) UNIQUE NOT NULL,
    password    VARCHAR(255) NOT NULL,           -- fixed: hashed password needs 255
    full_name   VARCHAR(100) NOT NULL,
    email       VARCHAR(100),
    role        ENUM('admin','staff') NOT NULL DEFAULT 'staff',  -- fixed: default staff
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Categories (new — replaces plain varchar in products)
CREATE TABLE categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 3. Suppliers
CREATE TABLE suppliers (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(150) NOT NULL,
    contact_person  VARCHAR(100),
    email           VARCHAR(100),
    phone           VARCHAR(20),
    address         VARCHAR(255),                -- fixed: 50 is too short for address
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 4. Products
CREATE TABLE products (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    name                VARCHAR(150) NOT NULL,
    description         TEXT,
    category_id         INT,                     -- fixed: FK to categories table
    price               DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    quantity            INT NOT NULL DEFAULT 0,
    low_stock_threshold INT NOT NULL DEFAULT 10,
    supplier_id         INT,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
);

-- 5. Transactions
CREATE TABLE txns (
    id          INT AUTO_INCREMENT PRIMARY KEY,  -- fixed: was missing AUTO_INCREMENT PK
    product_id  INT NOT NULL,
    type        ENUM('in','out') NOT NULL,
    quantity    INT NOT NULL,
    unit_price  DECIMAL(10,2),
    total_price DECIMAL(10,2),
    notes       TEXT,
    user_id     INT,
    txn_date    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE SET NULL
);

-- Insert default admin (password = "Admin123" hashed properly)
INSERT INTO users (username, password, full_name, email, role)
VALUES (
    'admin',
    '$2y$10$8tGGEHBqFmgMgMkDQIZgp.0v7rRDXwkpGWMvmIDCNJBlPdm4ZLcpG',
    'Administrator',
    'admin63@gmail.com',
    'admin'
);
