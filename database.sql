-- Anonim So'rovnoma Platformasi Database Schema

CREATE DATABASE IF NOT EXISTS anonim_survey CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE anonim_survey;

-- Foydalanuvchilar jadvali (Admin va talabalar)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'student') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Xodimlar jadvali (O'qituvchilar, Dekanlar, Koordinatorlar)
CREATE TABLE employees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    position ENUM('teacher', 'dean', 'coordinator') NOT NULL,
    department_uz VARCHAR(100) NULL COMMENT 'Kafedra nomi (Uzbek)',
    department_ru VARCHAR(100) NULL COMMENT 'Kafedra nomi (Russian)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- So'rovnoma savollari jadvali
CREATE TABLE questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    question_text TEXT NOT NULL,
    question_type ENUM('rating', 'text') DEFAULT 'rating',
    position_type ENUM('teacher', 'dean', 'coordinator', 'all') DEFAULT 'all',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- So'rovnoma javoblari jadvali
CREATE TABLE survey_responses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    question_id INT NOT NULL,
    rating INT DEFAULT NULL COMMENT '1-5 baholash',
    text_response TEXT DEFAULT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    INDEX idx_employee (employee_id),
    INDEX idx_question (question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- So'rovnoma topshirilganligini kuzatish (bir talaba bir xodimga bir marta javob bera olishi uchun)
CREATE TABLE survey_submissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    employee_id INT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_submission (user_id, employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin foydalanuvchi (username: admin, password: admin123)
INSERT INTO users (username, password, full_name, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');
-- Parol: admin123 (bcrypt hash)

-- Namuna savollar
INSERT INTO questions (question_text, question_type, position_type) VALUES
('Xodimning professional bilim darajasi qanday?', 'rating', 'all'),
('Xodimning o\'qitish uslubi qanday?', 'rating', 'teacher'),
('Xodimning talabalar bilan munosabati qanday?', 'rating', 'all'),
('Xodimning vaqtida kelishi va tayyorgarligi qanday?', 'rating', 'all'),
('Xodimning umumiy ish faoliyati qanday?', 'rating', 'all'),
('Qo\'shimcha fikr va takliflar', 'text', 'all');

