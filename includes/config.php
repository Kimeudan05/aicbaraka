<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = (int) (getenv('DB_PORT') ?: 3306);
$dbUser = getenv('DB_USER') ?: 'root';
$dbPassword = getenv('DB_PASSWORD') ?: '';
$dbName = getenv('DB_NAME') ?: 'youth_ministry_db';

if (str_contains($dbHost, ':')) {
    [$hostPart, $portPart] = explode(':', $dbHost, 2);
    if (is_numeric($portPart)) {
        $dbHost = $hostPart;
        $dbPort = (int) $portPart;
    }
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($dbHost, $dbUser, $dbPassword, '', $dbPort);
} catch (mysqli_sql_exception $e) {
    die('Connection failed: ' . $e->getMessage());
}

$conn->set_charset('utf8mb4');
$conn->query("CREATE DATABASE IF NOT EXISTS `{$dbName}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($dbName);

$schemaStatements = [
    "CREATE TABLE IF NOT EXISTS users (" .
    "id INT AUTO_INCREMENT PRIMARY KEY, " .
    "firstname VARCHAR(100) NOT NULL, " .
    "lastname VARCHAR(100) NOT NULL, " .
    "name VARCHAR(200) NOT NULL, " .
    "email VARCHAR(150) NOT NULL UNIQUE, " .
    "phone VARCHAR(30) DEFAULT NULL, " .
    "password VARCHAR(255) NOT NULL, " .
    "role ENUM('admin','youth') NOT NULL DEFAULT 'youth', " .
    "profile_picture VARCHAR(255) DEFAULT NULL, " .
    "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP" .
    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS resources (" .
    "id INT AUTO_INCREMENT PRIMARY KEY, " .
    "title VARCHAR(150) NOT NULL, " .
    "type VARCHAR(100) NOT NULL, " .
    "description TEXT, " .
    "file_path VARCHAR(255) NOT NULL, " .
    "uploaded_by INT DEFAULT NULL, " .
    "upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, " .
    "CONSTRAINT fk_resources_user FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL" .
    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS encouragements (" .
    "id INT AUTO_INCREMENT PRIMARY KEY, " .
    "user_id INT NOT NULL, " .
    "content TEXT NOT NULL, " .
    "date_shared TIMESTAMP DEFAULT CURRENT_TIMESTAMP, " .
    "approved TINYINT(1) NOT NULL DEFAULT 0, " .
    "CONSTRAINT fk_encouragement_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE" .
    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS youth_pledges (" .
    "id INT AUTO_INCREMENT PRIMARY KEY, " .
    "youth_id INT NOT NULL, " .
    "pledge_type VARCHAR(100) NOT NULL, " .
    "due_date DATE DEFAULT NULL, " .
    "pledge_amount DECIMAL(10,2) NOT NULL DEFAULT 0, " .
    "amount_paid DECIMAL(10,2) NOT NULL DEFAULT 0, " .
    "status ENUM('Paid','Unpaid') NOT NULL DEFAULT 'Unpaid', " .
    "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, " .
    "CONSTRAINT fk_pledge_user FOREIGN KEY (youth_id) REFERENCES users(id) ON DELETE CASCADE" .
    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

foreach ($schemaStatements as $statement) {
    $conn->query($statement);
}

$defaultAdminEmail = getenv('DEFAULT_ADMIN_EMAIL') ?: 'admin@example.com';
$defaultAdminPassword = getenv('DEFAULT_ADMIN_PASSWORD') ?: 'admin1@2024';

$checkAdmin = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$checkAdmin->bind_param('s', $defaultAdminEmail);
$checkAdmin->execute();
$checkAdmin->store_result();

if ($checkAdmin->num_rows === 0) {
    $firstName = 'System';
    $lastName = 'Admin';
    $fullName = $firstName . ' ' . $lastName;
    $adminPhone = '0700000000';
    $hashedPassword = password_hash($defaultAdminPassword, PASSWORD_DEFAULT);

    $insertAdmin = $conn->prepare('INSERT INTO users (firstname, lastname, name, email, phone, password, role) VALUES (?, ?, ?, ?, ?, ?, "admin")');
    $insertAdmin->bind_param('ssssss', $firstName, $lastName, $fullName, $defaultAdminEmail, $adminPhone, $hashedPassword);
    $insertAdmin->execute();
    $insertAdmin->close();
}

$checkAdmin->close();

function sanitize_input(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}
