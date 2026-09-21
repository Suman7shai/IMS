<?php

function is_valid_person_name(string $value, int $maxLength = 100): bool
{
    return mb_strlen($value) >= 2
        && mb_strlen($value) <= $maxLength
        && preg_match("/^[\\p{L}][\\p{L} '\\-]*$/u", $value) === 1;
}

function is_valid_entity_name(string $value, int $maxLength = 150): bool
{
    return mb_strlen($value) >= 2
        && mb_strlen($value) <= $maxLength
        && preg_match("/^[\\p{L}\\p{N}][\\p{L}\\p{N} .,'&()\\/+\\-#]*$/u", $value) === 1;
}

function is_valid_supplier_name(string $value, int $maxLength = 150): bool
{
    return mb_strlen($value) >= 2
        && mb_strlen($value) <= $maxLength
        && preg_match("/^[\\p{L}][\\p{L} .,'&()\\/+\\-#]*$/u", $value) === 1;
}

function is_valid_category_name(string $value, int $maxLength = 100): bool
{
    return mb_strlen($value) >= 2
        && mb_strlen($value) <= $maxLength
    && preg_match('/^[\\p{L}]+(?: [\\p{L}]+)*$/u', $value) === 1;
}

function is_valid_product_name(string $value, int $maxLength = 150): bool
{
    return mb_strlen($value) >= 2
        && mb_strlen($value) <= $maxLength
        && preg_match('/^[\\p{L}\\p{N}]+(?: [\\p{L}\\p{N}]+)*$/u', $value) === 1;
}

function is_valid_email(string $value): bool
{
    return mb_strlen($value) <= 254
        && preg_match('/^[A-Za-z0-9.!#$%&\'*+\\/=?^_`{|}~-]+@[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?(?:\\.[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?)+$/', $value) === 1;
}

function is_valid_phone(string $value): bool
{
    return preg_match('/^[0-9+() .-]{7,20}$/', $value) === 1;
}

function is_valid_username(string $value): bool
{
    return preg_match('/^[A-Za-z0-9_]{3,30}$/', $value) === 1;
}

function is_valid_password(string $value): bool
{
    return preg_match('/^(?=.*[A-Za-z])(?=.*[0-9])[^\\r\\n]{8,72}$/', $value) === 1;
}

function is_valid_decimal(string $value): bool
{
    return preg_match('/^\\d{1,10}(?:\\.\\d{1,2})?$/', $value) === 1;
}

function is_valid_integer(string $value): bool
{
    return preg_match('/^\\d+$/', $value) === 1;
}

function is_valid_free_text(string $value, int $maxLength = 1000): bool
{
    return mb_strlen($value) <= $maxLength
        && preg_match('/^[\\p{L}\\p{N}\\s.,\'"!?()\\/\\&@:#%+\\-_]*$/u', $value) === 1;
}
