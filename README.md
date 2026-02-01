# CMS-secl

**Complaint Monitoring System for South Eastern Coalfields Limited (SECL)**

A PHP-based web application for recording, managing, and tracking departmental complaints with integrated stock management capabilities.

---

## 📋 Table of Contents
- [Features](#features)
- [Technology Stack](#technology-stack)
- [System Requirements](#system-requirements)
- [Installation](#installation)
- [Database Setup](#database-setup)
- [Configuration](#configuration)
- [Usage](#usage)
- [Project Structure](#project-structure)
- [Security Considerations](#security-considerations)
- [Development Roadmap](#development-roadmap)
- [Contributing](#contributing)
- [License](#license)

---

## ✨ Features

### Core Functionality
- **User Authentication**: Secure registration and login with role-based access control
- **Complaint Management**: 
  - Submit complaints with department, date, room number, and detailed descriptions
  - Track complaint status and resolution timeline
  - Officer signature approval workflow
  - Calculate days taken to resolve complaints
- **Data Export**: Export complaint records to CSV with multiple filtering options:
  - Date range selection
  - Department-based filtering
  - Custom row range export
- **Stock Management**: Record and track stock transfers across departments
- **Dashboard Views**: 
  - All complaints listing
  - Signed/approved complaints
  - Status monitoring with computed metrics

---

## 🛠 Technology Stack

- **Backend**: PHP 7.4+ (plain PHP with PDO)
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Frontend**: HTML5, CSS3, JavaScript
- **Server**: Apache / Nginx (or PHP built-in server for development)

---

## 💻 System Requirements

### Runtime Dependencies
- PHP 7.4 or higher
- PHP Extensions:
  - `pdo_mysql` (MySQL PDO driver)
  - `mbstring` (multibyte string handling)
  - `session` (session management)
  - `fileinfo` (optional, for file uploads)
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache/Nginx) or PHP built-in server

### Development Tools (Optional)
- Composer (for dependency management)
- Git (version control)
- phpMyAdmin or MySQL Workbench (database management)

---

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/yaash11/demo.git cms-secl
cd cms-secl
```

### 2. Web Server Setup

#### Option A: Apache/XAMPP (Recommended for Production)
1. Copy project to your web root:
   ```bash
   # Linux/Mac
   sudo cp -r . /var/www/html/cms-secl/
   
   # Windows (XAMPP)
   # Copy to C:\xampp\htdocs\cms-secl\
   ```

2. Configure Apache virtual host (optional):
   ```apache
   <VirtualHost *:80>
       ServerName cmssecl.local
       DocumentRoot "/var/www/html/cms-secl"
       <Directory "/var/www/html/cms-secl">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

3. Add to `/etc/hosts` (Linux/Mac) or `C:\Windows\System32\drivers\etc\hosts` (Windows):
   ```
   127.0.0.1 cmssecl.local
   ```

#### Option B: PHP Built-in Server (Development Only)
```bash
php -S localhost:8000
```
Access at `http://localhost:8000`

---

## 🗄 Database Setup

### 1. Create Database
```sql
CREATE DATABASE cmssecl CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Create Tables

#### Users Table (`cred`)
```sql
CREATE TABLE cred (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### Complaints Table (`ccdrc`)
```sql
CREATE TABLE ccdrc (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_date DATE NOT NULL,
    department VARCHAR(100) NOT NULL,
    room_no VARCHAR(50),
    complaint_description TEXT NOT NULL,
    submitted_by VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (submitted_by) REFERENCES cred(code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### Complaint Workflow Table (`cwceo`)
```sql
CREATE TABLE cwceo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    work_description TEXT,
    complaint_close_date DATE,
    engineer_sign VARCHAR(100),
    officer_sign VARCHAR(100),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES ccdrc(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### Complaint Status View
```sql
CREATE VIEW vw_complaint_status AS
SELECT 
    c.id,
    c.complaint_date,
    c.department,
    c.room_no,
    c.complaint_description,
    w.work_description,
    w.complaint_close_date,
    w.engineer_sign,
    w.officer_sign,
    DATEDIFF(w.complaint_close_date, c.complaint_date) AS days_taken_to_resolve
FROM ccdrc c
LEFT JOIN cwceo w ON c.id = w.complaint_id;
```

### 3. Create Database User (Recommended)
```sql
CREATE USER 'cmssecl_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT SELECT, INSERT, UPDATE, DELETE ON cmssecl.* TO 'cmssecl_user'@'localhost';
FLUSH PRIVILEGES;
```

---

## ⚙️ Configuration

### Database Connection (`db/db.php`)

**⚠️ IMPORTANT**: Never commit real credentials to version control!

1. Create a `.env` file in the project root:
   ```env
   DB_HOST=localhost
   DB_NAME=cmssecl
   DB_USER=cmssecl_user
   DB_PASS=secure_password_here
   ```

2. Update `db/db.php` to use environment variables:
   ```php
   <?php
   // Load environment variables
   if (file_exists(__DIR__ . '/../.env')) {
       $env = parse_ini_file(__DIR__ . '/../.env');
       foreach ($env as $key => $value) {
           putenv("$key=$value");
       }
   }

   $host = getenv('DB_HOST') ?: 'localhost';
   $db = getenv('DB_NAME') ?: 'cmssecl';
   $user = getenv('DB_USER') ?: 'root';
   $pass = getenv('DB_PASS') ?: '';
   $charset = 'utf8mb4';

   $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
   $options = [
       PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
       PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
       PDO::ATTR_EMULATE_PREPARES   => false,
   ];

   try {
       $pdo = new PDO($dsn, $user, $pass, $options);
   } catch (PDOException $e) {
       error_log('Database Connection Error: ' . $e->getMessage());
       die('Database connection failed. Please contact system administrator.');
   }
   ```

3. Add `.env` to `.gitignore`:
   ```
   .env
   ```

### Production Settings

In `db/db.php` or `php.ini`, for production:
```php
// DISABLE error display
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/logs/php-errors.log');
```

---

## 📖 Usage

### First Time Setup

1. **Access the application**:
   - Navigate to `http://localhost:8000/views/login.php` (or your configured domain)

2. **Register an admin user**:
   - Click "REGISTER" tab
   - Fill in employee code, name, select role
   - Submit registration

3. **Login**:
   - Use your employee code
   - Select your role from dropdown
   - Click "Login"

### Submitting Complaints

1. Navigate to "Complaint Form" (or `views/complaint_form.php`)
2. Fill in required fields:
   - Complaint Date
   - Department (from dropdown)
   - Room Number
   - Detailed Description
3. Submit the form

### Viewing Complaints

- **All Records**: Navigate to `views/records.php`
- **Signed Complaints**: Navigate to `views/signed_complaints.php`
- Complaints display with status, dates, and resolution metrics

### Exporting Data

1. Go to `views/excel.php`
2. Choose export method:
   - **Date Range**: Select start and end dates
   - **Department**: Filter by specific department
   - **Row Range**: Export specific row numbers
3. Click export to download CSV file

### Stock Management

1. Navigate to `views/stock.php`
2. Record stock transfers and view inventory

---

## 📁 Project Structure

```
cms-secl/
├── db/
│   └── db.php                 # PDO database connection
├── views/                     # Application pages
│   ├── login.php             # User authentication
│   ├── register.php          # User registration
│   ├── complaint_form.php    # Submit complaints
│   ├── signed_complaints.php # View signed complaints
│   ├── records.php           # All complaint records
│   ├── excel.php             # CSV export functionality
│   └── stock.php             # Stock management
├── includes/                  # Shared components
│   ├── header.php            # Common header
│   └── footer.php            # Common footer
├── css/
│   └── style.css             # Global styles
├── js/
│   └── main.js               # Client-side scripts
├── img/                      # Images and assets
├── .env.example              # Environment template
├── .gitignore
└── README.md                 # This file
```

---

## 🔐 Security Considerations

### Current Security Issues (MUST FIX)

1. **Database Credentials Exposure**
   - ❌ Hardcoded credentials in `db/db.php`
   - ✅ **Fix**: Use environment variables (see Configuration section)

2. **Error Display in Production**
   - ❌ `display_errors` enabled exposes sensitive information
   - ✅ **Fix**: Disable error display, enable logging

3. **Missing CSRF Protection**
   - ❌ Forms vulnerable to Cross-Site Request Forgery
   - ✅ **Fix**: Implement CSRF tokens:
     ```php
     // Generate token
     session_start();
     $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
     
     // In form
     <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
     
     // Validate on submit
     if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
         die('CSRF validation failed');
     }
     ```

4. **XSS Vulnerability**
   - ❌ User input not properly escaped in output
   - ✅ **Fix**: Use `htmlspecialchars()`:
     ```php
     echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');
     ```

5. **Password Storage**
   - ❌ Ensure passwords are hashed
   - ✅ **Fix**: Use `password_hash()` and `password_verify()`:
     ```php
     // Registration
     $hashed = password_hash($password, PASSWORD_DEFAULT);
     
     // Login
     if (password_verify($password, $hashed_from_db)) {
         // Success
     }
     ```

6. **Session Security**
   - ✅ **Fix**: Harden session cookies:
     ```php
     session_set_cookie_params([
         'lifetime' => 0,
         'path' => '/',
         'domain' => '',
         'secure' => true,      // HTTPS only
         'httponly' => true,    // No JavaScript access
         'samesite' => 'Strict' // CSRF protection
     ]);
     ```

### Recommended Security Headers

Add to `.htaccess` or web server config:
```apache
# Prevent XSS
Header set X-XSS-Protection "1; mode=block"

# Prevent clickjacking
Header set X-Frame-Options "SAMEORIGIN"

# Prevent MIME sniffing
Header set X-Content-Type-Options "nosniff"

# HTTPS only (if using SSL)
Header set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

---

## 🗺 Development Roadmap

### Priority 1: Security Hardening
- [ ] Move credentials to environment variables
- [ ] Implement CSRF protection
- [ ] Add output escaping (XSS prevention)
- [ ] Implement proper password hashing
- [ ] Harden session configuration
- [ ] Disable production error display

### Priority 2: Code Quality
- [ ] Refactor to MVC architecture
- [ ] Separate business logic from presentation
- [ ] Create data access layer
- [ ] Add input validation layer
- [ ] Implement dependency injection

### Priority 3: Database & Schema
- [ ] Create SQL migration files
- [ ] Add database seed data
- [ ] Document all table relationships
- [ ] Add database indexes for performance
- [ ] Implement soft deletes

### Priority 4: Testing & CI/CD
- [ ] Add PHPUnit tests
- [ ] Create integration tests
- [ ] Set up GitHub Actions CI
- [ ] Add code linting (PHP_CodeSniffer)
- [ ] Add static analysis (PHPStan)

### Priority 5: Features
- [ ] Email notifications for complaint updates
- [ ] File attachment support
- [ ] Advanced search and filtering
- [ ] Complaint analytics dashboard
- [ ] Role-based permissions system
- [ ] Audit logging

### Priority 6: Documentation
- [ ] API documentation
- [ ] Code commenting standards
- [ ] User manual
- [ ] Administrator guide
- [ ] Development setup guide

---

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. **Fork the repository**
2. **Create a feature branch**: `git checkout -b feature/your-feature-name`
3. **Follow coding standards**:
   - PSR-12 coding style
   - Meaningful variable/function names
   - Comment complex logic
4. **Test your changes**: Ensure no regressions
5. **Commit with clear messages**: `git commit -m "Add: complaint filtering by priority"`
6. **Push to your fork**: `git push origin feature/your-feature-name`
7. **Submit a Pull Request**

---

## 📄 License

This project is proprietary software developed for South Eastern Coalfields Limited (SECL) but now doesnot contain any relevent/crucial data related to them.

**Copyright © 2024 SECL. All rights reserved.**

Unauthorized copying, distribution, or modification of this software is strictly prohibited.

---

## 📞 Support

For issues, questions, or support:

- **Internal Support**: Contact your system administrator
- **Bug Reports**: Create an issue in the repository
- **Feature Requests**: Submit via internal ticketing system

---

## 🙏 Acknowledgments

- South Eastern Coalfields Limited (SECL)
- System Department Team

---

**Version**: 1.0.0  
**Last Updated**: july 2026  
**Maintained By**: me
