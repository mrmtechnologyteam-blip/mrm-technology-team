<?php

declare(strict_types=1);

namespace App\Http;

final class RateLimiter
{
    private string $storagePath;

    public function __construct(?string $storagePath = null)
    {
        $this->storagePath = $storagePath
            ?? sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mrm_rate_limits';

        if (
            !is_dir($this->storagePath)
            && !mkdir($this->storagePath, 0700, true)
            && !is_dir($this->storagePath)
        ) {
            throw new \RuntimeException(
                'Unable to initialize rate limiter storage.'
            );
        }

        if (!is_writable($this->storagePath)) {
            throw new \RuntimeException(
                'Rate limiter storage is not writable.'
            );
        }
    }

    public function attempt(
        string $key,
        int $maxAttempts,
        int $decaySeconds
    ): bool {
        if (
            $key === ''
            || $maxAttempts < 1
            || $decaySeconds < 1
        ) {
            throw new \InvalidArgumentException(
                'Invalid rate limiter configuration.'
            );
        }

        $file = $this->filePath($key);
        $handle = fopen($file, 'c+');

        if ($handle === false) {
            throw new \RuntimeException(
                'Unable to access rate limiter storage.'
            );
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new \RuntimeException(
                    'Unable to lock rate limiter storage.'
                );
            }

            rewind($handle);
            $contents = stream_get_contents($handle);

            $state = is_string($contents)
                ? json_decode($contents, true)
                : null;

            $now = time();

            if (
                !is_array($state)
                || !isset($state['attempts'], $state['expires_at'])
                || !is_numeric($state['attempts'])
                || !is_numeric($state['expires_at'])
                || (int) $state['expires_at'] <= $now
            ) {
                $state = [
                    'attempts' => 0,
                    'expires_at' => $now + $decaySeconds,
                ];
            }

            if ((int) $state['attempts'] >= $maxAttempts) {
                return false;
            }

            $state['attempts']++;

            rewind($handle);
            ftruncate($handle, 0);

            $written = fwrite(
                $handle,
                json_encode($state, JSON_THROW_ON_ERROR)
            );

            if ($written === false) {
                throw new \RuntimeException(
                    'Unable to save rate limiter state.'
                );
            }

            fflush($handle);

            return true;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    public function tooManyAttempts(
        string $key,
        int $maxAttempts
    ): bool {
        $state = $this->getState($key);

        return $state !== null
            && (int) $state['attempts'] >= $maxAttempts;
    }

    public function availableIn(string $key): int
    {
        $state = $this->getState($key);

        if ($state === null) {
            return 0;
        }

        return max(0, (int) $state['expires_at'] - time());
    }

    public function remaining(
        string $key,
        int $maxAttempts
    ): int {
        if ($maxAttempts < 1) {
            throw new \InvalidArgumentException(
                'Maximum attempts must be greater than zero.'
            );
        }

        $state = $this->getState($key);

        if ($state === null) {
            return $maxAttempts;
        }

        return max(
            0,
            $maxAttempts - (int) $state['attempts']
        );
    }

    public function clear(string $key): void
    {
        $file = $this->filePath($key);

        if (is_file($file)) {
            unlink($file);
        }
    }

    private function getState(string $key): ?array
    {
        if ($key === '') {
            return null;
        }

        $file = $this->filePath($key);

        if (!is_file($file)) {
            return null;
        }

        $handle = fopen($file, 'c+');

        if ($handle === false) {
            return null;
        }

        try {
            if (!flock($handle, LOCK_SH)) {
                return null;
            }

            rewind($handle);
            $contents = stream_get_contents($handle);

            $state = is_string($contents)
                ? json_decode($contents, true)
                : null;

            if (
                !is_array($state)
                || !isset($state['attempts'], $state['expires_at'])
                || !is_numeric($state['attempts'])
                || !is_numeric($state['expires_at'])
                || (int) $state['expires_at'] <= time()
            ) {
                return null;
            }

            return $state;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function filePath(string $key): string
    {
        return $this->storagePath
            . DIRECTORY_SEPARATOR
            . hash('sha256', $key)
            . '.json';
    }
}
