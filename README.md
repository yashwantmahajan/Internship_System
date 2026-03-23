# Internship Management System

## Prerequisites
1.  **XAMPP/WAMP** (or any PHP + MySQL environment).
2.  **Web Browser**.

## Setup Instructions

### 1. Database Setup
1.  Open **phpMyAdmin** (usually `http://localhost/phpmyadmin`).
2.  Create a new database named **`internship_system`**.
3.  Click **Import** tab.
4.  Choose the `database.sql` file provided in this project folder and click **Import**.

### 2. Configure Database Connection
If you are NOT using the default XAMPP settings (User: `root`, Password: `Yashwant@1408`), open `db/db.php` and update the credentials:
```php
$host = "localhost";
$user = "YOUR_USERNAME";
$pass = "YOUR_PASSWORD";
$db   = "internship_system";
```

### 3. Run the Project
#### Option A: Using Built-in PHP Server (Recommended for quick test)
1.  Open this folder in a terminal (Command Prompt or PowerShell).
2.  Run the following command:
    ```sh
    php -S localhost:8000
    ```
3.  Open your browser and visit: [http://localhost:8000](http://localhost:8000)

#### Option B: Using XAMPP Apache
1.  Move this entire folder into your `htdocs` folder (e.g., `C:\xampp\htdocs\internship_system`).
2.  Start **Apache** and **MySQL** from XAMPP Control Panel.
3.  Open your browser and visit: `http://localhost/internship_system`

## Default Login Credentials

- **Students/Companies**: Register a new account to test.
