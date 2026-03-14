-- ============================================================
-- Student School Management System — Database Schema
-- MySQL 8+ compatible
-- ============================================================

-- Create database (run manually if needed)
-- CREATE DATABASE IF NOT EXISTS school_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE school_management;

-- ============================================================
-- 1. ADMINS TABLE
-- Stores administrator accounts for system management.
-- ============================================================
CREATE TABLE IF NOT EXISTS admins (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)    NOT NULL,
    email       VARCHAR(150)    NOT NULL UNIQUE,
    password    VARCHAR(255)    NOT NULL,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. TEACHERS TABLE
-- Stores teacher accounts and profile information.
-- ============================================================
CREATE TABLE IF NOT EXISTS teachers (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)    NOT NULL,
    email       VARCHAR(150)    NOT NULL UNIQUE,
    password    VARCHAR(255)    NOT NULL,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_teacher_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. STUDENTS TABLE
-- Stores student profiles with matricule and academic level.
-- ============================================================
CREATE TABLE IF NOT EXISTS students (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    matricule   VARCHAR(20)     NOT NULL UNIQUE,
    first_name  VARCHAR(100)    NOT NULL,
    last_name   VARCHAR(100)    NOT NULL,
    birth_date  DATE            NULL,
    email       VARCHAR(150)    NOT NULL UNIQUE,
    password    VARCHAR(255)    NOT NULL,
    level       VARCHAR(20)     NOT NULL DEFAULT 'L1',
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_student_matricule (matricule),
    INDEX idx_student_email (email),
    INDEX idx_student_level (level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. MODULES TABLE
-- Academic modules/courses, each assigned to a teacher.
-- ============================================================
CREATE TABLE IF NOT EXISTS modules (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(20)     NOT NULL UNIQUE,
    name        VARCHAR(150)    NOT NULL,
    coefficient INT             NOT NULL DEFAULT 1,
    teacher_id  INT             NULL,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_module_code (code),
    INDEX idx_module_teacher (teacher_id),
    CONSTRAINT fk_module_teacher
        FOREIGN KEY (teacher_id) REFERENCES teachers(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. GRADES TABLE
-- Stores individual grades per student per module.
-- ============================================================
CREATE TABLE IF NOT EXISTS grades (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    student_id  INT             NOT NULL,
    module_id   INT             NOT NULL,
    grade       DECIMAL(5,2)    NOT NULL CHECK (grade >= 0 AND grade <= 20),
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_student_module (student_id, module_id),
    INDEX idx_grade_student (student_id),
    INDEX idx_grade_module (module_id),
    CONSTRAINT fk_grade_student
        FOREIGN KEY (student_id) REFERENCES students(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_grade_module
        FOREIGN KEY (module_id) REFERENCES modules(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SAMPLE DATA
-- Pre-hashed passwords use password_hash('password', PASSWORD_BCRYPT)
-- All sample passwords: "password"
-- ============================================================

-- Hash for "password": $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- (This is a well-known bcrypt test hash for "password")

INSERT INTO admins (name, email, password) VALUES
('Admin Principal', 'admin@university.dz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO teachers (name, email, password) VALUES
('Dr. Ahmed Benmoussa',  'ahmed.benmoussa@university.dz',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Dr. Fatima Zohra',     'fatima.zohra@university.dz',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Dr. Karim Hadj',       'karim.hadj@university.dz',       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Dr. Samira Belkacem',  'samira.belkacem@university.dz',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO students (matricule, first_name, last_name, birth_date, email, password, level) VALUES
('STU2024001', 'Mohamed',  'Benali',     '2003-05-15', 'mohamed.benali@student.dz',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'L2'),
('STU2024002', 'Amina',    'Khelifi',    '2004-01-22', 'amina.khelifi@student.dz',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'L2'),
('STU2024003', 'Youssef',  'Djellali',   '2003-09-10', 'youssef.djellali@student.dz',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'L1'),
('STU2024004', 'Sara',     'Mansouri',   '2004-03-08', 'sara.mansouri@student.dz',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'L2'),
('STU2024005', 'Rachid',   'Bouzid',     '2002-11-30', 'rachid.bouzid@student.dz',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'L3'),
('STU2024006', 'Nour',     'El Houda',   '2003-07-19', 'nour.elhouda@student.dz',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'L1'),
('STU2024007', 'Amine',    'Ferhat',     '2004-02-14', 'amine.ferhat@student.dz',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'L2'),
('STU2024008', 'Lina',     'Cherif',     '2003-12-01', 'lina.cherif@student.dz',       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'L3');

INSERT INTO modules (code, name, coefficient, teacher_id) VALUES
('MATH101', 'Analyse Mathématique',    4, 1),
('PHY101',  'Physique Générale',       3, 2),
('INFO101', 'Algorithmique',           4, 3),
('INFO102', 'Programmation Web',       3, 3),
('ENG101',  'Anglais Technique',       2, 4),
('MATH102', 'Algèbre Linéaire',        3, 1);

INSERT INTO grades (student_id, module_id, grade) VALUES
(1, 1, 15.50), (1, 2, 12.00), (1, 3, 17.00), (1, 4, 14.50), (1, 5, 16.00),
(2, 1, 14.00), (2, 2, 13.50), (2, 3, 16.00), (2, 4, 15.00), (2, 5, 18.00),
(3, 1, 10.50), (3, 2, 11.00), (3, 3, 13.00),
(4, 1, 16.50), (4, 2, 15.00), (4, 3, 18.50), (4, 4, 17.00),
(5, 1, 12.00), (5, 3, 14.00), (5, 6, 11.50),
(6, 1,  9.00), (6, 2, 10.00),
(7, 1, 15.00), (7, 3, 16.50), (7, 4, 14.00),
(8, 1, 17.00), (8, 2, 16.00), (8, 3, 19.00), (8, 6, 15.00);
