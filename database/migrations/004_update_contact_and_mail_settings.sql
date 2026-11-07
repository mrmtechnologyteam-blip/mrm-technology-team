-- =========================================================
-- MRM Technology Team
-- Migration: 004_update_contact_and_mail_settings
-- Description: Set official contact and email information
-- =========================================================

INSERT INTO system_settings (
    setting_key,
    setting_value,
    setting_type,
    setting_group,
    setting_label,
    description,
    is_public
)
VALUES

(
    'contact_email',
    'mrmtechnologyteam@gmail.com',
    'email',
    'contact',
    'ایمیل تماس',
    'ایمیل رسمی MRM Technology Team.',
    1
),

(
    'contact_phone',
    '0764491299',
    'text',
    'contact',
    'شماره تماس',
    'شماره تماس رسمی تیم.',
    1
),

(
    'contact_whatsapp',
    '0773517812',
    'text',
    'contact',
    'شماره واتساپ',
    'شماره رسمی واتساپ تیم.',
    1
),

(
    'contact_address',
    'ولایت بلخ، شهر مزارشریف، مزار هوتل، دفتر MRM Technology Team',
    'textarea',
    'contact',
    'آدرس دفتر',
    'آدرس رسمی دفتر MRM Technology Team.',
    1
),

(
    'mail_from_name',
    'MRM Technology Team',
    'text',
    'email',
    'نام فرستنده ایمیل',
    'نام رسمی فرستنده ایمیل‌های سامانه.',
    0
),

(
    'mail_from_address',
    'mrmtechnologyteam@gmail.com',
    'email',
    'email',
    'آدرس فرستنده ایمیل',
    'ایمیل رسمی فرستنده پیام‌های سامانه.',
    0
)

ON DUPLICATE KEY UPDATE
    setting_value = VALUES(setting_value),
    setting_type = VALUES(setting_type),
    setting_group = VALUES(setting_group),
    setting_label = VALUES(setting_label),
    description = VALUES(description),
    is_public = VALUES(is_public);

-- =========================================================
-- END OF MIGRATION
-- =========================================================
