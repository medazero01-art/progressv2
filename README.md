# 🎓 Student School Management System

A full-stack web application built with PHP and MySQL for managing students, modules, grades, and academic records across multiple user roles.

---

## 📖 Overview

The **Student School Management System** is a university portal designed to streamline academic administration. It provides a unified platform where **administrators** can manage students, teachers, and modules; **teachers** can enter and update grades for their assigned modules; and **students** can view their grades, averages, and download a printable academic transcript.

This application solves the common challenge of fragmented academic data management by centralizing student records, course modules, grade assignments, and transcript generation into a single, secure, and responsive web interface.

**Who is it for?**

- University administrators managing academic data
- Teachers entering and tracking student grades
- Students viewing their academic performance

---

## ✨ Features

### Authentication & Security
- Role-based login system (Admin, Teacher, Student)
- Secure password hashing with `bcrypt`
- Session-based authentication with session regeneration
- Protection against SQL injection via PDO prepared statements

### Admin Panel
- **Dashboard** with real-time statistics (total students, teachers, modules)
- **Student Management** — full CRUD (Create, Read, Update, Delete) with search
- **Teacher Management** — full CRUD with module assignment tracking
- **Module Management** — create modules, assign teachers, set coefficients
- **Grade Management** — assign grades per student/module, automatic weighted average

### Teacher Interface
- View assigned modules with student count and class average
- Batch grade entry — enter grades for all students in a module at once
- Update existing grades with current/new value comparison
- Authorization enforcement — teachers can only grade their own modules

### Student Interface
- Personal dashboard with profile card and academic summary
- Detailed grades view with pass/fail status per module
- Automatic weighted average calculation
- **Printable academic transcript** with formal layout and print-optimized CSS

### User Experience
- Responsive design (desktop, tablet, mobile)
- Real-time table search and filtering
- Modal-based forms for create/edit operations
- Toast notifications for action feedback
- Client-side and server-side form validation

---

## 🛠️ Tech Stack

### Backend

| Technology | Purpose |
|------------|---------|
| **PHP 8+** | Server-side logic and routing |
| **MySQL** | Relational database |
| **PDO** | Database access with prepared statements |

### Frontend

| Technology | Purpose |
|------------|---------|
| **HTML5** | Semantic page structure |
| **CSS3** | Styling, responsive layout, animations |
| **Vanilla JavaScript** | Interactivity, modals, validation |
| **Google Fonts (Inter)** | Typography |

### Environment

| Tool | Purpose |
|------|---------|
| **Apache** | Web server (XAMPP / WAMP recommended) |
| **phpMyAdmin** | Database management GUI |

---

## 📁 Project Structure

```
PWEB/
│
├── config/
│   └── database.php            # PDO database connection configuration
│
├── includes/
│   ├── auth.php                 # Session management, role guards, helper functions
│   ├── header.php               # Top navigation bar with user menu
│   ├── sidebar.php              # Role-based sidebar navigation
│   └── footer.php               # Page footer and JS include
│
├── auth/
│   ├── login.php                # Login page with role selector
│   └── logout.php               # Session destruction and redirect
│
├── admin/
│   ├── dashboard.php            # Admin dashboard with statistics
│   ├── students.php             # Student CRUD management
│   ├── teachers.php             # Teacher CRUD management
│   ├── modules.php              # Module CRUD with teacher assignment
│   └── grades.php               # Grade assignment and averages
│
├── teacher/
│   ├── dashboard.php            # Teacher dashboard with module cards
│   ├── modules.php              # List of assigned modules
│   └── grades.php               # Batch grade entry per module
│
├── student/
│   ├── dashboard.php            # Student profile and grades overview
│   ├── grades.php               # Detailed grades with pass/fail status
│   └── transcript.php           # Printable academic transcript
│
├── assets/
│   ├── css/
│   │   └── style.css            # Complete stylesheet (responsive)
│   └── js/
│       └── script.js            # Client-side interactivity
│
├── sql/
│   └── schema.sql               # Database schema and sample data
│
├── index.php                    # Landing page with login CTA
├── design.md                    # UI/UX design specification
└── README.md                    # This file
```

### Folder Purposes

| Folder | Description |
|--------|-------------|
| `config/` | Database connection settings |
| `includes/` | Shared PHP components (header, sidebar, footer, auth helpers) |
| `auth/` | Authentication pages (login, logout) |
| `admin/` | Administrator-only pages for managing all data |
| `teacher/` | Teacher-only pages for module and grade management |
| `student/` | Student-only pages for viewing grades and transcript |
| `assets/` | Static frontend files (CSS, JavaScript) |
| `sql/` | Database schema and seed data |

---

## 🗄️ Database Schema

The database `school_management` consists of **5 tables**:

### `admins`
Stores administrator accounts with system-wide access.

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK) | Auto-increment primary key |
| `name` | VARCHAR(100) | Full name |
| `email` | VARCHAR(150) | Unique email address |
| `password` | VARCHAR(255) | Bcrypt-hashed password |

### `teachers`
Stores teacher accounts and profile information.

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK) | Auto-increment primary key |
| `name` | VARCHAR(100) | Full name |
| `email` | VARCHAR(150) | Unique email address |
| `password` | VARCHAR(255) | Bcrypt-hashed password |

### `students`
Stores student profiles with academic level and matriculation number.

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK) | Auto-increment primary key |
| `matricule` | VARCHAR(20) | Unique student identifier |
| `first_name` | VARCHAR(100) | First name |
| `last_name` | VARCHAR(100) | Last name |
| `birth_date` | DATE | Date of birth |
| `email` | VARCHAR(150) | Unique email address |
| `password` | VARCHAR(255) | Bcrypt-hashed password |
| `level` | VARCHAR(20) | Academic level (L1, L2, L3, M1, M2) |

### `modules`
Academic courses/modules, each optionally assigned to a teacher.

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK) | Auto-increment primary key |
| `code` | VARCHAR(20) | Unique module code (e.g., `MATH101`) |
| `name` | VARCHAR(150) | Module name |
| `coefficient` | INT | Weight for average calculation |
| `teacher_id` | INT (FK) | References `teachers.id` |

### `grades`
Individual student grades per module. Each student can have only one grade per module.

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK) | Auto-increment primary key |
| `student_id` | INT (FK) | References `students.id` |
| `module_id` | INT (FK) | References `modules.id` |
| `grade` | DECIMAL(5,2) | Grade value (0.00 – 20.00) |

### Entity Relationship

```
admins (standalone)

teachers ──< modules ──< grades >── students
   1:N          1:N         N:1
```

---

## 🚀 Installation Guide

Follow these steps to run the project on your local machine.

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) (or any Apache + MySQL + PHP environment)
- A modern web browser

### Step 1 — Install XAMPP

Download and install XAMPP from [apachefriends.org](https://www.apachefriends.org/). Make sure **Apache** and **MySQL** modules are included.

### Step 2 — Start Services

Open the XAMPP Control Panel and start **Apache** and **MySQL**.

### Step 3 — Get the Project

Clone the repository or download and extract the ZIP archive:

```bash
git clone https://github.com/your-username/school-management.git
```

Or simply download and unzip into a folder.

### Step 4 — Move to htdocs

Copy the project folder into your XAMPP web root:

```
C:/xampp/htdocs/PWEB/
```

The full path to `index.php` should be:

```
C:/xampp/htdocs/PWEB/index.php
```

### Step 5 — Create the Database

1. Open **phpMyAdmin** at [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Create a new database named:

```
school_management
```

3. Select the newly created database
4. Go to the **Import** tab
5. Choose the file `sql/schema.sql` from the project
6. Click **Go** to import the schema and sample data

### Step 6 — Configure the Database Connection

Open `config/database.php` and verify the credentials match your environment:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'school_management');
define('DB_USER', 'root');
define('DB_PASS', '');          // Default XAMPP has no password
```

### Step 7 — Open in Browser

Navigate to:

```
http://localhost/PWEB/
```

You should see the landing page. Click **Login** to access the system.

---

## 🔑 Default Credentials

All sample accounts use the same password: **`password`**

| Role | Email | Password |
|------|-------|----------|
| **Admin** | `admin@university.dz` | `password` |
| **Teacher** | `ahmed.benmoussa@university.dz` | `password` |
| **Teacher** | `fatima.zohra@university.dz` | `password` |
| **Teacher** | `karim.hadj@university.dz` | `password` |
| **Teacher** | `samira.belkacem@university.dz` | `password` |
| **Student** | `mohamed.benali@student.dz` | `password` |
| **Student** | `amina.khelifi@student.dz` | `password` |
| **Student** | `youssef.djellali@student.dz` | `password` |

> ⚠️ **Important**: Change all default passwords before using this application in any non-local environment.

---

## 📸 Screenshots

> Replace these placeholders with actual screenshots of the running application.

| Page | Description |
|------|-------------|
| **Landing Page** | Hero section with login CTA |
| **Login Page** | Role selector with email/password form |
| **Admin Dashboard** | Statistics cards and quick action links |
| **Student Management** | Table with search, add/edit modal |
| **Grade Management** | Student/module selectors with average display |
| **Teacher Grade Entry** | Batch grade input with save all |
| **Student Dashboard** | Profile card with grades and average |
| **Transcript** | Printable academic transcript |

---

## 🔒 Security Notes

This application implements several layers of security:

### Prepared Statements (PDO)
All database queries use **PDO prepared statements** with parameterized bindings. No user input is ever concatenated directly into SQL queries, preventing SQL injection attacks.

```php
$stmt = $pdo->prepare("SELECT * FROM students WHERE email = :email");
$stmt->execute(['email' => $email]);
```

### Password Hashing
Passwords are hashed using PHP's `password_hash()` with the **bcrypt** algorithm before storage. Authentication uses `password_verify()` to compare submitted passwords against stored hashes.

```php
// Hashing on registration
$hash = password_hash($password, PASSWORD_BCRYPT);

// Verification on login
if (password_verify($inputPassword, $storedHash)) { ... }
```

### Session Authentication
- Sessions are started on every protected page
- `requireRole()` guards enforce that only authorized users can access role-specific pages
- `session_regenerate_id(true)` is called after successful login to prevent session fixation
- Logout destroys the session and clears the session cookie

### Input Validation
- Server-side validation on all form submissions
- Client-side validation for immediate user feedback
- Output escaping with `htmlspecialchars()` to prevent XSS attacks

---

## 🔮 Future Improvements

- [ ] **REST API** — Expose data via a RESTful API for mobile app integration
- [ ] **PDF Transcript Export** — Generate downloadable PDF transcripts using a library like TCPDF or Dompdf
- [ ] **Advanced Permission System** — Granular role permissions and access control lists
- [ ] **Modern Frontend Framework** — Migrate to React, Vue.js, or a similar SPA framework
- [ ] **Dark Mode** — Full dark theme toggle (header toggle already designed)
- [ ] **Advanced Statistics** — Charts and analytics for admin dashboard (pass rates, grade distributions)
- [ ] **Email Notifications** — Alert students when new grades are posted
- [ ] **Password Recovery** — Forgot password flow with email verification
- [ ] **Pagination** — Server-side pagination for large datasets
- [ ] **Attendance Tracking** — Module for tracking student attendance
- [ ] **Multi-language Support** — French/Arabic translations for international use

---

## 👤 Author

This project was created as part of a **university web programming course** (Programmation Web — Semester 2).

Built to demonstrate full-stack web development skills including:

- PHP backend development with MVC-like architecture
- MySQL database design with proper normalization
- Responsive frontend design with modern CSS
- Security best practices in web applications
- Role-based access control and session management

---

## 📄 License

This project is licensed under the **MIT License**.

```
MIT License

Copyright (c) 2026 Student School Management System

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```
