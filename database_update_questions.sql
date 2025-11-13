-- Savollar jadvalini ikki tilga moslashtirish
-- question_text_uz va question_text_ru qo'shish

USE anonim_survey;

-- Eski question_text ustunini saqlab qolish (backup sifatida)
ALTER TABLE questions ADD COLUMN question_text_uz TEXT NULL COMMENT 'Savol matni (Uzbek)' AFTER question_text;
ALTER TABLE questions ADD COLUMN question_text_ru TEXT NULL COMMENT 'Savol matni (Russian)' AFTER question_text_uz;

-- Mavjud savollarni ko'chirish
UPDATE questions SET question_text_uz = question_text WHERE question_text_uz IS NULL;

-- Eski question_text ustunini o'chirish (yoki saqlab qolish mumkin)
-- ALTER TABLE questions DROP COLUMN question_text;

-- Yaxshiroq savollar qo'shish
-- O'qituvchilar uchun savollar
INSERT INTO questions (question_text_uz, question_text_ru, question_type, position_type) VALUES
('O\'qituvchining dars o\'tish uslubi va metodikasi qanday?', 'Какой стиль и методика преподавания у преподавателя?', 'rating', 'teacher'),
('O\'qituvchi dars materiallarini tushuntirishda qanchalik aniq va tushunarli?', 'Насколько четко и понятно преподаватель объясняет материал?', 'rating', 'teacher'),
('O\'qituvchi talabalar bilan muloqotda qanchalik do\'stona va yordamchi?', 'Насколько дружелюбен и отзывчив преподаватель в общении со студентами?', 'rating', 'teacher'),
('O\'qituvchi darslarga vaqtida keladimi va tayyorgarlik ko\'radimi?', 'Приходит ли преподаватель на занятия вовремя и готов ли к ним?', 'rating', 'teacher'),
('O\'qituvchining fan bo\'yicha bilim darajasi qanday?', 'Каков уровень знаний преподавателя по предмету?', 'rating', 'teacher'),
('O\'qituvchi talabalarning savollariga qanchalik javob bera oladi?', 'Насколько хорошо преподаватель отвечает на вопросы студентов?', 'rating', 'teacher'),
('O\'qituvchining darsda ishlatadigan texnologiyalari va materiallari qanday?', 'Какие технологии и материалы использует преподаватель на занятиях?', 'rating', 'teacher'),
('O\'qituvchi talabalarga adolatli baho beradimi?', 'Справедливо ли преподаватель оценивает студентов?', 'rating', 'teacher');

-- Dekanlar uchun savollar
INSERT INTO questions (question_text_uz, question_text_ru, question_type, position_type) VALUES
('Dekanning fakultetni boshqarish qobiliyati qanday?', 'Каковы управленческие способности декана?', 'rating', 'dean'),
('Dekan talabalar va o\'qituvchilar bilan muloqotda qanchalik ochiq va yondashuvchan?', 'Насколько открыт и доступен декан в общении со студентами и преподавателями?', 'rating', 'dean'),
('Dekan fakultet masalalarini hal qilishda qanchalik faol va samarali?', 'Насколько активно и эффективно декан решает проблемы факультета?', 'rating', 'dean'),
('Dekanning qarorlari adolatli va shaffofmi?', 'Справедливы ли и прозрачны решения декана?', 'rating', 'dean'),
('Dekan fakultet rivojlanishiga qanchalik hissa qo\'shmoqda?', 'Насколько декан способствует развитию факультета?', 'rating', 'dean'),
('Dekanning talabalar va o\'qituvchilar bilan munosabati qanday?', 'Каковы отношения декана со студентами и преподавателями?', 'rating', 'dean'),
('Dekanning umumiy professional faoliyati qanday?', 'Какова общая профессиональная деятельность декана?', 'rating', 'dean');

-- Koordinatorlar uchun savollar
INSERT INTO questions (question_text_uz, question_text_ru, question_type, position_type) VALUES
('Koordinatorning ish tashkilotchiligi va samaradorligi qanday?', 'Каковы организаторские способности и эффективность координатора?', 'rating', 'coordinator'),
('Koordinator talabalar bilan muloqotda qanchalik yordamchi va do\'stona?', 'Насколько отзывчив и дружелюбен координатор в общении со студентами?', 'rating', 'coordinator'),
('Koordinator masalalarni hal qilishda qanchalik tez va samarali?', 'Насколько быстро и эффективно координатор решает проблемы?', 'rating', 'coordinator'),
('Koordinatorning ma\'lumot berish va maslahat berish qobiliyati qanday?', 'Каковы способности координатора предоставлять информацию и консультации?', 'rating', 'coordinator'),
('Koordinatorning vaqtida javob berishi va ishlarni tashkil qilishi qanday?', 'Насколько своевременно координатор отвечает и организует работу?', 'rating', 'coordinator'),
('Koordinatorning umumiy ish faoliyati va professional yondashuvi qanday?', 'Какова общая рабочая деятельность и профессиональный подход координатора?', 'rating', 'coordinator');

-- Barcha xodimlar uchun umumiy savollar
INSERT INTO questions (question_text_uz, question_text_ru, question_type, position_type) VALUES
('Xodimning professional bilim darajasi va malakasi qanday?', 'Каков уровень профессиональных знаний и квалификации сотрудника?', 'rating', 'all'),
('Xodimning talabalar bilan munosabati qanday?', 'Каковы отношения сотрудника со студентами?', 'rating', 'all'),
('Xodimning vaqtida kelishi va tayyorgarligi qanday?', 'Насколько сотрудник пунктуален и подготовлен?', 'rating', 'all'),
('Xodimning umumiy ish faoliyati va professional yondashuvi qanday?', 'Какова общая рабочая деятельность и профессиональный подход сотрудника?', 'rating', 'all'),
('Qo\'shimcha fikr va takliflar', 'Дополнительные комментарии и предложения', 'text', 'all');

