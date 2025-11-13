-- Kafedra nomini uz va ru versiyalarda saqlash uchun database yangilash

USE anonim_survey;

-- Eski department ustunini department_uz ga o'zgartirish
ALTER TABLE employees 
CHANGE COLUMN department department_uz VARCHAR(100) NULL COMMENT 'Kafedra nomi (Uzbek)';

-- Yangi department_ru ustunini qo'shish
ALTER TABLE employees 
ADD COLUMN department_ru VARCHAR(100) NULL COMMENT 'Kafedra nomi (Russian)' AFTER department_uz;

-- Mavjud ma'lumotlarni department_uz ga ko'chirish (agar department bo'lsa)
UPDATE employees SET department_uz = department_uz WHERE department_uz IS NOT NULL;

