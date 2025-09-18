<?php
function validate_password(string $password): bool
{
    $lengthValid = strlen($password) >= 8;
    $hasUpper = preg_match('/[A-Z]/', $password) === 1;
    $hasLower = preg_match('/[a-z]/', $password) === 1;
    $hasNumber = preg_match('/\d/', $password) === 1;
    $hasSymbol = preg_match('/[\W_]/', $password) === 1;

    return $lengthValid && $hasUpper && $hasLower && $hasNumber && $hasSymbol;
}

function password_requirements_message(): string
{
    return 'Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.';
}
