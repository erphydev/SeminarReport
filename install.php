<?php

// اگر پوشه vendor ندارید و ارور می‌دهد، این ۳ خط را حذف کنید و مقادیر را دستی در متغیرها بنویسید
require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad(); // تغییر به safeLoad برای جلوگیری از ارور در صورت نبود فایل

// دریافت اطلاعات از env یا استفاده از مقادیر پیش‌فرض (حتما فایل .env را در هاست بسازید)
$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbName = $_ENV['DB_NAME'] ?? 'your_cpanel_db_name'; // نام دیتابیس سی‌پنل را اینجا چک کنید
$user = $_ENV['DB_USER'] ?? 'your_cpanel_db_user';
$pass = $_ENV['DB_PASS'] ?? 'your_db_password';

echo "<div style='font-family: Tahoma; direction: rtl; padding: 20px; line-height: 2;'>";
echo "<h2>🚀 شروع عملیات نصب روی هاست...</h2>";

try {
    // اصلاح اتصال: اتصال مستقیم به دیتابیس مشخص شده (بدون تلاش برای ساخت دیتابیس)
    $dsn = "mysql:host=$host;dbname=$dbName;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "✅ اتصال به دیتابیس <b>$dbName</b> با موفقیت انجام شد.<br>";

    $sql = "
    SET FOREIGN_KEY_CHECKS = 0;

    DROP TABLE IF EXISTS attendance_logs;
    DROP TABLE IF EXISTS guests;
    DROP TABLE IF EXISTS experts;
    DROP TABLE IF EXISTS seminars;
    DROP TABLE IF EXISTS payments;

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

    CREATE TABLE `attendance_logs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `guest_id` INT NOT NULL,
        `seminar_id` INT NOT NULL,
        `scanned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT `fk_log_guest` FOREIGN KEY (`guest_id`) REFERENCES `guests`(`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_log_seminar` FOREIGN KEY (`seminar_id`) REFERENCES `seminars`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `payments` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `guest_id` INT NOT NULL,
      `registrar_expert` VARCHAR(100) NOT NULL COMMENT 'کارشناس ثبت کننده',
      `amount` DECIMAL(15, 0) NOT NULL DEFAULT 0 COMMENT 'مبلغ به تومان',
      `receipt_image` VARCHAR(255) NOT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`guest_id`) REFERENCES `guests`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    SET FOREIGN_KEY_CHECKS = 1;
    ";
    
    $pdo->exec($sql);
    echo "✅ جدول‌ها با موفقیت ساخته شدند.<br>";

    // Insert example data
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM seminars");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO seminars (title, date, is_active) VALUES ('سمینار تست', CURDATE(), 1)");
        echo "✅ داده‌های تستی وارد شد.<br>";
    }

    echo "<hr><h3 style='color: green'>🎉 نصب تمام شد!</h3>";
    echo "<a href='index.php'>رفتن به صفحه اصلی</a>";

} catch (PDOException $e) {
    echo "<h3 style='color: red'>❌ خطا:</h3>";
    echo "متن خطا: " . $e->getMessage();
    echo "<br><br><b>راهنمایی:</b><br>";
    echo "1. آیا فایل .env را ساخته‌اید؟<br>";
    echo "2. نام دیتابیس در سی‌پنل معمولا پیشوند دارد (مثلا user_seminar).<br>";
    echo "3. آیا دستور composer install را زده‌اید؟ (اگر ارور Class not found دارید).";
}
echo "</div>";
?>