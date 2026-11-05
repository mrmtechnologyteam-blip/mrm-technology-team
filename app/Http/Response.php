<?php

declare(strict_types=1);

namespace App\Http;

final class Response
{
    private string $body;
    private int $statusCode;
    private array $headers;
    private bool $sent = false;

    public function __construct(
        string $body = '',
        int $statusCode = 200,
        array $headers = []
    ) {
        $this->body = $body;
        $this->setStatusCode($statusCode);

        foreach ($headers as $name => $value) {
            $this->setHeader((string) $name, (string) $value);
        }
    }

    public static function html(
        string $html,
        int $statusCode = 200
    ): self {
        return new self($html, $statusCode, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public static function json(
        array $data,
        int $statusCode = 200,
        array $headers = []
    ): self {
        $json = json_encode(
            $data,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_THROW_ON_ERROR
        );

        return new self(
            $json,
            $statusCode,
            array_merge([
                'Content-Type' => 'application/json; charset=UTF-8',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
                'X-Content-Type-Options' => 'nosniff',
            ], $headers)
        );
    }

    public static function text(
        string $text,
        int $statusCode = 200
    ): self {
        return new self($text, $statusCode, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public static function redirect(
        string $location,
        int $statusCode = 302
    ): self {
        if (!in_array($statusCode, [301, 302, 303, 307, 308], true)) {
            throw new \InvalidArgumentException(
                'Invalid redirect status code.'
            );
        }

        if (
            $location === ''
            || preg_match('/[\r\n]/', $location)
            || !str_starts_with($location, '/')
            || str_starts_with($location, '//')
        ) {
            throw new \InvalidArgumentException(
                'Invalid redirect location.'
            );
        }

        return new self('', $statusCode, [
            'Location' => $location,
            'Cache-Control' => 'no-store',
        ]);
    }

    public static function noContent(): self
    {
        return new self('', 204);
    }

    public static function notFound(
        string $message = 'صفحه موردنظر پیدا نشد.'
    ): self {
        return self::html(
            self::errorPage(404, 'پیدا نشد', $message),
            404
        );
    }

    public static function forbidden(
        string $message = 'شما اجازه دسترسی به این بخش را ندارید.'
    ): self {
        return self::html(
            self::errorPage(403, 'دسترسی غیرمجاز', $message),
            403
        );
    }

    public static function serverError(
        string $message = 'خطای داخلی سرور رخ داده است.'
    ): self {
        return self::html(
            self::errorPage(500, 'خطای سرور', $message),
            500
        );
    }

    private static function errorPage(
        int $code,
        string $title,
        string $message
    ): string {
        return '<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $code . ' - ' . htmlspecialchars(
            $title,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        ) . '</title>
</head>
<body>
    <main>
        <h1>' . $code . '</h1>
        <h2>' . htmlspecialchars(
            $title,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        ) . '</h2>
        <p>' . htmlspecialchars(
            $message,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        ) . '</p>
        <a href="/">بازگشت به صفحه اصلی</a>
    </main>
</body>
</html>';
    }

    public function setStatusCode(int $statusCode): self
    {
        if ($statusCode < 100 || $statusCode > 599) {
            throw new \InvalidArgumentException(
                'Invalid HTTP status code.'
            );
        }

        $this->statusCode = $statusCode;

        return $this;
    }

    public function setHeader(
        string $name,
        string $value
    ): self {
        if (
            $name === ''
            || !preg_match('/^[A-Za-z0-9!#$%&\'*+.^_`|~-]+$/', $name)
        ) {
            throw new \InvalidArgumentException(
                'Invalid HTTP header name.'
            );
        }

        if (preg_match('/[\r\n]/', $value)) {
            throw new \InvalidArgumentException(
                'Invalid HTTP header value.'
            );
        }

        $this->headers[$name] = $value;

        return $this;
    }

    public function removeHeader(string $name): self
    {
        foreach (array_keys($this->headers) as $headerName) {
            if (strcasecmp($headerName, $name) === 0) {
                unset($this->headers[$headerName]);
            }
        }

        return $this;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function send(): void
    {
        if ($this->sent) {
            return;
        }

        if (headers_sent()) {
            throw new \RuntimeException(
                'Cannot send response because headers have already been sent.'
            );
        }

        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value, true);
        }

        if (
            $this->statusCode !== 204
            && $this->statusCode !== 304
            && strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD'
        ) {
            echo $this->body;
        }

        $this->sent = true;
    }

    public function isSent(): bool
    {
        return $this->sent;
    }
}
