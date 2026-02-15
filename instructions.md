# Ayurveda E-Commerce System - Project Documentation

## 1. Project Overview
Meka Sri Lankawe ayurweda nishpadana online wikunanna hadapu system ekak. Meeke Admin saha Customer kiyala main roles dekak thiyenawa.

---

## 2. Business Requirements

### A. User Management
* **Registration:** Aluth customer kenekta site ekata register wenna puluwan wenna ona.
* **Login/Logout:** Register wechcha ayata secure widiyata login wenna saha wada iwara unama logout wenna puluwan wenna ona.
* **User Roles:** Admin (To manage products) saha Customer (To buy products).

### B. Product Management (Admin Only)
* **Add Products:** Admin ta nawa ayurweda nishpadana (Name, Description, Price, Image) system ekata athulath kala haki wiya yuthui.
* **Manage Inventory:** Thiyena products update kirima ho remove kirime hakiyaawa.

### C. Shopping Features (Customer)
* **Product Catalog:** Customer ta thiyena okkoma ayurveda items browse kala haki wiya yuthui.
* **Shopping Cart:** Kamathi items cart ekata add kara genima saha cart eka edit kirime hakiyaawa.
* **Checkout & Payment:** Cart eke thiyena items walata pay kara (Mock payment or Gateway) order eka confirm kirima.

---

## 3. Technical Requirements

### A. Technology Stack
* **Language:** PHP (Server-side scripting)
* **Database:** MySQL (Relational database)
* **Frontend:** HTML5, CSS3, JavaScript (Optional: Bootstrap for responsiveness)
* **Server:** XAMPP / WAMP ho live server ekak.

### B. Database Schema (Tentative Tables)
* **Users Table:** `id, name, email, password, role (admin/customer)`
* **Products Table:** `id, p_name, description, price, image_url, category`
* **Cart/Orders Table:** `id, user_id, product_id, quantity, status`

---

## 4. Implementation Instructions (Step-by-Step)

### Step 1: Environment Setup
1.  **XAMPP** install karaganna.
2.  `C:/xampp/htdocs/` athule oyaage project folder eka (e.g., `ayurveda_shop`) hadanna.
3.  **phpMyAdmin** gihin database ekak (e.g., `ayurveda_db`) hadala tables tika create karanna.

### Step 2: Database Connection
* `db_connect.php` kiyala file ekak hadala PHP saha MySQL connect karanna `mysqli_connect()` use karala.

### Step 3: User Authentication
1.  `register.php`: User details aran MySQL `users` table ekata insert karanna. (Password hash karanna `password_hash()` use karanna).
2.  `login.php`: User email/password check karala `$_SESSION` variables set karanna.
3.  `logout.php`: Session eka destroy karala userwa home page ekata yawanna.

### Step 4: Admin Interface (Product Management)
1.  Admin dashboard ekak hadanna.
2.  `add_product.php`: Image upload feature ekath samaga form ekak hadala products table ekata data danna.

### Step 5: Customer Interface & Navigation
1.  `header.php`: Navigation bar eka methana hadala (Home, Products, Cart, Login/Logout) hama page ekakatama include karanna.
2.  `index.php`: Database eke thiyena products `SELECT` query ekakin aran lassanata card view ekaka pennanna.

### Step 6: Shopping Cart & Checkout
1.  **Cart Logic:** Session ekak use karala user thoraganna product IDs tika save karaganna.
2.  **Payment:** Payment interface ekak hadala successfully pay kalama database ekata order details 'Complete' widiyata update wenna hadanna.

---

## 5. Safety & Security Suggestions
* **SQL Injection:** `mysqli_real_escape_string` ho Prepared Statements use karanna.
* **Session Security:** Hama page ekakatama `session_start()` dala user login welada nadda kiyala check karanna.