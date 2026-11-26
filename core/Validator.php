<?php
declare(strict_types=1);

namespace Core;

/**
 * Server-Side Input Validation & Sanitization
 */
class Validator
{
    private array $errors = [];

    public function validate(array $data, array $rules): array
    {
        $this->errors = [];
        $clean = [];

        foreach ($rules as $field => $ruleString) {
            $value = $data[$field] ?? null;
            $ruleList = is_array($ruleString) ? $ruleString : explode('|', $ruleString);
            $label = ucwords(str_replace('_', ' ', $field));

            if (is_string($value)) {
                $value = trim($value);
            }

            $isRequired = in_array('required', $ruleList, true);
            $isEmpty = ($value === null || $value === '');

            if ($isEmpty && !$isRequired) {
                $clean[$field] = $value;
                continue;
            }

            foreach ($ruleList as $rule) {
                if ($rule === 'required' && $isEmpty) {
                    $this->errors[$field] = "{$label} is required.";
                    break;
                }

                if ($rule === 'email' && !$isEmpty && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field] = "Please enter a valid email address for {$label}.";
                }

                if ($rule === 'numeric' && !$isEmpty && !is_numeric($value)) {
                    $this->errors[$field] = "{$label} must be a valid number.";
                }

                if ($rule === 'integer' && !$isEmpty && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->errors[$field] = "{$label} must be a whole number.";
                }

                if ($rule === 'date' && !$isEmpty && strtotime((string) $value) === false) {
                    $this->errors[$field] = "{$label} must be a valid date.";
                }

                if ($rule === 'hex_color' && !$isEmpty && !preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $value)) {
                    $this->errors[$field] = "{$label} must be a valid 6-digit hex color (e.g., #0B1D2E).";
                }

                if ($rule === 'url' && !$isEmpty && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->errors[$field] = "{$label} must be a valid URL.";
                }

                if ($rule === 'slug' && !$isEmpty && !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', (string) $value)) {
                    $this->errors[$field] = "{$label} may only contain lowercase letters, numbers, and hyphens.";
                }

                if (str_starts_with($rule, 'min:') && !$isEmpty) {
                    $min = (int) substr($rule, 4);
                    if (mb_strlen((string) $value) < $min) {
                        $this->errors[$field] = "{$label} must be at least {$min} characters.";
                    }
                }

                if (str_starts_with($rule, 'max:') && !$isEmpty) {
                    $max = (int) substr($rule, 4);
                    if (mb_strlen((string) $value) > $max) {
                        $this->errors[$field] = "{$label} cannot exceed {$max} characters.";
                    }
                }

                if (str_starts_with($rule, 'min_val:') && !$isEmpty && is_numeric($value)) {
                    $minVal = (float) substr($rule, 8);
                    if ((float) $value < $minVal) {
                        $this->errors[$field] = "{$label} must be at least {$minVal}.";
                    }
                }

                if (str_starts_with($rule, 'in:') && !$isEmpty) {
                    $allowed = explode(',', substr($rule, 3));
                    if (!in_array((string) $value, $allowed, true)) {
                        $this->errors[$field] = "Invalid selection for {$label}.";
                    }
                }

                if (str_starts_with($rule, 'match:') && !$isEmpty) {
                    $other = substr($rule, 6);
                    if ($value !== trim((string) ($data[$other] ?? ''))) {
                        $this->errors[$field] = "{$label} does not match.";
                    }
                }
            }

            $clean[$field] = $value;
        }

        return $clean;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        return $this->errors ? reset($this->errors) : null;
    }
}
