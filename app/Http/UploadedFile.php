<?php

declare(strict_types=1);

namespace App\Http;

final class UploadedFile
{
    private array $file;

    public function __construct(array $file)
    {
        $requiredKeys = [
            'name',
            'type',
            'tmp_name',
            'error',
            'size',
        ];

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $file)) {
                throw new \InvalidArgumentException(
                    'Invalid uploaded file structure.'
                );
            }
        }

        if (is_array($file['name'])) {
            throw new \InvalidArgumentException(
                'Multiple file uploads must be handled separately.'
            );
        }

        $this->file = $file;
    }

    public function originalName(): string
    {
        return basename((string) $this->file['name']);
    }

    public function temporaryPath(): string
    {
        return (string) $this->file['tmp_name'];
    }

    public function size(): int
    {
        return (int) $this->file['size'];
    }

    public function errorCode(): int
    {
        return (int) $this->file['error'];
    }

    public function isUploaded(): bool
    {
        return $this->errorCode() === UPLOAD_ERR_OK
            && is_uploaded_file($this->temporaryPath());
    }

    public function hasError(): bool
    {
        return $this->errorCode() !== UPLOAD_ERR_OK;
    }

    public function isValid(): bool
    {
        return $this->isUploaded()
            && $this->size() > 0;
    }

    public function mimeType(): ?string
    {
        if (!$this->isValid()) {
            return null;
        }

        if (!class_exists(\finfo::class)) {
            throw new \RuntimeException(
                'The PHP fileinfo extension is required.'
            );
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        $mime = $finfo->file($this->temporaryPath());

        return is_string($mime) ? $mime : null;
    }

    public function extension(): string
    {
        return strtolower(
            pathinfo($this->originalName(), PATHINFO_EXTENSION)
        );
    }

    public function validate(
        array $allowedMimeTypes,
        int $maxSize
    ): bool {
        if (!$this->isValid() || $this->size() > $maxSize) {
            return false;
        }

        $mime = $this->mimeType();

        return $mime !== null
            && in_array($mime, $allowedMimeTypes, true);
    }

    public function moveTo(
        string $destination,
        array $allowedMimeTypes,
        int $maxSize
    ): string {
        if (!$this->validate($allowedMimeTypes, $maxSize)) {
            throw new \RuntimeException(
                'فایل انتخاب‌شده معتبر نیست یا حجم آن بیش از حد مجاز است.'
            );
        }

        $directory = dirname($destination);

        if (!is_dir($directory)) {
            if (!mkdir($directory, 0750, true) && !is_dir($directory)) {
                throw new \RuntimeException(
                    'امکان ایجاد پوشه مقصد وجود ندارد.'
                );
            }
        }

        if (file_exists($destination)) {
            throw new \RuntimeException(
                'فایل مقصد از قبل وجود دارد.'
            );
        }

        if (!move_uploaded_file(
            $this->temporaryPath(),
            $destination
        )) {
            throw new \RuntimeException(
                'ذخیره فایل با مشکل مواجه شد.'
            );
        }

        return $destination;
    }

    public function errorMessage(): ?string
    {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'حجم فایل از محدودیت سرور بیشتر است.',
            UPLOAD_ERR_FORM_SIZE => 'حجم فایل از محدودیت فرم بیشتر است.',
            UPLOAD_ERR_PARTIAL => 'فایل به‌صورت ناقص ارسال شده است.',
            UPLOAD_ERR_NO_FILE => 'هیچ فایلی انتخاب نشده است.',
            UPLOAD_ERR_NO_TMP_DIR => 'پوشه موقت سرور در دسترس نیست.',
            UPLOAD_ERR_CANT_WRITE => 'ذخیره فایل روی سرور ناموفق بود.',
            UPLOAD_ERR_EXTENSION => 'یک افزونه سرور مانع بارگذاری فایل شد.',
        ];

        if ($this->errorCode() === UPLOAD_ERR_OK) {
            return null;
        }

        return $messages[$this->errorCode()]
            ?? 'خطای ناشناخته در بارگذاری فایل.';
    }
}
