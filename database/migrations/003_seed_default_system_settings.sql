-- =========================================================
-- MRM Technology Team
-- Migration: 003_seed_default_system_settings
-- Description: Insert default system settings
-- =========================================================

INSERT IGNORE INTO system_settings (
    setting_key,
    setting_value,
    setting_type,
    setting_group,
    setting_label,
    description,
    is_public
) VALUES

-- =========================================================
-- GENERAL SETTINGS
-- =========================================================

(
    'site_name',
    'MRM Technology Team',
    'text',
    'general',
    'نام وب‌سایت',
    'نام رسمی وب‌سایت و سامانه.',
    1
),

(
    'site_tagline',
    'Your Idea. Our Technology. Your Future.',
    'text',
    'general',
    'شعار وب‌سایت',
    'شعار رسمی MRM Technology Team.',
    1
),

(
    'site_description',
    'MRM Technology Team provides digital solutions through application development, database systems and website development.',
    'textarea',
    'general',
    'توضیحات وب‌سایت',
    'توضیحات عمومی وب‌سایت برای معرفی خدمات.',
    1
),

(
    'default_language',
    'fa',
    'text',
    'general',
    'زبان پیش‌فرض',
    'زبان پیش‌فرض رابط کاربری وب‌سایت.',
    1
),

(
    'default_timezone',
    'Asia/Kabul',
    'text',
    'general',
    'منطقه زمانی',
    'منطقه زمانی پیش‌فرض سامانه.',
    0
),

-- =========================================================
-- WEBSITE CONTROL
-- =========================================================

(
    'maintenance_mode',
    '0',
    'boolean',
    'general',
    'حالت تعمیر و نگهداری',
    'در صورت فعال‌سازی، وب‌سایت وارد حالت تعمیر و نگهداری می‌شود.',
    0
),

(
    'allow_registration',
    '1',
    'boolean',
    'general',
    'اجازه ثبت‌نام کاربران',
    'تعیین می‌کند کاربران جدید بتوانند حساب ایجاد کنند یا خیر.',
    0
),

(
    'require_email_verification',
    '1',
    'boolean',
    'security',
    'الزام تأیید ایمیل',
    'کاربران برای استفاده از حساب خود باید ایمیل‌شان را تأیید کنند.',
    0
),

-- =========================================================
-- CONTACT SETTINGS
-- =========================================================

(
    'contact_email',
    '',
    'email',
    'contact',
    'ایمیل تماس',
    'آدرس ایمیل رسمی برای ارتباط کاربران با تیم.',
    1
),

(
    'contact_phone',
    '',
    'text',
    'contact',
    'شماره تماس',
    'شماره تماس رسمی تیم.',
    1
),

(
    'contact_whatsapp',
    '',
    'text',
    'contact',
    'شماره واتساپ',
    'شماره رسمی واتساپ برای ارتباط با تیم.',
    1
),

(
    'contact_address',
    '',
    'textarea',
    'contact',
    'آدرس دفتر',
    'آدرس رسمی دفتر یا محل فعالیت تیم.',
    1
),

-- =========================================================
-- EMAIL SETTINGS
-- =========================================================

(
    'mail_from_name',
    'MRM Technology Team',
    'text',
    'email',
    'نام فرستنده ایمیل',
    'نامی که در ایمیل‌های ارسالی نمایش داده می‌شود.',
    0
),

(
    'mail_from_address',
    '',
    'email',
    'email',
    'آدرس فرستنده ایمیل',
    'آدرس ایمیل رسمی ارسال‌کننده پیام‌ها.',
    0
);

-- =========================================================
-- END OF MIGRATION
-- =========================================================
