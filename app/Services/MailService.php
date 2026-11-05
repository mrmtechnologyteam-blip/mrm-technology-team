<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;
use Throwable;

final class MailService
{
    private PHPMailer $mailer;

    public function __construct()
    {
        if (!class_exists(PHPMailer::class)) {
            throw new RuntimeException(
                'PHPMailer نصب نشده است. ابتدا Composer را اجرا کنید.'
            );
        }

        $host = trim((string) app_env('MAIL_HOST', ''));
        $username = trim((string) app_env('MAIL_USERNAME', ''));
        $password = (string) app_env('MAIL_PASSWORD', '');
        $fromAddress = trim(
            (string) app_env('MAIL_FROM_ADDRESS', '')
        );

        $port = (int) app_env('MAIL_PORT', 587);
        $encryption = strtolower(
            trim((string) app_env('MAIL_ENCRYPTION', 'tls'))
        );

        $fromName = trim(
            (string) app_env(
                'MAIL_FROM_NAME',
                APP_NAME
            )
        );

        if (
            $host === '' ||
            $username === '' ||
            $password === '' ||
            !filter_var($fromAddress, FILTER_VALIDATE_EMAIL)
        ) {
            throw new RuntimeException(
                'تنظیمات SMTP کامل نیست.'
            );
        }

        if ($port < 1 || $port > 65535) {
            throw new RuntimeException(
                'پورت SMTP معتبر نیست.'
            );
        }

        if (!in_array($encryption, ['tls', 'ssl', 'none'], true)) {
            throw new RuntimeException(
                'نوع رمزنگاری SMTP معتبر نیست.'
            );
        }

        $this->mailer = new PHPMailer(true);

        $this->mailer->isSMTP();
        $this->mailer->Host = $host;
        $this->mailer->Port = $port;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $username;
        $this->mailer->Password = $password;

        if ($encryption === 'tls') {
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($encryption === 'ssl') {
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $this->mailer->SMTPSecure = '';
            $this->mailer->SMTPAutoTLS = false;
        }

        $this->mailer->CharSet = 'UTF-8';
        $this->mailer->isHTML(true);

        $this->mailer->setFrom(
            $fromAddress,
            $fromName
        );

        $this->mailer->SMTPDebug = 0;
    }

    public function send(
        string $recipient,
        string $subject,
        string $htmlBody,
        string $plainBody = ''
    ): bool {
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(
                'آدرس ایمیل گیرنده معتبر نیست.'
            );
        }

        if (trim($subject) === '' || trim($htmlBody) === '') {
            throw new RuntimeException(
                'موضوع و محتوای ایمیل نمی‌توانند خالی باشند.'
            );
        }

        try {
            $this->mailer->clearAllRecipients();
            $this->mailer->clearAttachments();
            $this->mailer->clearReplyTos();

            $this->mailer->addAddress($recipient);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $htmlBody;

            $this->mailer->AltBody = $plainBody !== ''
                ? $plainBody
                : trim(strip_tags($htmlBody));

            return $this->mailer->send();

        } catch (Exception $exception) {
            error_log(
                '[MailService] Email delivery failed: ' .
                $exception->getMessage()
            );

            throw new RuntimeException(
                'ارسال ایمیل انجام نشد. تنظیمات سرویس ایمیل را بررسی کنید.',
                0,
                $exception
            );

        } catch (Throwable $exception) {
            error_log(
                '[MailService] Unexpected mail error: ' .
                $exception->getMessage()
            );

            throw new RuntimeException(
                'خطای غیرمنتظره در سرویس ایمیل رخ داد.',
                0,
                $exception
            );
        }
    }
}
