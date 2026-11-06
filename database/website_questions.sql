USE mrm_technology_team;

-- =========================================
-- WEBSITE DEVELOPMENT QUESTIONNAIRE
-- 60 QUESTIONS / 10 SECTIONS
-- =========================================

INSERT INTO project_categories (name, description)
VALUES (
    'Website Development',
    'Professional Website Design, Development and Management'
)
ON DUPLICATE KEY UPDATE
    description = VALUES(description);

SET @web_category_id = (
    SELECT id
    FROM project_categories
    WHERE name = 'Website Development'
    LIMIT 1
);

-- SECTION 1: PROJECT OVERVIEW
INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@web_category_id,1,1,'هدف اصلی شما از ایجاد وب‌سایت چیست؟','select','["معرفی شرکت یا سازمان","فروشگاه آنلاین","وب‌سایت آموزشی","وب‌سایت خبری","وب‌سایت شخصی یا نمونه‌کار","سامانه آنلاین","سایر"]',1),
(@web_category_id,1,2,'نام وب‌سایت یا برند شما چیست؟','text',NULL,1),
(@web_category_id,1,3,'مخاطبان اصلی وب‌سایت چه کسانی هستند؟','textarea',NULL,1),
(@web_category_id,1,4,'آیا وب‌سایت مشابهی برای نمونه در نظر دارید؟','radio','["بله","خیر"]',1),
(@web_category_id,1,5,'آیا در حال حاضر وب‌سایت فعال دارید؟','radio','["بله","خیر","در حال طراحی است"]',1),
(@web_category_id,1,6,'توضیحات تکمیلی درباره هدف و ایده وب‌سایت را بنویسید.','textarea',NULL,0)
ON DUPLICATE KEY UPDATE
    question_text = VALUES(question_text),
    question_type = VALUES(question_type),
    options_json = VALUES(options_json),
    is_required = VALUES(is_required);

-- SECTION 2: WEBSITE STRUCTURE AND PAGES
INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@web_category_id,2,1,'چه صفحاتی در وب‌سایت نیاز دارید؟','checkbox','["صفحه اصلی","درباره ما","خدمات","محصولات","وبلاگ","تماس با ما","ورود و ثبت‌نام","داشبورد کاربران","سایر"]',1),
(@web_category_id,2,2,'تقریباً چند صفحه برای وب‌سایت در نظر دارید؟','select','["۱ تا ۵ صفحه","۶ تا ۱۰ صفحه","۱۱ تا ۲۰ صفحه","۲۱ تا ۵۰ صفحه","بیشتر از ۵۰ صفحه","نامشخص"]',1),
(@web_category_id,2,3,'آیا وب‌سایت به پنل مدیریت اختصاصی نیاز دارد؟','radio','["بله","خیر","نیاز به مشاوره دارم"]',1),
(@web_category_id,2,4,'آیا کاربران باید حساب کاربری داشته باشند؟','radio','["بله","خیر"]',1),
(@web_category_id,2,5,'آیا ساختار منوها و دسته‌بندی صفحات مشخص شده است؟','radio','["بله","خیر","تا حدی"]',1),
(@web_category_id,2,6,'صفحات یا بخش‌های اختصاصی موردنیاز خود را توضیح دهید.','textarea',NULL,0)
ON DUPLICATE KEY UPDATE
    question_text = VALUES(question_text),
    question_type = VALUES(question_type),
    options_json = VALUES(options_json),
    is_required = VALUES(is_required);

-- SECTION 3: DESIGN AND USER EXPERIENCE
INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@web_category_id,3,1,'چه سبک طراحی برای وب‌سایت ترجیح می‌دهید؟','select','["مدرن و مینیمال","رسمی و شرکتی","خلاقانه و رنگارنگ","لوکس و حرفه‌ای","ساده و کلاسیک","بدون ترجیح"]',1),
(@web_category_id,3,2,'رنگ‌های اصلی برند یا وب‌سایت شما کدام‌اند؟','text',NULL,0),
(@web_category_id,3,3,'آیا لوگو و هویت بصری آماده دارید؟','radio','["بله","خیر","نیاز به طراحی دارم"]',1),
(@web_category_id,3,4,'آیا طراحی وب‌سایت باید کاملاً واکنش‌گرا باشد؟','radio','["بله، برای موبایل و تبلت و دسکتاپ","فقط موبایل و دسکتاپ","نیاز به مشاوره دارم"]',1),
(@web_category_id,3,5,'آیا انیمیشن و جلوه‌های بصری در وب‌سایت نیاز دارید؟','checkbox','["انیمیشن هنگام اسکرول","افکت‌های حرکتی","اسلایدر و بنر متحرک","تعاملات پیشرفته","بدون انیمیشن"]',1),
(@web_category_id,3,6,'لینک نمونه وب‌سایت یا توضیحات طراحی موردعلاقه خود را بنویسید.','textarea',NULL,0)
ON DUPLICATE KEY UPDATE
    question_text = VALUES(question_text),
    question_type = VALUES(question_type),
    options_json = VALUES(options_json),
    is_required = VALUES(is_required);

-- SECTION 4: FEATURES AND FUNCTIONALITY
INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@web_category_id,4,1,'کدام قابلیت‌ها را در وب‌سایت نیاز دارید؟','checkbox','["جستجوی پیشرفته","فرم تماس","سیستم نظرات","سیستم امتیازدهی","چت آنلاین","عضویت کاربران","مدیریت محتوا","سایر"]',1),
(@web_category_id,4,2,'آیا وب‌سایت به فروش آنلاین و سبد خرید نیاز دارد؟','radio','["بله","خیر"]',1),
(@web_category_id,4,3,'آیا امکان رزرو یا ثبت سفارش لازم است؟','radio','["بله","خیر"]',1),
(@web_category_id,4,4,'آیا کاربران باید بتوانند فایل یا تصویر بارگذاری کنند؟','radio','["بله","خیر"]',1),
(@web_category_id,4,5,'آیا اعلان‌های ایمیلی یا پیامکی نیاز دارید؟','checkbox','["ایمیل","پیامک","اعلان داخل وب‌سایت","WhatsApp","هیچ‌کدام"]',1),
(@web_category_id,4,6,'قابلیت‌های اختصاصی دیگری که نیاز دارید را توضیح دهید.','textarea',NULL,0)
ON DUPLICATE KEY UPDATE
    question_text = VALUES(question_text),
    question_type = VALUES(question_type),
    options_json = VALUES(options_json),
    is_required = VALUES(is_required);

-- SECTION 5: USERS AND CONTENT MANAGEMENT
INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@web_category_id,5,1,'چه نوع کاربرانی از وب‌سایت استفاده خواهند کرد؟','checkbox','["مدیر","کارمند","مشتری","نویسنده","بازدیدکننده","سایر"]',1),
(@web_category_id,5,2,'آیا کاربران باید نقش‌ها و سطح دسترسی متفاوت داشته باشند؟','radio','["بله","خیر"]',1),
(@web_category_id,5,3,'چه نوع محتوایی در وب‌سایت منتشر خواهد شد؟','checkbox','["متن","تصویر","ویدئو","فایل قابل دانلود","محصولات","مقالات","سایر"]',1),
(@web_category_id,5,4,'آیا مدیریت محتوا باید از طریق پنل اختصاصی انجام شود؟','radio','["بله","خیر","نیاز به مشاوره دارم"]',1),
(@web_category_id,5,5,'چه تعداد مدیر یا ویرایشگر محتوا خواهید داشت؟','select','["۱ نفر","۲ تا ۵ نفر","۶ تا ۱۰ نفر","بیشتر از ۱۰ نفر","نامشخص"]',1),
(@web_category_id,5,6,'توضیحات مربوط به کاربران و مدیریت محتوا را بنویسید.','textarea',NULL,0)
ON DUPLICATE KEY UPDATE
    question_text = VALUES(question_text),
    question_type = VALUES(question_type),
    options_json = VALUES(options_json),
    is_required = VALUES(is_required);

-- SECTION 6: TECHNOLOGY AND INTEGRATION
INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@web_category_id,6,1,'آیا فناوری یا زبان برنامه‌نویسی خاصی مدنظر دارید؟','checkbox','["PHP","Laravel","JavaScript","React","Node.js","WordPress","بدون ترجیح","سایر"]',1),
(@web_category_id,6,2,'آیا وب‌سایت باید به پایگاه داده متصل شود؟','radio','["بله","خیر","نمی‌دانم"]',1),
(@web_category_id,6,3,'آیا اتصال به سرویس‌ها یا سیستم‌های خارجی نیاز دارید؟','checkbox','["درگاه پرداخت","سرویس ایمیل","WhatsApp","Google Maps","شبکه‌های اجتماعی","API خارجی","سایر","هیچ‌کدام"]',1),
(@web_category_id,6,4,'آیا وب‌سایت به API اختصاصی نیاز دارد؟','radio','["بله","خیر","نیاز به مشاوره دارم"]',1),
(@web_category_id,6,5,'آیا چندزبانه بودن وب‌سایت ضروری است؟','radio','["بله","خیر"]',1),
(@web_category_id,6,6,'زبان‌های موردنیاز وب‌سایت را مشخص کنید.','checkbox','["فارسی","دری","پشتو","انگلیسی","عربی","آلمانی","سایر"]',0)
ON DUPLICATE KEY UPDATE
    question_text = VALUES(question_text),
    question_type = VALUES(question_type),
    options_json = VALUES(options_json),
    is_required = VALUES(is_required);

-- SECTION 7: SECURITY AND PRIVACY
INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@web_category_id,7,1,'سطح امنیت موردنیاز وب‌سایت چیست؟','select','["معمولی","بالا","بسیار بالا","نیاز به مشاوره دارم"]',1),
(@web_category_id,7,2,'آیا اطلاعات شخصی کاربران در وب‌سایت ذخیره می‌شود؟','radio','["بله","خیر","احتمالاً"]',1),
(@web_category_id,7,3,'آیا سیستم ورود امن و بازیابی رمز عبور نیاز دارید؟','radio','["بله","خیر"]',1),
(@web_category_id,7,4,'آیا تأیید هویت دومرحله‌ای برای کاربران لازم است؟','radio','["بله","خیر","اختیاری"]',1),
(@web_category_id,7,5,'آیا ثبت فعالیت مدیران و کاربران برای بررسی امنیتی نیاز است؟','radio','["بله","خیر"]',1),
(@web_category_id,7,6,'الزامات امنیتی یا حریم خصوصی خاص خود را توضیح دهید.','textarea',NULL,0)
ON DUPLICATE KEY UPDATE
    question_text = VALUES(question_text),
    question_type = VALUES(question_type),
    options_json = VALUES(options_json),
    is_required = VALUES(is_required);

-- SECTION 8: SEO AND PERFORMANCE
INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@web_category_id,8,1,'آیا بهینه‌سازی وب‌سایت برای موتورهای جستجو (SEO) نیاز دارید؟','radio','["بله","خیر","نیاز به مشاوره دارم"]',1),
(@web_category_id,8,2,'آیا وب‌سایت باید به Google Analytics متصل شود؟','radio','["بله","خیر","نمی‌دانم"]',1),
(@web_category_id,8,3,'سرعت بارگذاری وب‌سایت تا چه اندازه اهمیت دارد؟','select','["معمولی","بالا","بسیار بالا"]',1),
(@web_category_id,8,4,'آیا بهینه‌سازی تصاویر و فایل‌های وب‌سایت لازم است؟','radio','["بله","خیر"]',1),
(@web_category_id,8,5,'آیا وب‌سایت باید از تعداد زیادی بازدیدکننده هم‌زمان پشتیبانی کند؟','radio','["بله","خیر","نامشخص"]',1),
(@web_category_id,8,6,'محدودیت یا هدف خاصی برای سرعت و عملکرد وب‌سایت دارید؟','textarea',NULL,0)
ON DUPLICATE KEY UPDATE
    question_text = VALUES(question_text),
    question_type = VALUES(question_type),
    options_json = VALUES(options_json),
    is_required = VALUES(is_required);

-- SECTION 9: DOMAIN, HOSTING AND DEPLOYMENT
INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@web_category_id,9,1,'آیا دامنه اینترنتی برای وب‌سایت دارید؟','radio','["بله","خیر","نیاز به راهنمایی دارم"]',1),
(@web_category_id,9,2,'نام دامنه موردنظر شما چیست؟','text',NULL,0),
(@web_category_id,9,3,'آیا فضای میزبانی (Hosting) تهیه کرده‌اید؟','radio','["بله","خیر","نیاز به مشاوره دارم"]',1),
(@web_category_id,9,4,'چه نوع میزبانی برای پروژه در نظر دارید؟','select','["هاست اشتراکی","VPS","سرور اختصاصی","Cloud Hosting","بدون ترجیح"]',1),
(@web_category_id,9,5,'آیا نصب SSL و فعال‌سازی HTTPS ضروری است؟','radio','["بله","خیر","نمی‌دانم"]',1),
(@web_category_id,9,6,'آیا انتقال وب‌سایت فعلی به سرور جدید نیاز دارید؟','radio','["بله","خیر","وب‌سایت فعلی ندارم"]',1)
ON DUPLICATE KEY UPDATE
    question_text = VALUES(question_text),
    question_type = VALUES(question_type),
    options_json = VALUES(options_json),
    is_required = VALUES(is_required);

-- SECTION 10: BUDGET, TIMELINE AND FINAL DETAILS
INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@web_category_id,10,1,'بودجه تقریبی شما برای توسعه وب‌سایت چقدر است؟','number',NULL,0),
(@web_category_id,10,2,'چه زمانی برای تکمیل وب‌سایت در نظر دارید؟','select','["کمتر از یک ماه","۱ تا ۳ ماه","۳ تا ۶ ماه","بیشتر از ۶ ماه","قابل مذاکره"]',1),
(@web_category_id,10,3,'آیا تاریخ مشخصی برای تحویل پروژه دارید؟','date',NULL,0),
(@web_category_id,10,4,'آیا پس از تحویل، پشتیبانی و نگهداری وب‌سایت نیاز دارید؟','radio','["بله","خیر","نیاز به مشاوره دارم"]',1),
(@web_category_id,10,5,'آیا محتوا، تصاویر و فایل‌های موردنیاز وب‌سایت آماده هستند؟','radio','["بله","خیر","بخشی آماده است"]',1),
(@web_category_id,10,6,'سایر نیازمندی‌ها و توضیحات نهایی پروژه را بنویسید.','textarea',NULL,0)
ON DUPLICATE KEY UPDATE
    question_text = VALUES(question_text),
    question_type = VALUES(question_type),
    options_json = VALUES(options_json),
    is_required = VALUES(is_required);

-- END: 60 WEBSITE QUESTIONS
