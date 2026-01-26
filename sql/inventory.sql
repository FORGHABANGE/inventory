
CREATE DATABASE IF NOT EXISTS inventory;
USE inventory;

-- Roles table
CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE,
  description VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Users table
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role_id INT NOT NULL,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(150) UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(150),
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
);

-- Categories table
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sku VARCHAR(100) UNIQUE,
  name VARCHAR(255) NOT NULL,
  quantity INT DEFAULT 0,
  category_id INT,
  purchase_price DECIMAL(12,2) DEFAULT 0,
  selling_price DECIMAL(12,2) DEFAULT 0,
  reorder_level INT DEFAULT 0,
  image_path VARCHAR(255),
  description TEXT,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Stock movements table
CREATE TABLE stock_movements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  type ENUM('in','out','adjustment') NOT NULL,
  quantity INT NOT NULL,
  reference VARCHAR(150),
  note TEXT,
  user_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Sales table
CREATE TABLE sales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_no VARCHAR(120) UNIQUE,
  user_id INT,
  total_amount DECIMAL(12,2) DEFAULT 0,
  paid_amount DECIMAL(12,2) DEFAULT 0,
  customer_name VARCHAR(150),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Sale items table
CREATE TABLE sale_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sale_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL(12,2) NOT NULL,
  line_total DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
);


/* ------------------------------------------------------
   INSERT ESSENTIAL INITIAL DATA
---------------------------------------------------------*/

-- Insert roles
INSERT INTO roles (name, description) VALUES
('admin', 'Has full control of the inventory system'),
('staff', 'Handles sales, stock, and inventory tracking');

-- Insert admin user (password: )
INSERT INTO users (role_id, username, email, password_hash, full_name)
VALUES
(1, 'lucie', 'angelucie@gmail.com',
'$2y$10$wwmRZBTb3bPlUPIHkwe6x.l9ANIowKafgvoqH5oxR1tOGUZUqqG7y', 
'Forghab Ange');



/* ------------------------------------------------------
   INSERT CATEGORIES
---------------------------------------------------------*/

INSERT INTO categories (name, description) VALUES
('Beverages', 'Soft drinks, juices, and bottled water'),
('Food Items', 'Rice, spaghetti, oil, canned foods'),
('Cosmetics', 'Body lotions, perfumes, hair products'),
('Household Items', 'Detergents, soaps, cleaning supplies'),
('Electronics', 'Small gadgets and accessories'),
('Snacks', 'Biscuits, chocolates, sweets');

/* ------------------------------------------------------
   INSERT PRODUCTS
---------------------------------------------------------*/

INSERT INTO products 
(sku, name, quantity, category_id, purchase_price, selling_price, reorder_level, image_path, description, is_active)
VALUES
('PRD-001', 'Coca-Cola 1.5L', 15, 1, 600, 800, 20, NULL, 'Popular soft drink', 1),
('PRD-002', 'Supermont Water 1.5L' , 10, 1, 300, 500, 30, NULL, 'Cameroonian mineral water', 1),
('PRD-003', 'Golden Spaghetti 500g', 20, 2, 550, 700, 25, NULL, 'Pasta product', 1),
('PRD-004', 'Mayor Oil 1L', 8, 2, 1200, 1500, 15, NULL, 'Cooking oil', 1),
('PRD-005', 'Nido 400g', 10, 2, 2900, 3300, 10, NULL, 'Milk powder', 1),
('PRD-006', 'Dudu Osun Soap', 12, 3, 900, 1200, 20, NULL, 'Black natural soap', 1),
('PRD-007', 'Carrot Lotion', 10, 3, 1500, 2000, 10, NULL, 'Skin lotion', 1),
('PRD-008', 'Harpic Toilet Cleaner', 10, 4, 800, 1200, 10, NULL, 'Cleaning liquid', 1),
('PRD-009', 'Omo Detergent 1kg', 12, 4, 1800, 2300, 15, NULL, 'Laundry detergent', 1),
('PRD-010', 'USB Flash Drive 32GB', 15, 5, 3500, 4500, 5, NULL, 'Storage device', 1),
('PRD-011', 'Chocolate Bonbon', 20, 6, 100, 150, 50, NULL, 'Candy sweet', 1),
('PRD-012', 'Bisco Biscuit 50g', 65, 6, 100, 150, 40, NULL, 'Small biscuit', 1);

