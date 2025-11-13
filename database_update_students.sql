-- Talabalar ma'lumotlari uchun users jadvalini yangilash

USE anonim_survey;

-- users jadvaliga yangi ustunlar qo'shish
ALTER TABLE users 
ADD COLUMN hemis_id VARCHAR(50) NULL UNIQUE COMMENT 'HEMIS ID (student_id_number)' AFTER username,
ADD COLUMN faculty VARCHAR(200) NULL COMMENT 'Fakultet nomi' AFTER full_name,
ADD COLUMN group_name VARCHAR(100) NULL COMMENT 'Guruh nomi' AFTER faculty;

-- Index qo'shish (tez qidirish uchun)
CREATE INDEX idx_hemis_id ON users(hemis_id);

