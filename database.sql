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
    question_text TEXT NULL COMMENT 'Eski format (backup)',
    question_text_uz TEXT NOT NULL COMMENT 'Savol matni (Uzbek)',
    question_text_ru TEXT NULL COMMENT 'Savol matni (Russian)',
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
('admin', '$2y$10$/n3TI7ZQuSPQsDjT0YzGPefOpq0h25PaDvCuBnhir4A.kHk1Nml72', 'Administrator', 'admin');
-- Parol: admin123 (bcrypt hash)

-- Namuna savollar - O'qituvchilar uchun
INSERT INTO questions (question_text_uz, question_text_ru, question_type, position_type) VALUES
('O\'qituvchining dars o\'tish uslubi va metodikasi qanday?', 'Какой стиль и методика преподавания у преподавателя?', 'rating', 'teacher'),
('O\'qituvchi dars materiallarini tushuntirishda qanchalik aniq va tushunarli?', 'Насколько четко и понятно преподаватель объясняет материал?', 'rating', 'teacher'),
('O\'qituvchi talabalar bilan muloqotda qanchalik do\'stona va yordamchi?', 'Насколько дружелюбен и отзывчив преподаватель в общении со студентами?', 'rating', 'teacher'),
('O\'qituvchi darslarga vaqtida keladimi va tayyorgarlik ko\'radimi?', 'Приходит ли преподаватель на занятия вовремя и готов ли к ним?', 'rating', 'teacher'),
('O\'qituvchining fan bo\'yicha bilim darajasi qanday?', 'Каков уровень знаний преподавателя по предмету?', 'rating', 'teacher');

-- Namuna savollar - Dekanlar uchun
INSERT INTO questions (question_text_uz, question_text_ru, question_type, position_type) VALUES
('Dekanning fakultetni boshqarish qobiliyati qanday?', 'Каковы управленческие способности декана?', 'rating', 'dean'),
('Dekan talabalar va o\'qituvchilar bilan muloqotda qanchalik ochiq va yondashuvchan?', 'Насколько открыт и доступен декан в общении со студентами и преподавателями?', 'rating', 'dean'),
('Dekan fakultet masalalarini hal qilishda qanchalik faol va samarali?', 'Насколько активно и эффективно декан решает проблемы факультета?', 'rating', 'dean'),
('Dekanning qarorlari adolatli va shaffofmi?', 'Справедливы ли и прозрачны решения декана?', 'rating', 'dean');

-- Namuna savollar - Koordinatorlar uchun
INSERT INTO questions (question_text_uz, question_text_ru, question_type, position_type) VALUES
('Koordinatorning ish tashkilotchiligi va samaradorligi qanday?', 'Каковы организаторские способности и эффективность координатора?', 'rating', 'coordinator'),
('Koordinator talabalar bilan muloqotda qanchalik yordamchi va do\'stona?', 'Насколько отзывчив и дружелюбен координатор в общении со студентами?', 'rating', 'coordinator'),
('Koordinator masalalarni hal qilishda qanchalik tez va samarali?', 'Насколько быстро и эффективно координатор решает проблемы?', 'rating', 'coordinator'),
('Koordinatorning ma\'lumot berish va maslahat berish qobiliyati qanday?', 'Каковы способности координатора предоставлять информацию и консультации?', 'rating', 'coordinator');

-- Namuna savollar - Barcha xodimlar uchun
INSERT INTO questions (question_text_uz, question_text_ru, question_type, position_type) VALUES
('Xodimning professional bilim darajasi va malakasi qanday?', 'Каков уровень профессиональных знаний и квалификации сотрудника?', 'rating', 'all'),
('Xodimning talabalar bilan munosabati qanday?', 'Каковы отношения сотрудника со студентами?', 'rating', 'all'),
('Xodimning vaqtida kelishi va tayyorgarligi qanday?', 'Насколько сотрудник пунктуален и подготовлен?', 'rating', 'all'),
('Xodimning umumiy ish faoliyati va professional yondashuvi qanday?', 'Какова общая рабочая деятельность и профессиональный подход сотрудника?', 'rating', 'all'),
('Qo\'shimcha fikr va takliflar', 'Дополнительные комментарии и предложения', 'text', 'all');

