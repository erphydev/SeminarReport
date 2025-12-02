<?php

namespace App\Controllers;

use PDO;
use PDOException;

class PaymentController
{
    private $pdo;

    public function __construct()
    {
        $host = $_SERVER['DB_HOST'] ?? $_ENV['DB_HOST'] ?? 'localhost';
        $dbName = $_SERVER['DB_NAME'] ?? $_ENV['DB_NAME'] ?? 'salescoaching_seminar';
        $user = $_SERVER['DB_USER'] ?? $_ENV['DB_USER'] ?? 'salescoaching_seminar';
        $pass = $_SERVER['DB_PASS'] ?? $_ENV['DB_PASS'] ?? 'Nuw%xri6R9NuK+rQ';

        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("خطا در اتصال دیتابیس: " . $e->getMessage());
        }
    }

    public function index()
    {
        require_once __DIR__ . '/../Views/guest/paymentform.php';
    }

    // متد جدید: بررسی شماره موبایل و برگرداندن نام (برای AJAX)
    public function checkPhone()
    {
        header('Content-Type: application/json');
        
        $rawPhone = $_POST['phone'] ?? '';
        
        if (empty($rawPhone)) {
            echo json_encode(['status' => 'error', 'message' => 'شماره وارد نشده است']);
            exit;
        }

        // 1. حذف فاصله و کاراکترهای غیر عددی احتمالی
        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);

        // 2. ساخت دو نسخه از شماره:
        // نسخه بدون صفر اول (مثلا: 9121112233)
        $phoneWithoutZero = ltrim($cleanPhone, '0');
        
        // نسخه با صفر اول (مثلا: 09121112233)
        $phoneWithZero = '0' . $phoneWithoutZero;

        // 3. جستجو در دیتابیس با شرط OR
        // یعنی: یا شماره دقیقاً برابر با حالت با صفر باشد، یا برابر با حالت بدون صفر
        $sql = "SELECT id, full_name, phone FROM guests WHERE phone = ? OR phone = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$phoneWithZero, $phoneWithoutZero]);
        
        $guest = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($guest) {
            echo json_encode([
                'status' => 'success', 
                'full_name' => $guest['full_name'], 
                'guest_id' => $guest['id']
            ]);
        } else {
            echo json_encode([
                'status' => 'error', 
                'message' => 'این شماره در لیست مهمان‌ها یافت نشد!'
            ]);
        }
        exit;
    }

    public function submit()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $phone = $_POST['phone'] ?? '';
        $expertName = $_POST['payment_expert_name'] ?? '';
        $guestId = $_POST['guest_id'] ?? '';
        
        // دریافت مبلغ و حذف ویرگول‌ها
        $amountRaw = $_POST['amount'] ?? '0';
        $amount = str_replace(',', '', $amountRaw);

        if (empty($guestId) || empty($phone)) {
            header('Location: ' . BASE_URL . '/payment?status=guest_not_found');
            exit;
        }

        if (isset($_FILES['receipt_image']) && $_FILES['receipt_image']['error'] === UPLOAD_ERR_OK) {
            
            $uploadDir = __DIR__ . '/../../public/uploads/receipts/';
            
            // اطمینان از وجود پوشه
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = $_FILES['receipt_image']['name'];
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $newFileName = time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
            $destPath = $uploadDir . $newFileName;

            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

            if (in_array($ext, $allowed)) {
                if(move_uploaded_file($_FILES['receipt_image']['tmp_name'], $destPath)) {
                    
                    try {
                        $this->pdo->beginTransaction();

                        // 1. درج در جدول پرداختی‌ها
                        $sql = "INSERT INTO payments (guest_id, registrar_expert, amount, receipt_image, created_at) VALUES (?, ?, ?, ?, NOW())";
                        $stmt = $this->pdo->prepare($sql);
                        
                        // چک کردن اینکه مقادیر نال نباشند
                        if(!$stmt->execute([$guestId, $expertName, $amount, $newFileName])) {
                            throw new PDOException("خطا در اجرای کوئری اینسرت");
                        }

                        // 2. آپدیت وضعیت مهمان
                        $updateSql = "UPDATE guests SET payment_status = 'paid' WHERE id = ?";
                        $stmtUpdate = $this->pdo->prepare($updateSql);
                        $stmtUpdate->execute([$guestId]);

                        $this->pdo->commit();
                        
                        header('Location: ' . BASE_URL . '/payment?status=success');
                        exit;

                    } catch (PDOException $e) {
                        $this->pdo->rollBack();
                        
                        // --- تغییر مهم برای دیباگ ---
                        // این خط را موقتا فعال کنید تا متن دقیق خطا را ببینید
                        echo "<div style='direction:ltr; text-align:left; background:#f8d7da; padding:20px; color:#721c24;'>";
                        echo "<h3>Database Error:</h3>";
                        echo "<p>" . $e->getMessage() . "</p>";
                        echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
                        echo "</div>";
                        die(); 
                        // -----------------------------
                        
                        // بعد از رفع مشکل، خطوط بالا را پاک کرده و خط زیر را از کامنت درآورید:
                        // header('Location: ' . BASE_URL . '/payment?status=db_error');
                        // exit;
                    }
                }
            }
        }
        header('Location: ' . BASE_URL . '/payment?status=upload_error');
        exit;
    }
}
}