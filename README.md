# Expense Tracker

A modern PHP expense tracker web app with login/register, premium dashboard UI, transaction management, and reporting.

## Features

- User authentication: register, login, logout
- Dashboard with financial summary and charts
- Transaction management: add, edit, delete, filter, search
- Reports view: income vs expense trends, total income/expense, net savings, top spending category
- Shared user interface across pages with sidebar and topbar
- Responsive modern design with soft shadows, orange accent, and fintech-style cards

## Tech Stack

- PHP
- MySQL / MariaDB
- HTML, CSS, JavaScript
- Chart.js for dashboard visuals
- XAMPP / Apache local web server

## Repo Structure

- `index.php` — landing page / public homepage
- `auth/` — authentication pages: `login.php`, `register.php`, `logout.php`
- `dashboard/dashboard.php` — main logged-in dashboard view
- `transactions/transaction.php` — add/manage transactions page
- `reports/report.php` — reporting and analytics page
- `includes/` — shared layout components: `header.php`, `footer.php`, `sidebar.php`, `topbar.php`
- `config/` — application config: `db.php`, `session.php`
- `assets/` — static front-end assets: `css/style.css`, `js/dashboard.js`
- `database/schema.sql` — database schema and default categories

## Requirements

- XAMPP installed with Apache and MySQL
- PHP 7.4+ or PHP 8.x
- MySQL / MariaDB

## Setup Instructions

1. Place the project in your XAMPP web root:

   - `C:\xampp\htdocs\expense-tracker`

2. Start XAMPP and enable:

   - Apache
   - MySQL

3. Create the database and tables:

   - Open phpMyAdmin at `http://localhost/phpmyadmin`
   - Create a new database named `expense_tracker`
   - Import the schema from `database/schema.sql`

   Or run the SQL directly in phpMyAdmin:

   ```sql
   CREATE DATABASE expense_tracker;
   USE expense_tracker;
   -- then import the rest of schema.sql
   ```

4. Confirm database credentials in `config/db.php`:

   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'expense_tracker');
   ```

   Update these values if your local MySQL user or password differs.

5. Open the app in your browser:

   - `http://localhost/expense-tracker`

6. Register a new user:

   - Visit `Register`
   - Enter first name, last name, email, password
   - After successful registration, login with the new account

7. Start tracking transactions:

   - Access the dashboard
   - Go to `Transactions`
   - Add income and expense entries
   - View reports from the `Reports` page

## Database Notes

- The schema includes `users`, `categories`, and `transactions` tables.
- `database/schema.sql` seeds default categories:
  - Food, Transport, Shopping, Bills, Entertainment, Health, Salary, Freelance
- After creating the first user, use the `Transactions` page to insert transaction data.

## URL Routes

- `http://localhost/expense-tracker/` — homepage
- `http://localhost/expense-tracker/auth/register.php` — register
- `http://localhost/expense-tracker/auth/login.php` — login
- `http://localhost/expense-tracker/dashboard/dashboard.php` — dashboard
- `http://localhost/expense-tracker/transactions/transaction.php` — transactions
- `http://localhost/expense-tracker/reports/report.php` — reports

## Notes

- The app uses session-based authentication.
- `includes/topbar.php` and `includes/sidebar.php` are shared across dashboard, transactions, and reports.
- If you see a database connection error, verify XAMPP MySQL is running and `config/db.php` matches your local credentials.

## Troubleshooting

- If `http://localhost/expense-tracker` returns 404:
  - Confirm the folder is inside `C:\xampp\htdocs`
  - Confirm Apache is running

- If login fails after user registration:
  - Verify the user record exists in the `users` table
  - Ensure password hashing is working correctly

## License

This project is an example local expense tracker application for learning and demonstration purposes.
