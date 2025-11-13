-- Koordinatorlarni bazaga qo'shish SQL buyruqlari

USE anonim_survey;

-- Axborot tizimlari va texnologiyalari fakulteti koordinatori
INSERT INTO employees (full_name, position, department_uz, department_ru) 
VALUES ('Bekzod Xayritdinovich', 'coordinator', 'Axborot tizimlari va texnologiyalari fakulteti', 'Факультет информационных систем и технологий')
ON DUPLICATE KEY UPDATE 
    position = 'coordinator',
    department_uz = 'Axborot tizimlari va texnologiyalari fakulteti',
    department_ru = 'Факультет информационных систем и технологий';

-- Turizm fakulteti koordinatori
INSERT INTO employees (full_name, position, department_uz, department_ru) 
VALUES ('Quvonch Jumadullayevich', 'coordinator', 'Turizm fakulteti', 'Факультет туризма')
ON DUPLICATE KEY UPDATE 
    position = 'coordinator',
    department_uz = 'Turizm fakulteti',
    department_ru = 'Факультет туризма';

-- Qo'shilgan koordinatorlarni ko'rsatish
SELECT id, full_name, position, department_uz, department_ru 
FROM employees 
WHERE position = 'coordinator' 
ORDER BY full_name;

