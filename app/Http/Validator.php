<?php

declare(strict_types=1);

namespace App\Http;

final class Validator
{
    private array $data;

    private array $rules;

    private array $errors = [];

    private array $messages = [];

    public function __construct(
        array $data,
        array $rules,
        array $messages = []
    ) {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = $messages;
    }

    public static function make(
        array $data,
        array $rules,
        array $messages = []
    ): self {
        return new self($data, $rules, $messages);
    }

    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $fieldRules) {
            $rules = is_string($fieldRules)
                ? explode('|', $fieldRules)
                : $fieldRules;

            if (!is_array($rules)) {
                throw new \InvalidArgumentException(
                    'Validation rules must be an array or string.'
                );
            }

            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                if (is_string($rule)) {
                    [$ruleName, $parameter] = array_pad(
                        explode(':', $rule, 2),
                        2,
                        null
                    );
                } elseif (is_array($rule) && isset($rule['rule'])) {
                    $ruleName = $rule['rule'];
                    $parameter = $rule['value'] ?? null;
                } else {
                    throw new \InvalidArgumentException(
                        'Invalid validation rule.'
                    );
                }

                $ruleName = strtolower((string) $ruleName);

                if (
                    $ruleName !== 'required'
                    && $this->isEmpty($value)
                ) {
                    continue;
                }

                if (!$this->passes(
                    $ruleName,
                    $value,
                    $parameter
                )) {
                    $this->addError(
                        (string) $field,
                        $ruleName,
                        $parameter
                    );
                }
            }
        }

        return $this->errors === [];
    }

    private function passes(
        string $rule,
        mixed $value,
        mixed $parameter
    ): bool {
        switch ($rule) {
            case 'required':
                return !$this->isEmpty($value);

            case 'string':
                return is_string($value);

            case 'integer':
                return filter_var(
                    $value,
                    FILTER_VALIDATE_INT
                ) !== false;

            case 'numeric':
                return is_numeric($value);

            case 'boolean':
                return in_array(
                    $value,
                    [true, false, 0, 1, '0', '1'],
                    true
                );

            case 'email':
                return is_string($value)
                    && filter_var(
                        $value,
                        FILTER_VALIDATE_EMAIL
                    ) !== false;

            case 'min':
                return $this->measure($value)
                    >= (float) $parameter;

            case 'max':
                return $this->measure($value)
                    <= (float) $parameter;

            case 'between':
                [$min, $max] = array_pad(
                    explode(',', (string) $parameter, 2),
                    2,
                    null
                );

                return $min !== null
                    && $max !== null
                    && $this->measure($value) >= (float) $min
                    && $this->measure($value) <= (float) $max;

            case 'in':
                $allowed = explode(',', (string) $parameter);

                return in_array(
                    (string) $value,
                    $allowed,
                    true
                );

            case 'regex':
                return is_string($parameter)
                    && @preg_match($parameter, (string) $value) === 1;

            case 'url':
                return is_string($value)
                    && filter_var(
                        $value,
                        FILTER_VALIDATE_URL
                    ) !== false;

            case 'date':
                if (!is_string($value)) {
                    return false;
                }

                $date = \DateTimeImmutable::createFromFormat(
                    '!Y-m-d',
                    $value
                );

                return $date !== false
                    && $date->format('Y-m-d') === $value;

            case 'confirmed':
                return array_key_exists(
                    'password_confirmation',
                    $this->data
                ) && $value === $this->data['password_confirmation'];

            case 'accepted':
                return in_array(
                    $value,
                    [true, 1, '1', 'yes', 'on'],
                    true
                );

            default:
                throw new \InvalidArgumentException(
                    'Unknown validation rule: ' . $rule
                );
        }
    }

    private function measure(mixed $value): float
    {
        if (is_array($value)) {
            return (float) count($value);
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            return (float) (
                function_exists('mb_strlen')
                    ? mb_strlen($value, 'UTF-8')
                    : strlen($value)
            );
        }

        return 0;
    }

    private function isEmpty(mixed $value): bool
    {
        return $value === null
            || $value === ''
            || (is_array($value) && $value === []);
    }

    private function addError(
        string $field,
        string $rule,
        mixed $parameter
    ): void {
        $key = $field . '.' . $rule;

        $message = $this->messages[$key]
            ?? $this->defaultMessage($field, $rule, $parameter);

        $this->errors[$field][] = $message;
    }

    private function defaultMessage(
        string $field,
        string $rule,
        mixed $parameter
    ): string {
        $messages = [
            'required' => 'تکمیل این فیلد الزامی است.',
            'string' => 'مقدار واردشده باید متن باشد.',
            'integer' => 'مقدار باید عدد صحیح باشد.',
            'numeric' => 'مقدار باید عددی باشد.',
            'boolean' => 'مقدار انتخاب‌شده معتبر نیست.',
            'email' => 'آدرس ایمیل معتبر نیست.',
            'min' => 'حداقل مقدار مجاز رعایت نشده است.',
            'max' => 'حداکثر مقدار مجاز رعایت نشده است.',
            'between' => 'مقدار خارج از محدوده مجاز است.',
            'in' => 'مقدار انتخاب‌شده معتبر نیست.',
            'regex' => 'فرمت واردشده معتبر نیست.',
            'url' => 'آدرس اینترنتی معتبر نیست.',
            'date' => 'تاریخ واردشده معتبر نیست.',
            'confirmed' => 'تکرار رمز عبور مطابقت ندارد.',
            'accepted' => 'پذیرش این گزینه الزامی است.',
        ];

        return $messages[$rule]
            ?? 'مقدار واردشده معتبر نیست.';
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(?string $field = null): ?string
    {
        if ($field !== null) {
            return $this->errors[$field][0] ?? null;
        }

        foreach ($this->errors as $fieldErrors) {
            if (isset($fieldErrors[0])) {
                return $fieldErrors[0];
            }
        }

        return null;
    }

    public function validated(): array
    {
        if ($this->errors !== []) {
            return [];
        }

        $validated = [];

        foreach (array_keys($this->rules) as $field) {
            if (array_key_exists($field, $this->data)) {
                $validated[$field] = $this->data[$field];
            }
        }

        return $validated;
    }
}
