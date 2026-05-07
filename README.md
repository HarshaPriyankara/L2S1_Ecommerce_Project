# දිවිසරණ System Setup Guide

## Prerequisites
1. **XAMPP** or **WAMP** server installed.
2. PHP and MySQL running.

## Installation Steps

1. **Move Project Folder**:
   - Copy or clone this project folder into your XAMPP `htdocs` directory.

2. **Database Setup**:
   - Open **phpMyAdmin** (usually `http://localhost/phpmyadmin`).
   - Create a new database named `ayurveda_db` (or just import the file, it will create it).
   - Import the `database.sql` file located in this folder. This creates the tables, admin account, and sample products.
   - If the products are still empty, open `http://localhost/L2S1_Ecommerce_Project/seed_products.php` once.

3. **Database Connection**:
   - By default, the app connects to MySQL at `127.0.0.1:3306` with user `root`, empty password, and database `ayurveda_db`.
   - Every team member should copy `includes/db.local.example.php` to `includes/db.local.php`.
   - If your XAMPP MySQL uses another port (for example `3308`), change the port in `includes/db.local.php`.
   - If your MySQL `root` user has a password, add that password in `includes/db.local.php`.
   - `includes/db.local.php` is ignored by git, so each team member can keep their own local settings.

4. **Admin Account**:
   - Default Admin Email: `admin@ayurveda.com`
   - Default Admin Password: `admin123`

5. **Running the App**:
   - Open your browser and go to the project folder URL, for example: `http://localhost/L2S1_Ecommerce_Project/index.php`

## Features
- **Admin**: Add products, View products, Delete products.
- **Customer**: Register, Login, View Products, Add to Cart, Checkout.
- **Security**: Password hashing, Session management, SQL injection protection.

## Troubleshooting
- If images don't upload, ensure the `uploads` folder exists and has write permissions.
- If the database does not connect, check `includes/db.local.php` and confirm your MySQL port in the XAMPP control panel.
- `Access denied for user 'root'@'localhost' (using password: NO)` means your MySQL root account needs a password. Add it to `includes/db.local.php`.
- `No connection could be made because the target machine actively refused it` usually means MySQL is not running or the port is wrong. Start MySQL in XAMPP and check whether it is using `3306` or `3308`.
