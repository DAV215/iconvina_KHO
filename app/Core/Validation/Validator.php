<?php

declare(strict_types=1);

namespace App\Core\Validation;

use App\Core\Exceptions\ValidationException;

final class Validator
{
    public static function validate(array $input, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $input[$field] ?? null;
            $fieldRules = is_array($fieldRules) ? $fieldRules : explode('|', (string) $fieldRules);
            $isNullable = in_array('nullable', $fieldRules, true);

            if ($isNullable && ($value === null || $value === '')) {
                continue;
            }

            foreach ($fieldRules as $rule) {
                if ($rule === 'nullable') {
                    continue;
                }

                if ($rule === 'required' && ($value === null || $value === '')) {
                    $errors[$field][] = 'Trường này là bắt buộc.';
                }

                if ($rule === 'string' && $value !== null && !is_string($value)) {
                    $errors[$field][] = 'Trường này phải là chuỗi.';
                }

                if ($rule === 'numeric' && $value !== null && !is_numeric($value)) {
                    $errors[$field][] = 'Trường này phải là số.';
                }

                if ($rule === 'email' && $value !== null && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                    $errors[$field][] = 'Trường này phải là địa chỉ email hợp lệ.';
                }

                if (str_starts_with($rule, 'max:') && $value !== null) {
                    $max = (int) substr($rule, 4);
                    if (mb_strlen((string) $value) > $max) {
                        $errors[$field][] = "Trường này không được vượt quá {$max} ký tự.";
                    }
                }
            }
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return $input;
    }
}