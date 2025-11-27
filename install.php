<?php

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

//load .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_SERVER['DB_HOST'] ?? $_ENV['DB_HOST'] ?? 'localhost';
$dbName = $_SERVER['DB_NAME'] ?? $_ENV['DB_NAME'] ?? 'seminar_db';
$user = $_SERVER['DB_USER'] ?? $_ENV['DB_USER'] ?? 'root';
$pass = $_SERVER['DB_PASS'] ?? $_ENV['DB_PASS'] ?? '';

echo "<div style='font-family: Tahoma; direction: rtl; padding: 20px; line-height: 2;'>";
echo "<h2>🚀 شروع عملیات نصب...</h2>";

try {
    //connect database
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //creat database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "✅ دیتابیس <b>$dbName</b> با موفقیت بررسی/ایجاد شد.<br>";

    //select database
    $pdo->exec("USE `$dbName`");

    $sql = "
    SET FOREIGN_KEY_CHECKS = 0;

    -- (Clean Install) اصلاح شد: کامنت کردن توضیحات
    DROP TABLE IF EXISTS guests;
    DROP TABLE IF EXISTS experts;
    DROP TABLE IF EXISTS seminars;

    CREATE TABLE IF NOT EXISTS `attendance_logs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `guest_id` INT NOT NULL,
        `seminar_id` INT NOT NULL,
        `scanned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        
        -- ارتباط با جداول دیگر
        CONSTRAINT `fk_log_guest` FOREIGN KEY (`guest_id`) REFERENCES `guests`(`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_log_seminar` FOREIGN KEY (`seminar_id`) REFERENCES `seminars`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `seminars` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `date` DATE NOT NULL,
        `is_active` TINYINT(1) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `experts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL UNIQUE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `guests` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `seminar_id` INT NOT NULL,
        `expert_id` INT NOT NULL,
        `full_name` VARCHAR(255) NOT NULL,
        `phone` VARCHAR(20) NOT NULL,
        `is_present` TINYINT(1) DEFAULT 0,
        `checkin_time` DATETIME NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        
        UNIQUE KEY `unique_checkin` (`seminar_id`, `phone`),
        
        FOREIGN KEY (`seminar_id`) REFERENCES `seminars`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`expert_id`) REFERENCES `experts`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    SET FOREIGN_KEY_CHECKS = 1;

    CREATE TABLE `payments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `first_name` varchar(100) NOT NULL,
    `last_name` varchar(100) NOT NULL,
    `phone` varchar(20) NOT NULL,
    `expert_name` varchar(100) NOT NULL,
    `receipt_image` varchar(255) NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $pdo->exec($sql);
    echo "✅ جدول‌ها (Seminars, Experts, Guests) با موفقیت ساخته شدند.<br>";

    //creat example
    $pdo->exec("INSERT INTO seminars (title, date, is_active) VALUES ('سمینار بزرگ فروش', CURDATE(), 1)");
    echo "✅ یک سمینار تستی با وضعیت فعال ایجاد شد.<br>";

    echo "<hr><h3 style='color: green'>🎉 نصب با موفقیت به پایان رسید!</h3>";
    echo "<p style='color: red'>⚠️ لطفاً فایل <b>install.php</b> را حذف کنید.</p>";
    echo "<a href='index.php'>رفتن به صفحه اصلی</a>";

} catch (PDOException $e) {
    echo "<h3 style='color: red'>❌ خطا در نصب:</h3>";
    echo "پیام خطا: " . $e->getMessage();
    echo "<br><strong>راهنمایی:</strong> مطمئن شوید در فایل .env پسورد دیتابیس صحیح است (معمولا در Laragon خالی است).";
}

echo "</div>";