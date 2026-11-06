USE mrm_technology_team;

-- =========================================
-- APPLICATION DEVELOPMENT QUESTIONNAIRE
-- 60 QUESTIONS / 10 SECTIONS
-- =========================================

INSERT INTO project_categories (name, description)
VALUES (
    'Application Development',
    'Android, iOS and Web Applications'
)
ON DUPLICATE KEY UPDATE
    description = VALUES(description);

SET @app_category_id = (
    SELECT id
    FROM project_categories
    WHERE name = 'Application Development'
    LIMIT 1
);

-- =========================================
-- SECTION 1: PROJECT OVERVIEW
-- =========================================

INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@app_category_id,1,1,'هدف اصلی شما از ساخت اپلیکیشن چیست؟','textarea',NULL,1),
(@app_category_id,1,2,'اپلیکیشن برای چه کسانی ساخته می‌شود؟','text',NULL,1),
(@app_category_id,1,3,'اپلیکیشن در کدام حوزه فعالیت خواهد کرد؟','select','["آموزشی","تجاری","خدماتی","سلامت","مالی","اجتماعی","سرگرمی","سایر"]',1),
(@app_category_id,1,4,'آیا نمونه مشابهی از اپلیکیشن موردنظر شما وجود دارد؟','radio','["بله","خیر","اطمینان ندارم"]',1),
(@app_category_id,1,5,'نام پیشنهادی اپلیکیشن چیست؟','text',NULL,0),
(@app_category_id,1,6,'توضیحات تکمیلی درباره ایده اپلیکیشن را بنویسید.','textarea',NULL,0)
ON DUPLICATE KEY UPDATE
    question_text = VALUES(question_text),
    question_type = VALUES(question_type),
    options_json = VALUES(options_json),
    is_required = VALUES(is_required);

-- =========================================
-- SECTION 2: PLATFORM AND DEVICES
-- =========================================

INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@app_category_id,2,1,'اپلیکیشن برای کدام سیستم‌عامل ساخته شود؟','checkbox','["Android","iOS","هر دو"]',1),
(@app_category_id,2,2,'آیا نسخه وب اپلیکیشن نیز نیاز دارید؟','radio','["بله","خیر","در آینده"]',1),
(@app_category_id,2,3,'اپلیکیشن باید روی چه دستگاه‌هایی اجرا شود؟','checkbox','["موبایل","تبلت","کامپیوتر","سایر"]',1),
(@app_category_id,2,4,'آیا اپلیکیشن باید روی نسخه‌های قدیمی سیستم‌عامل نیز کار کند؟','radio','["بله","خیر","مهم نیست"]',1),
(@app_category_id,2,5,'حداقل نسخه Android یا iOS موردنظر شما چیست؟','text',NULL,0),
(@app_category_id,2,6,'آیا طراحی واکنش‌گرا برای اندازه‌های مختلف صفحه نیاز است؟','radio','["بله","خیر"]',1)
ON DUPLICATE KEY UPDATE
    question_text = VALUES(question_text),
    question_type = VALUES(question_type),
    options_json = VALUES(options_json),
    is_required = VALUES(is_required);

-- =========================================
-- SECTION 3: USERS AND ACCOUNTS
-- =========================================

INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@app_category_id,3,1,'آیا کاربران باید در اپلیکیشن ثبت‌نام کنند؟','radio','["بله","خیر"]',1),
(@app_category_id,3,2,'روش ورود کاربران چگونه باشد؟','checkbox','["ایمیل","شماره موبایل","نام کاربری و رمز عبور","Google","Apple","سایر"]',1),
(@app_category_id,3,3,'آیا تأیید شماره موبایل یا ایمیل لازم است؟','checkbox','["تأیید ایمیل","تأیید شماره موبایل","هر دو","هیچ‌کدام"]',1),
(@app_category_id,3,4,'آیا کاربران باید پروفایل شخصی داشته باشند؟','radio','["بله","خیر"]',1),
(@app_category_id,3,5,'چه نوع نقش‌هایی در اپلیکیشن وجود داشته باشد؟','checkbox','["مدیر","کاربر عادی","کارمند","مشتری","فروشنده","سایر"]',1),
(@app_category_id,3,6,'آیا کاربران باید بتوانند رمز عبور خود را بازیابی کنند؟','radio','["بله","خیر"]',1)
ON DUPLICATE KEY UPDATE
    question_text = VALUES(question_text),
    question_type = VALUES(question_type),
    options_json = VALUES(options_json),
    is_required = VALUES(is_required);

-- =========================================
-- SECTION 4: FEATURES AND FUNCTIONALITY
-- =========================================

INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@app_category_id,4,1,'مهم‌ترین قابلیت‌های اپلیکیشن چیست؟','textarea',NULL,1),
(@app_category_id,4,2,'آیا اپلیکیشن به سیستم جستجو نیاز دارد؟','radio','["بله","خیر"]',1),
(@app_category_id,4,3,'آیا کاربران باید بتوانند اطلاعات یا فایل ارسال کنند؟','checkbox','["تصویر","ویدیو","فایل PDF","اسناد","صدا","خیر"]',1),
(@app_category_id,4,4,'آیا امکان ثبت، ویرایش و حذف اطلاعات لازم است؟','radio','["بله","خیر"]',1),
(@app_category_id,4,5,'آیا اپلیکیشن به سیستم پرداخت آنلاین نیاز دارد؟','radio','["بله","خیر","در آینده"]',1),
(@app_category_id,4,6,'آیا کاربران باید بتوانند با یکدیگر ارتباط برقرار کنند؟','radio','["بله","خیر"]',1)
ON DUPLICATE KEY UPDATE
    question_text = VALUES(question_text),
    question_type = VALUES(question_type),
    options_json = VALUES(options_json),
    is_required = VALUES(is_required);

-- =========================================
-- SECTION 5: DESIGN AND USER EXPERIENCE
-- =========================================

INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@app_category_id,5,1,'چه سبک طراحی را ترجیح می‌دهید؟','select','["مدرن","مینیمال","رسمی و تجاری","خلاقانه","ساده","سایر"]',1),
(@app_category_id,5,2,'رنگ‌های اصلی اپلیکیشن چه باشند؟','text',NULL,0),
(@app_category_id,5,3,'آیا لوگوی آماده دارید؟','radio','["بله","خیر","نیاز به طراحی دارم"]',1),
(@app_category_id,5,4,'زبان‌های موردنیاز اپلیکیشن کدام‌اند؟','checkbox','["فارسی","دری","پشتو","انگلیسی","عربی","سایر"]',1),
(@app_category_id,5,5,'آیا اپلیکیشن باید از حالت تاریک و روشن پشتیبانی کند؟','radio','["بله","خیر"]',1),
(@app_category_id,5,6,'آیا نمونه طراحی یا اپلیکیشن مرجع دارید؟ آدرس یا توضیحات آن را بنویسید.','textarea',NULL,0)
ON DUPLICATE KEY UPDATE
    question_text = VALUES(question_text),
    question_type = VALUES(question_type),
    options_json = VALUES(options_json),
    is_required = VALUES(is_required);

-- =========================================
-- SECTION 6: DATABASE AND BACKEND
-- =========================================

INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@app_category_id,6,1,'آیا اپلیکیشن به پایگاه داده نیاز دارد؟','radio','["بله","خیر","نمی‌دانم"]',1),
(@app_category_id,6,2,'آیا سرور یا هاست آماده دارید؟','radio','["بله","خیر","نیاز به راهنمایی دارم"]',1),
(@app_category_id,6,3,'آیا اپلیکیشن باید اطلاعات را بین دستگاه‌ها همگام‌سازی کند؟','radio','["بله","خیر"]',1),
(@app_category_id,6,4,'چه نوع اطلاعاتی باید ذخیره شوند؟','textarea',NULL,1),
(@app_category_id,6,5,'آیا پنل مدیریت برای کنترل اطلاعات نیاز دارید؟','radio','["بله","خیر"]',1),
(@app_category_id,6,6,'آیا API یا سیستم خارجی باید به اپلیکیشن متصل شود؟','radio','["بله","خیر","نمی‌دانم"]',1)
ON DUPLICATE KEY UPDATE
    question_text = VALUES(question_text),
    question_type = VALUES(question_type),
    options_json = VALUES(options_json),
    is_required = VALUES(is_required);

-- =========================================
-- SECTION 7: SECURITY AND PRIVACY
-- =========================================

INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@app_category_id,7,1,'آیا اطلاعات کاربران محرمانه هستند؟','radio','["بله","خیر","بعضی اطلاعات"]',1),
(@app_category_id,7,2,'آیا دسترسی کاربران باید بر اساس نقش محدود شود؟','radio','["بله","خیر"]',1),
(@app_category_id,7,3,'آیا ورود دومرحله‌ای نیاز دارید؟','radio','["بله","خیر","در آینده"]',1),
(@app_category_id,7,4,'آیا اطلاعات باید رمزگذاری شوند؟','radio','["بله","خیر","نیاز به مشاوره دارم"]',1),
(@app_category_id,7,5,'آیا تهیه نسخه پشتیبان از اطلاعات لازم است؟','radio','["بله","خیر"]',1),
(@app_category_id,7,6,'آیا الزامات امنیتی یا حریم خصوصی خاصی دارید؟','textarea',NULL,0)
ON DUPLICATE KEY UPDATE
    question_text = VALUES(question_text),
    question_type = VALUES(question_type),
    options_json = VALUES(options_json),
    is_required = VALUES(is_required);

-- =========================================
-- SECTION 8: NOTIFICATIONS AND INTEGRATIONS
-- =========================================

INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@app_category_id,8,1,'آیا اپلیکیشن به سیستم اعلان نیاز دارد؟','radio','["بله","خیر"]',1),
(@app_category_id,8,2,'اعلان‌ها از چه طریقی ارسال شوند؟','checkbox','["اعلان داخل اپلیکیشن","Push Notification","ایمیل","پیامک"]',1),
(@app_category_id,8,3,'آیا اتصال به WhatsApp نیاز دارید؟','radio','["بله","خیر"]',1),
(@app_category_id,8,4,'آیا اتصال به شبکه‌های اجتماعی لازم است؟','checkbox','["Facebook","Instagram","Telegram","WhatsApp","سایر","خیر"]',1),
(@app_category_id,8,5,'آیا اتصال به سرویس‌های پرداخت یا بانکی نیاز دارید؟','radio','["بله","خیر"]',1),
(@app_category_id,8,6,'آیا اتصال به سرویس یا نرم‌افزار دیگری نیاز دارید؟','textarea',NULL,0)
ON DUPLICATE KEY UPDATE
    question_text = VALUES(question_text),
    question_type = VALUES(question_type),
    options_json = VALUES(options_json),
    is_required = VALUES(is_required);

-- =========================================
-- SECTION 9: TESTING AND DEPLOYMENT
-- =========================================

INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@app_category_id,9,1,'آیا تست کامل اپلیکیشن پیش از انتشار لازم است؟','radio','["بله","خیر"]',1),
(@app_category_id,9,2,'آیا تست امنیتی اپلیکیشن نیاز دارید؟','radio','["بله","خیر","نیاز به مشاوره دارم"]',1),
(@app_category_id,9,3,'اپلیکیشن در کجا منتشر شود؟','checkbox','["Google Play","App Store","وب‌سایت اختصاصی","فقط فایل APK","فعلاً انتشار لازم نیست"]',1),
(@app_category_id,9,4,'آیا حساب توسعه‌دهنده Google Play یا Apple دارید؟','radio','["بله","خیر","نیاز به راهنمایی دارم"]',1),
(@app_category_id,9,5,'آیا آموزش استفاده از اپلیکیشن نیاز دارید؟','radio','["بله","خیر"]',1),
(@app_category_id,9,6,'آیا مستندات فنی و راهنمای نصب لازم دارید؟','radio','["بله","خیر"]',1)
ON DUPLICATE KEY UPDATE
    question_text = VALUES(question_text),
    question_type = VALUES(question_type),
    options_json = VALUES(options_json),
    is_required = VALUES(is_required);

-- =========================================
-- SECTION 10: BUDGET, TIME AND FINAL DETAILS
-- =========================================

INSERT INTO project_questions
(category_id, section_number, question_number, question_text, question_type, options_json, is_required)
VALUES
(@app_category_id,10,1,'بودجه تقریبی شما برای ساخت اپلیکیشن چقدر است؟','number',NULL,0),
(@app_category_id,10,2,'زمان موردنظر برای تکمیل پروژه چیست؟','select','["کمتر از یک ماه","۱ تا ۳ ماه","۳ تا ۶ ماه","بیشتر از ۶ ماه","قابل مذاکره"]',1),
(@app_category_id,10,3,'آیا تاریخ مشخصی برای تحویل پروژه دارید؟','date',NULL,0),
(@app_category_id,10,4,'آیا پروژه باید در چند مرحله اجرا شود؟','radio','["بله","خیر","نیاز به مشاوره دارم"]',1),
(@app_category_id,10,5,'آیا فایل، طرح یا سندی برای ارائه دارید؟','radio','["بله","خیر","بعداً ارسال می‌کنم"]',1),
(@app_category_id,10,6,'توضیحات نهایی و درخواست‌های خاص خود را بنویسید.','textarea',NULL,0)
ON DUPLICATE KEY UPDATE
    question_text = VALUES(question_text),
    question_type = VALUES(question_type),
    options_json = VALUES(options_json),
    is_required = VALUES(is_required);

-- =========================================
-- END: 60 APPLICATION QUESTIONS
-- =========================================
