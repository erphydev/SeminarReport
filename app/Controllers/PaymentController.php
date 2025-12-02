<?php

namespace App\Controllers;

use PDO;
use Exception;

class PaymentController
{
    private $pdo;

    public function __construct()
    {
        // تنظیمات اتصال به دیتابیس
        $host = $_SERVER['DB_HOST'] ?? 'localhost';
        $dbName = $_SERVER['DB_NAME'] ?? 'salescoaching_seminar';
        $user = $_SERVER['DB_USER'] ?? 'salescoaching_seminar';
        $pass = $_SERVER['DB_PASS'] ?? 'Nuw%xri6R9NuK+rQ';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            die("خطای اتصال به دیتابیس: " . $e->getMessage());
        }
    }

    // نمایش فرم پرداخت
    public function index()
    {
        // خواندن تنظیمات از دیتابیس برای اینکه ببینیم گزینه "بدون پیش‌پرداخت" فعال است یا نه
        // اگر جدول تنظیمات هنوز ساخته نشده باشد، پیش‌فرض را false می‌گیریم تا ارور ندهد
        try {
            $stmt = $this->pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'enable_no_prepayment'");
            $stmt->execute();
            $result = $stmt->fetchColumn();
            // اگر مقدار 1 بود، یعنی فعال است
            $noPrepaymentActive = ($result === '1');
        } catch (Exception $e) {
            $noPrepaymentActive = false;
        }

        // فراخوانی ویو (فرم)
        // متغیر $noPrepaymentActive در فایل ویو قابل دسترسی خواهد بود
        require_once __DIR__ . '/../../Views/guest/paymentform.php';
    }

    // بررسی شماره موبایل (AJAX)
    public function checkPhone()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
            return;
        }

        $phone = $_POST['phone'] ?? '';

        // جستجو در جدول مهمان‌ها
        $stmt = $this->pdo->prepare("SELECT id, full_name FROM guests WHERE phone = :phone LIMIT 1");
        $stmt->execute([':phone' => $phone]);
        $guest = $stmt->fetch();

        if ($guest) {
            echo json_encode([
                'status' => 'success',
                'full_name' => $guest['full_name'],
                'guest_id' => $guest['id']
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'شماره شما در لیست مهمان‌ها یافت نشد.'
            ]);
        }
    }

    // ثبت نهایی اطلاعات
    public function submit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/payment');
            exit;
        }

        // دریافت ورودی‌ها
        $guestId = $_POST['guest_id'] ?? null;
        $expertName = $_POST['payment_expert_name'] ?? 'نامشخص';
        $amountRaw = $_POST['amount'] ?? '0';
        $amount = str_replace(',', '', $amountRaw); // حذف ویرگول از مبلغ (مثلا 1,000,000 بشه 1000000)

        // ورودی‌های جدید
        $paymentMethod = $_POST['payment_method'] ?? 'card_to_card';
        $description = $_POST['description'] ?? '';

        // اعتبارسنجی اولیه
        if (!$guestId) {
            header('Location: ' . BASE_URL . '/payment?status=guest_not_found');
            exit;
        }

        // === لاجیک اعتبارسنجی بر اساس روش پرداخت ===

        // اگر روش پرداخت "بدون پیش‌پرداخت" نبود، باید عکس و مبلغ چک شود
        if ($paymentMethod !== 'no_prepayment') {
            if (empty($amount) || $amount == 0) {
                // می‌توان ارور داد، اما فعلاً سخت‌گیری نمی‌کنیم
            }
            // تصویر اول الزامی است
            if (empty($_FILES['receipt_image']['name'])) {
                header('Location: ' . BASE_URL . '/payment?status=upload_error');
                exit;
            }
        } else {
            // اگر "بدون پیش پرداخت" بود، مبلغ را صفر می‌کنیم
            $amount = 0;
        }

        // مسیر آپلود
        $uploadDir = __DIR__ . '/../../public/uploads/receipts/';
        // اگر پوشه وجود نداشت بساز
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $imageName1 = null;
        $imageName2 = null;

        // --- آپلود تصویر اول ---
        if (!empty($_FILES['receipt_image']['name'])) {
            $ext = pathinfo($_FILES['receipt_image']['name'], PATHINFO_EXTENSION);
            $imageName1 = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (!move_uploaded_file($_FILES['receipt_image']['tmp_name'], $uploadDir . $imageName1)) {
                header('Location: ' . BASE_URL . '/payment?status=upload_error');
                exit;
            }
        }

        // --- آپلود تصویر دوم (فقط اگر روش کارت به کارت بود و فایل ارسال شده بود) ---
        if ($paymentMethod === 'card_to_card' && !empty($_FILES['receipt_image_2']['name'])) {
            $ext = pathinfo($_FILES['receipt_image_2']['name'], PATHINFO_EXTENSION);
            $imageName2 = time() . '_2_' . bin2hex(random_bytes(4)) . '.' . $ext;
            move_uploaded_file($_FILES['receipt_image_2']['tmp_name'], $uploadDir . $imageName2);
        }

        try {
            // ذخیره در دیتابیس
            $stmt = $this->pdo->prepare("
                INSERT INTO payments 
                (guest_id, amount, payment_method, registrar_expert, receipt_image, receipt_image_2, description, created_at) 
                VALUES 
                (:gid, :amt, :method, :exp, :img1, :img2, :desc, NOW())
            ");

            $stmt->execute([
                ':gid' => $guestId,
                ':amt' => $amount,
                ':method' => $paymentMethod,
                ':exp' => $expertName,
                ':img1' => $imageName1,
                ':img2' => $imageName2,
                ':desc' => $description
            ]);

            header('Location: ' . BASE_URL . '/payment?status=success');

        } catch (Exception $e) {
            // برای دیباگ می‌توانید خط زیر را از کامنت درآورید
            // die($e->getMessage());
            header('Location: ' . BASE_URL . '/payment?status=db_error');
        }
    }
}