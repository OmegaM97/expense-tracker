CREATE DATABASE expense_tracker;
USE expense_tracker;


-- USERS TABLE
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fname VARCHAR(50) NOT NULL,
    lname VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



-- CATEGORIES TABLE
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);



-- TRANSACTIONS TABLE
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,

    title VARCHAR(100) NOT NULL,

    amount DECIMAL(10,2) NOT NULL,

    type ENUM('income','expense') NOT NULL,

    payment_method ENUM(
        'cash',
        'bank',
        'credit_card',
        'debit_card',
        'mobile_payment'
    ) NOT NULL,

    note TEXT,

    transaction_date DATE NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    FOREIGN KEY (category_id)
        REFERENCES categories(id)
);

INSERT INTO categories (name) VALUES
('Food'),
('Transport'),
('Shopping'),
('Bills'),
('Entertainment'),
('Health'),
('Salary'),
('Freelance');