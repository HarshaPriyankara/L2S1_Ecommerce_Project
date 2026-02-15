# PosMini Ayurveda System Setup Guide

## Prerequisites
1. **XAMPP** or **WAMP** server installed.
2. PHP and MySQL running.

## Installation Steps

1. **Move Project Folder**:
   - Copy this entire `PosMini` folder to your XAMPP `htdocs` directory (e.g., `C:\xampp\htdocs\PosMini`).

2. **Database Setup**:
   - Open **phpMyAdmin** (usually `http://localhost/phpmyadmin`).
   - Create a new database named `ayurveda_db` (or just import the file, it will create it).
   - Import the `database.sql` file located in this folder.

3. **Admin Account**:
   - Default Admin Email: `admin@ayurveda.com`
   - Default Admin Password: `admin123`

4. **Running the App**:
   - Open your browser and go to: `http://localhost/PosMini/index.php`

## Features
- **Admin**: Add products, View products, Delete products.
- **Customer**: Register, Login, View Products, Add to Cart, Checkout.
- **Security**: Password hashing, Session management, SQL injection protection.

## Troubleshooting
- If images don't upload, ensure the `uploads` folder exists and has write permissions.
- Check `includes/db.php` if you changed database credentials.
