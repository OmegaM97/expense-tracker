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


INSERT INTO transactions 
(
    user_id,
    category_id,
    title,
    amount,
    type,
    payment_method,
    note,
    transaction_date
)
VALUES

-- INCOME
(1, 7, 'Monthly Salary', 25000.00, 'income', 'bank', 'Company salary payment', '2026-05-01'),

(1, 8, 'Freelance Website Project', 8000.00, 'income', 'mobile_payment', 'Client payment for website', '2026-06-05'),

-- EXPENSES
(1, 1, 'Burger Lunch', 350.00, 'expense', 'cash', 'Lunch at restaurant', '2026-05-06'),

(1, 2, 'Taxi Ride', 500.00, 'expense', 'cash', 'Ride to university', '2026-05-07'),

(1, 3, 'New Headphones', 2500.00, 'expense', 'debit_card', 'Bought headphones', '2026-04-08'),

(1, 4, 'Electric Bill', 1800.00, 'expense', 'bank', 'Monthly electric payment', '2026-05-09'),

(1, 5, 'Cinema Ticket', 600.00, 'expense', 'cash', 'Weekend movie', '2026-04-10'),

(1, 6, 'Medicine', 750.00, 'expense', 'cash', 'Pharmacy purchase', '2026-05-11'),

(1, 1, 'Pizza Dinner', 900.00, 'expense', 'credit_card', 'Dinner with friends', '2026-07-12'),

(1, 2, 'Fuel Expense', 2200.00, 'expense', 'bank', 'Fuel refill', '2026-07-13');