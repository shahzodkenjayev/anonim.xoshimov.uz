# Excel Fayldan SQL Buyruqlar Yaratish

## Qadamlar

### 1. Excel Faylni CSV Formatiga O'tkazish

1. Excel'da `teachers.xlsx` faylni oching
2. **File > Save As** (yoki **F12**)
3. **Save as type:** `CSV UTF-8 (Comma delimited) (*.csv)` tanlang
4. **Save** tugmasini bosing
5. Endi `teachers.csv` fayl yaratildi

### 2. CSV Fayldan SQL Buyruqlar Yaratish

#### Variant A: PHP Skript Orqali (Tavsiya)

`import_teachers.php` faylini ishga tushiring va CSV faylni yuklang.

#### Variant B: Qo'lda SQL Buyruqlar

Excel yoki CSV faylni ochib, quyidagi formatda SQL buyruqlar yarating:

```sql
-- Excel fayl struktura:
-- A ustun: Full Name
-- B ustun: Kafedra (UZ)
-- C ustun: Kafedra (RU)

INSERT INTO employees (full_name, position, department) VALUES
('Abdullayev Alisher Valiyevich', 'teacher', 'Axborot texnologiyalari'),
('Karimov Bahodir Toshmatovich', 'teacher', 'Dasturlash'),
('Toshmatov Valijon Olimovich', 'teacher', 'Kompyuter injiniringi');
```

### 3. Excel Formulasi (Agar Ko'p Ma'lumot Bo'lsa)

Excel'da D ustuniga quyidagi formulani kiriting:

```excel
="INSERT INTO employees (full_name, position, department) VALUES ('"&A2&"', 'teacher', '"&B2&"');"
```

Keyin D ustunni ko'chirib, barcha qatorlar uchun SQL buyruqlar yaratiladi.

### 4. SQL Fayl Yaratish

Yaratilgan SQL buyruqlarni `import_teachers.sql` faylga saqlang va database'ga import qiling:

```bash
mysql -u root -p anonim_survey < import_teachers.sql
```

Yoki phpMyAdmin orqali SQL faylni import qiling.

## CSV Fayl Format

CSV fayl quyidagi formatda bo'lishi kerak:

```csv
Full Name,Kafedra (UZ),Kafedra (RU)
Abdullayev Alisher Valiyevich,Axborot texnologiyalari,Информационные технологии
Karimov Bahodir Toshmatovich,Dasturlash,Программирование
```

**Muhim:**

- Birinchi qator header (o'tkazib yuboriladi)
- Har bir qatorda 3 ta ustun bo'lishi kerak
- Vergul bilan ajratilgan
- UTF-8 encoding

## PHP Skript Orqali Import

1. `import_teachers.php` faylini browser'da oching
2. CSV faylni yuklang
3. "Import Qilish" tugmasini bosing
4. Natijani ko'ring

## Xatoliklarni Hal Qilish

### CSV Encoding Muammosi

Agar o'zbek harflari to'g'ri ko'rinmasa:

1. Excel'da **File > Save As**
2. **CSV UTF-8 (Comma delimited)** tanlang
3. Yoki Notepad++ da **Encoding > Convert to UTF-8**

### Bo'sh Qatorlar

Agar bo'sh qatorlar bo'lsa, ular o'tkazib yuboriladi.

### Takrorlanuvchi Ismlar

Agar bir xil ism mavjud bo'lsa, u o'tkazib yuboriladi va xatolik ro'yxatiga qo'shiladi.
