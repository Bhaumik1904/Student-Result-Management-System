# ResultPro — Student Result Management System

<div align="center">

![ResultPro Banner](https://img.shields.io/badge/ResultPro-v1.0.0-black?style=for-the-badge&logo=php)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

**A production-ready, Apple-inspired Student Result Management System built for PHP Full Stack internship assessment.**

[Features](#features) · [Tech Stack](#tech-stack) · [Installation](#installation) · [Screenshots](#screenshots) · [Deployment](#deployment)

</div>

---

## Overview

ResultPro is a complete academic result management platform that allows administrators to manage students, subjects, marks, and generate professional academic transcripts. Designed with an Apple-inspired UI philosophy — clean, spacious, and premium.

> "This looks like a real SaaS product, not a student project."

---

## Features

### 🔐 Authentication
- Secure admin login with session management
- PHP `password_hash()` / `password_verify()`
- Session regeneration on login
- Protected routes via middleware

### 👨‍🎓 Student Management
- Add / Edit / Delete students
- Roll number uniqueness validation
- 10-digit mobile validation
- Email validation
- Department categorization (CSE, ECE, EEE, Civil, Mechanical)
- DataTables with search, sort, pagination
- AJAX delete with SweetAlert2 confirmation

### 📚 Subject Management
- Add / Edit / Delete subjects
- Subject code and name management
- Configurable maximum marks per subject
- Fixed pass mark: 35

### ✏️ Marks Management
- Enter marks per student per subject
- Select2 searchable dropdowns
- Dynamic max-marks validation
- Duplicate entry prevention
- AJAX delete confirmation

### 📊 Result Processing (Auto-Calculated)
| Calculation | Formula |
|---|---|
| Total | Sum of all marks obtained |
| Percentage | `(Total Obtained / Total Maximum) × 100` |
| Grade A | ≥ 80% |
| Grade B | 70–79% |
| Grade C | 60–69% |
| Grade D | 50–59% |
| Grade F | < 50% |
| Result | FAIL if any subject < 35 |

### 🔍 Result Search
- AJAX-powered instant result lookup by Roll Number
- Full result card rendered in-page
- Student not found handling

### 🪪 Result Card (Transcript)
- Official academic transcript design
- Subject-wise marks breakdown
- Total, percentage, grade, result status
- Print-to-PDF support
- Standalone printable page

### 📈 Dashboard
- Total Students, Subjects, Pass, Fail, Average %
- Pass vs Fail doughnut chart (Chart.js)
- Department distribution bar chart
- Top 5 performers leaderboard
- Recently added students

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2+, PDO Prepared Statements |
| Database | MySQL 8.0+ |
| Frontend | HTML5, CSS3, Bootstrap 5.3 |
| JavaScript | ES6+, jQuery 3.7 |
| Charts | Chart.js 4 |
| Tables | DataTables 1.13 |
| Alerts | SweetAlert2 11 |
| Notifications | Toastr |
| Dropdowns | Select2 4.1 |
| Icons | Font Awesome 6.5 |
| Fonts | Inter (Google Fonts) |

**No build step required.** Runs directly on XAMPP, WAMP, Laragon, or shared hosting.

---

## Project Structure

```
student-result-management/
├── index.php                    # Root redirect
├── .gitignore
├── README.md
│
├── config/
│   └── db.php                   # PDO database connection
│
├── database/
│   └── database.sql             # Full schema + seed data
│
├── includes/
│   ├── auth_check.php           # Auth middleware
│   ├── functions.php            # Core helpers & result engine
│   ├── header.php               # Shared HTML header + sidebar
│   └── footer.php               # Shared HTML footer + scripts
│
├── assets/
│   ├── css/style.css            # Apple-inspired design system
│   └── js/app.js                # Main application JavaScript
│
├── auth/
│   ├── login.php                # Admin login
│   └── logout.php               # Session destruction
│
├── dashboard/
│   └── index.php                # Executive dashboard
│
├── students/
│   ├── add.php                  # Add student
│   ├── view.php                 # Student list + DataTables
│   └── edit.php                 # Edit student
│
├── subjects/
│   ├── add.php                  # Add subject
│   ├── view.php                 # Subject list + DataTables
│   └── edit.php                 # Edit subject
│
├── marks/
│   ├── add.php                  # Enter marks
│   ├── view.php                 # Marks list
│   └── edit.php                 # Update marks
│
└── results/
    ├── search.php               # AJAX result search
    ├── result-card.php          # Full academic transcript
    └── print.php                # Standalone print page
```

---

## Installation

### Prerequisites
- PHP 8.2 or higher
- MySQL 8.0 or higher
- Apache web server (XAMPP / WAMP / Laragon)

---

### 🖥 Local Deployment (XAMPP)

**Step 1 — Install XAMPP**
Download and install from [https://www.apachefriends.org](https://www.apachefriends.org)

**Step 2 — Clone / Download Project**
```bash
git clone https://github.com/Bhaumik1904/Student-Result-Management-System.git
```
Place the folder inside `C:/xampp/htdocs/`

**Step 3 — Import Database**
1. Start Apache and MySQL from XAMPP Control Panel
2. Open `http://localhost/phpmyadmin`
3. Click **New** → Database name: `student_result_db` → Create
4. Click **Import** → Choose `database/database.sql` → Go

**Step 4 — Configure Database**
Edit `config/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');           // Your MySQL password
define('DB_NAME', 'student_result_db');
define('BASE_URL', 'http://localhost/student-result-management');
```

**Step 5 — Run**
Open your browser: `http://localhost/student-result-management`

**Default Login Credentials:**
| Field | Value |
|---|---|
| Username | `admin` |
| Password | `admin123` |

---

### 🌐 Shared Hosting Deployment (cPanel / Hostinger / InfinityFree)

**Step 1 — Upload Files**
Upload all project files to `public_html/` via File Manager or FTP (FileZilla)

**Step 2 — Create Database**
1. Go to cPanel → MySQL Databases
2. Create a new database (e.g. `yourusername_resultdb`)
3. Create a database user and assign full privileges

**Step 3 — Import SQL**
1. Go to phpMyAdmin
2. Select your database → Import → Choose `database/database.sql` → Go

**Step 4 — Update Credentials**
Edit `config/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'yourusername_dbuser');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'yourusername_resultdb');
define('BASE_URL', 'https://yourdomain.com');
```

**Step 5 — Access Application**
Visit `https://yourdomain.com` or `https://yourdomain.com/student-result-management`

---

## Security Features

| Feature | Implementation |
|---|---|
| SQL Injection | PDO Prepared Statements |
| XSS | `htmlspecialchars()` on all output |
| CSRF | Token-based form protection |
| Auth | Session-based middleware |
| Password | `password_hash()` / `password_verify()` |
| Session | `session_regenerate_id(true)` on login |

---

## Database Schema

```sql
admins     → id, username, password, created_at
students   → id, roll_number, student_name, mobile, email, department, created_at
subjects   → id, subject_code, subject_name, max_marks, created_at
marks      → id, student_id, subject_id, marks_obtained, created_at, updated_at
```

---

## Screenshots

<div align="center">

### 1. Secure Admin Login
![Login Page](assets/screenshots/login.png)

### 2. Executive Dashboard & Analytics
![Dashboard](assets/screenshots/dashboard.png)

### 3. Student Management List
![Students List](assets/screenshots/students_list.png)

### 4. Add Student Form
![Add Student](assets/screenshots/add_student.png)

### 5. Branch-Specific Subjects Management
![Subjects List](assets/screenshots/subjects_list.png)

</div>

## API Endpoints (Internal AJAX)

| URL | Method | Purpose |
|---|---|---|
| `students/view.php` | POST | Delete student |
| `subjects/view.php` | POST | Delete subject |
| `marks/view.php` | POST | Delete marks |
| `results/search.php` | POST | AJAX result search |

---

## Contributing

This project was built as a PHP Full Stack Internship Assessment. Pull requests are welcome.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## Author

**Bhaumik**
- GitHub: [@Bhaumik1904](https://github.com/Bhaumik1904)
- Repository: [Student-Result-Management-System](https://github.com/Bhaumik1904/Student-Result-Management-System)

---

## License

This project is licensed under the MIT License.

---

<div align="center">
Built with ❤️ using PHP, MySQL & Bootstrap · ResultPro v1.0.0
</div>
