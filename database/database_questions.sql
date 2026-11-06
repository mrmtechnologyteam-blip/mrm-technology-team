USE mrm_technology_team;

-- =========================================
-- DATABASE DEVELOPMENT QUESTIONNAIRE
-- 60 QUESTIONS / 10 SECTIONS
-- =========================================

INSERT INTO project_categories (name, description)
VALUES (
    'Database Development',
    'Database Design, Development and Management Systems'
)
ON DUPLICATE KEY UPDATE
    description = VALUES(description);

SET @db_category_id = (
    SELECT id
    FROM project_categories
    WHERE name = 'Database Development'
    LIMIT 1
);

-- =========================================
-- SECTION 1: PROJECT OVERVIEW
-- =========================================

INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@db_category_id,1,1,'هدف اصلی شما از ایجاد پایگاه داده چیست؟','textarea',NULL,1),
(@db_category_id,1,2,'پایگاه داده برای چه نوع سیستم یا سازمانی استفاده خواهد شد؟','select','["سیستم آموزشی","سیستم تجاری","سیستم اداری","سیستم مالی","سیستم صحی","سیستم فروشگاهی","سایر"]',1),
(@db_category_id,1,3,'چه نوع اطلاعاتی باید در پایگاه داده ذخیره شوند؟','textarea',NULL,1),
(@db_category_id,1,4,'چه تعداد کاربر به‌صورت تقریبی از سیستم استفاده خواهند کرد؟','select','["۱ تا ۱۰","۱۰ تا ۱۰۰","۱۰۰ تا ۱۰۰۰","۱۰۰۰ تا ۱۰۰۰۰","بیشتر از ۱۰۰۰۰","نامشخص"]',1),
(@db_category_id,1,5,'آیا سیستم یا پایگاه داده مشابهی در حال حاضر دارید؟','radio','["بله","خیر","در حال طراحی است"]',1),
(@db_category_id,1,6,'توضیحات تکمیلی درباره پروژه دیتابیس را بنویسید.','textarea',NULL,0);

-- =========================================
-- SECTION 2: DATABASE TYPE AND TECHNOLOGY
-- =========================================

INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@db_category_id,2,1,'چه نوع پایگاه داده‌ای نیاز دارید؟','checkbox','["Relational Database","NoSQL Database","Data Warehouse","Cloud Database","نمی‌دانم"]',1),
(@db_category_id,2,2,'کدام سیستم مدیریت پایگاه داده را ترجیح می‌دهید؟','select','["MySQL","PostgreSQL","Microsoft SQL Server","Oracle","MongoDB","SQLite","Redis","بدون ترجیح"]',1),
(@db_category_id,2,3,'آیا سیستم باید از چند پایگاه داده پشتیبانی کند؟','radio','["بله","خیر","نیاز به مشاوره دارم"]',1),
(@db_category_id,2,4,'پایگاه داده روی کدام سیستم‌عامل اجرا شود؟','checkbox','["Windows","Linux","macOS","Cloud Server","بدون ترجیح"]',1),
(@db_category_id,2,5,'آیا نسخه یا ویرایش مشخصی از DBMS مدنظر دارید؟','text',NULL,0),
(@db_category_id,2,6,'آیا استفاده از فناوری یا ابزار خاصی الزامی است؟','textarea',NULL,0);

-- =========================================
-- SECTION 3: DATA STRUCTURE AND DESIGN
-- =========================================

INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@db_category_id,3,1,'چه تعداد جدول یا مجموعه اطلاعاتی تقریباً نیاز دارید؟','select','["کمتر از ۱۰","۱۰ تا ۳۰","۳۰ تا ۵۰","۵۰ تا ۱۰۰","بیشتر از ۱۰۰","نامشخص"]',1),
(@db_category_id,3,2,'آیا نمودار ERD برای طراحی پایگاه داده نیاز دارید؟','radio','["بله","خیر","نمی‌دانم"]',1),
(@db_category_id,3,3,'آیا روابط بین جداول باید به‌صورت دقیق تعریف شوند؟','radio','["بله","خیر","نیاز به راهنمایی دارم"]',1),
(@db_category_id,3,4,'آیا طراحی پایگاه داده باید بر اساس اصول Normalization باشد؟','radio','["بله","خیر","نمی‌دانم"]',1),
(@db_category_id,3,5,'آیا کلیدهای اصلی و خارجی و محدودیت‌های داده باید تعریف شوند؟','radio','["بله","خیر","نیاز به مشاوره دارم"]',1),
(@db_category_id,3,6,'آیا ساختار یا نمونه‌ای از جداول موردنظر خود دارید؟','textarea',NULL,0);

-- =========================================
-- SECTION 4: DATA MANAGEMENT AND OPERATIONS
-- =========================================

INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@db_category_id,4,1,'چه عملیاتی روی اطلاعات باید انجام شود؟','checkbox','["INSERT","SELECT","UPDATE","DELETE","IMPORT","EXPORT","همه موارد"]',1),
(@db_category_id,4,2,'آیا سیستم به جستجوی پیشرفته اطلاعات نیاز دارد؟','radio','["بله","خیر"]',1),
(@db_category_id,4,3,'آیا فیلتر و مرتب‌سازی اطلاعات لازم است؟','radio','["بله","خیر"]',1),
(@db_category_id,4,4,'آیا گزارش‌گیری از اطلاعات نیاز دارید؟','radio','["بله","خیر"]',1),
(@db_category_id,4,5,'آیا سیستم باید از ثبت و پردازش حجم زیادی از اطلاعات پشتیبانی کند؟','radio','["بله","خیر","نامشخص"]',1),
(@db_category_id,4,6,'آیا عملیات خودکار مانند Stored Procedures یا Triggers نیاز دارید؟','checkbox','["Stored Procedures","Triggers","Scheduled Events","Functions","هیچ‌کدام","نمی‌دانم"]',1);

-- =========================================
-- SECTION 5: USERS AND ACCESS CONTROL
-- =========================================

INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@db_category_id,5,1,'چه تعداد کاربر یا حساب کاربری به دیتابیس دسترسی خواهند داشت؟','select','["۱ تا ۵","۵ تا ۲۰","۲۰ تا ۱۰۰","بیشتر از ۱۰۰","نامشخص"]',1),
(@db_category_id,5,2,'آیا کاربران باید سطوح دسترسی متفاوت داشته باشند؟','radio','["بله","خیر"]',1),
(@db_category_id,5,3,'چه نقش‌هایی برای کاربران در نظر گرفته‌اید؟','checkbox','["مدیر سیستم","مدیر دیتابیس","کارمند","کاربر عادی","تحلیل‌گر","سایر"]',1),
(@db_category_id,5,4,'آیا محدودیت دسترسی به جداول یا اطلاعات خاص نیاز دارید؟','radio','["بله","خیر"]',1),
(@db_category_id,5,5,'آیا ثبت فعالیت کاربران و تاریخچه تغییرات اطلاعات لازم است؟','radio','["بله","خیر"]',1),
(@db_category_id,5,6,'توضیحات مربوط به مدیریت کاربران و دسترسی‌ها را بنویسید.','textarea',NULL,0);

-- =========================================
-- SECTION 6: SECURITY AND DATA PROTECTION
-- =========================================

INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@db_category_id,6,1,'سطح امنیت موردنیاز پایگاه داده چیست؟','select','["معمولی","بالا","بسیار بالا","نیاز به مشاوره دارم"]',1),
(@db_category_id,6,2,'آیا اطلاعات محرمانه یا حساس ذخیره خواهند شد؟','radio','["بله","خیر","بعضی اطلاعات"]',1),
(@db_category_id,6,3,'آیا رمزگذاری اطلاعات حساس نیاز دارید؟','radio','["بله","خیر","نیاز به مشاوره دارم"]',1),
(@db_category_id,6,4,'آیا محدودسازی دسترسی بر اساس IP یا شبکه لازم است؟','radio','["بله","خیر","نمی‌دانم"]',1),
(@db_category_id,6,5,'آیا ثبت گزارش فعالیت‌ها و رویدادهای امنیتی نیاز دارید؟','radio','["بله","خیر"]',1),
(@db_category_id,6,6,'آیا استاندارد یا الزام امنیتی خاصی باید رعایت شود؟','textarea',NULL,0);

-- =========================================
-- SECTION 7: BACKUP AND RECOVERY
-- =========================================

INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@db_category_id,7,1,'آیا تهیه نسخه پشتیبان از پایگاه داده ضروری است؟','radio','["بله","خیر"]',1),
(@db_category_id,7,2,'نسخه پشتیبان با چه فاصله زمانی تهیه شود؟','select','["هر ساعت","روزانه","هفتگی","ماهانه","به‌صورت دستی"]',1),
(@db_category_id,7,3,'نسخه‌های پشتیبان در کجا ذخیره شوند؟','checkbox','["سرور محلی","سرور جداگانه","Cloud Storage","هارد خارجی","چند مکان"]',1),
(@db_category_id,7,4,'آیا بازیابی اطلاعات پس از خرابی سیستم لازم است؟','radio','["بله","خیر"]',1),
(@db_category_id,7,5,'چه مدت باید نسخه‌های پشتیبان نگهداری شوند؟','select','["۷ روز","۳۰ روز","۹۰ روز","یک سال","دائمی","نامشخص"]',1),
(@db_category_id,7,6,'آیا انتقال اطلاعات از سیستم قدیمی به دیتابیس جدید نیاز دارید؟','radio','["بله","خیر","اطمینان ندارم"]',1);

-- =========================================
-- SECTION 8: PERFORMANCE AND SCALABILITY
-- =========================================

INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@db_category_id,8,1,'سرعت پاسخ‌گویی پایگاه داده تا چه اندازه اهمیت دارد؟','select','["معمولی","بالا","بسیار بالا"]',1),
(@db_category_id,8,2,'آیا بهینه‌سازی Queryها نیاز دارید؟','radio','["بله","خیر","نمی‌دانم"]',1),
(@db_category_id,8,3,'آیا ایجاد Indexهای اختصاصی برای اطلاعات لازم است؟','radio','["بله","خیر","نیاز به مشاوره دارم"]',1),
(@db_category_id,8,4,'آیا چندین کاربر باید هم‌زمان از دیتابیس استفاده کنند؟','radio','["بله","خیر"]',1),
(@db_category_id,8,5,'آیا سیستم باید در آینده قابلیت توسعه و افزایش ظرفیت داشته باشد؟','radio','["بله","خیر"]',1),
(@db_category_id,8,6,'آیا محدودیت مشخصی برای زمان پاسخ‌گویی یا حجم پردازش دارید؟','textarea',NULL,0);

-- =========================================
-- SECTION 9: INTEGRATION AND DEPLOYMENT
-- =========================================

INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@db_category_id,9,1,'آیا پایگاه داده باید به نرم‌افزار یا وب‌سایت متصل شود؟','radio','["بله","خیر"]',1),
(@db_category_id,9,2,'چه سیستم‌هایی باید به پایگاه داده متصل شوند؟','checkbox','["وب‌سایت","اپلیکیشن موبایل","نرم‌افزار دسکتاپ","سیستم‌های سازمانی","API","سایر"]',1),
(@db_category_id,9,3,'آیا ایجاد API برای دسترسی به اطلاعات نیاز دارید؟','radio','["بله","خیر","نمی‌دانم"]',1),
(@db_category_id,9,4,'پایگاه داده در کجا نصب و اجرا شود؟','select','["سرور اختصاصی","هاست اشتراکی","Cloud Server","شبکه داخلی سازمان","کامپیوتر شخصی","نیاز به مشاوره دارم"]',1),
(@db_category_id,9,5,'آیا انتقال دیتابیس به سرور یا محیط دیگری در آینده نیاز است؟','radio','["بله","خیر","نامشخص"]',1),
(@db_category_id,9,6,'آیا مستندات فنی و راهنمای نصب و نگهداری لازم دارید؟','radio','["بله","خیر"]',1);

-- =========================================
-- SECTION 10: BUDGET, TIMELINE AND FINAL DETAILS
-- =========================================

INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@db_category_id,10,1,'بودجه تقریبی شما برای توسعه پایگاه داده چقدر است؟','number',NULL,0),
(@db_category_id,10,2,'چه زمانی برای تکمیل پروژه در نظر دارید؟','select','["کمتر از یک ماه","۱ تا ۳ ماه","۳ تا ۶ ماه","بیشتر از ۶ ماه","قابل مذاکره"]',1),
(@db_category_id,10,3,'آیا تاریخ مشخصی برای تحویل پروژه دارید؟','date',NULL,0),
(@db_category_id,10,4,'آیا آموزش مدیریت و نگهداری پایگاه داده نیاز دارید؟','radio','["بله","خیر"]',1),
(@db_category_id,10,5,'آیا فایل‌ها، مستندات یا اطلاعات اولیه برای شروع پروژه دارید؟','radio','["بله","خیر","بعداً ارائه می‌کنم"]',1),
(@db_category_id,10,6,'توضیحات نهایی و نیازمندی‌های خاص خود را بنویسید.','textarea',NULL,0);

-- =========================================
-- END: 60 DATABASE QUESTIONS
-- =========================================
