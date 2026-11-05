<?php

declare(strict_types=1);

namespace App\Services;

final class Validator
{
    private array $errors = [];

    /**
     * Validate required field.
     */
    public function required(
        string $field,
        mixed $value,
        string $label
    ): self {
        if (
            $value === null ||
            (is_string($value) && trim($value) === '') ||
            (is_array($value) && count($value) === 0)
        ) {
            $this->errors[$field][] =
                "$label is required.";
        }

        return $this;
    }

    /**
     * Validate email address.
     */
    public function email(
        string $field,
        mixed $value
    ): self {
        if (
            $value !== null &&
            $value !== '' &&
            (
                !is_string($value) ||
                filter_var($value, FILTER_VALIDATE_EMAIL) === false
            )
        ) {
            $this->errors[$field][] =
                'Invalid email address.';
        }

        return $this;
    }

    /**
     * Validate minimum string length.
     */
    public function minLength(
        string $field,
        mixed $value,
        int $length
    ): self {
        if (
            is_string($value) &&
            mb_strlen(trim($value), 'UTF-8') < $length
        ) {
            $this->errors[$field][] =
                "Minimum length is $length characters.";
        }

        return $this;
    }

    /**
     * Validate maximum string length.
     */
    public function maxLength(
        string $field,
        mixed $value,
        int $length
    ): self {
        if (
            is_string($value) &&
            mb_strlen(trim($value), 'UTF-8') > $length
        ) {
            $this->errors[$field][] =
                "Maximum length is $length characters.";
        }

        return $this;
    }

    /**
     * Validate numeric value.
     */
    public function numeric(
        string $field,
        mixed $value
    ): self {
        if (
            $value !== null &&
            $value !== '' &&
            !is_numeric($value)
        ) {
            $this->errors[$field][] =
                'Value must be numeric.';
        }

        return $this;
    }

    /**
     * Validate integer value.
     */
    public function integer(
        string $field,
        mixed $value
    ): self {
        if (
            $value !== null &&
            $value !== '' &&
            filter_var($value, FILTER_VALIDATE_INT) === false
        ) {
            $this->errors[$field][] =
                'Value must be an integer.';
        }

        return $this;
    }

    /**
     * Validate allowed values.
     */
    public function in(
        string $field,
        mixed $value,
        array $allowed
    ): self {
        if (
            $value !== null &&
            !in_array($value, $allowed, true)
        ) {
            $this->errors[$field][] =
                'Invalid selected value.';
        }

        return $this;
    }

    /**
     * Validate password strength.
     */
    public function strongPassword(
        string $field,
        mixed $value
    ): self {
        if (!is_string($value) || $value === '') {
            $this->errors[$field][] =
                'Password is required.';

            return $this;
        }

        if (strlen($value) < 8) {
            $this->errors[$field][] =
                'Password must contain at least 8 characters.';
        }

        if (!preg_match('/[A-Z]/', $value)) {
            $this->errors[$field][] =
                'Password must contain an uppercase letter.';
        }

        if (!preg_match('/[a-z]/', $value)) {
            $this->errors[$field][] =
                'Password must contain a lowercase letter.';
        }

        if (!preg_match('/[0-9]/', $value)) {
            $this->errors[$field][] =
                'Password must contain a number.';
        }

        return $this;
    }

    /**
     * Check whether validation passed.
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Check whether validation failed.
     */
    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * Return all validation errors.
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Return errors for one field.
     */
    public function error(string $field): array
    {
        return $this->errors[$field] ?? [];
    }
}
