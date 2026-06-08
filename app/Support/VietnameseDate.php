<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Throwable;

class VietnameseDate
{
    public static function toDatabase(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        $date = trim((string) $value);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1) {
            return $date;
        }

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date, $matches) === 1) {
            $day = (int) $matches[1];
            $month = (int) $matches[2];
            $year = (int) $matches[3];

            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        return $value;
    }

    public static function display(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('d/m/Y');
        }

        $date = trim((string) $value);

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date, $matches) === 1) {
            return sprintf('%02d/%02d/%04d', (int) $matches[1], (int) $matches[2], (int) $matches[3]);
        }

        try {
            return CarbonImmutable::parse($date)->format('d/m/Y');
        } catch (Throwable) {
            return $date;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $fields
     * @return array<string, mixed>
     */
    public static function normalizedFields(array $data, array $fields): array
    {
        $normalized = [];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $normalized[$field] = self::toDatabase($data[$field]);
            }
        }

        return $normalized;
    }
}
