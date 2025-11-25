<?php

namespace App\Controllers;

use App\Config\Database; // استفاده از کلاس دیتابیس شما
use PDO;

class CheckInController
{
    private $db;

    public function __construct()
    {
        // تغییر مهم: استفاده از متد استاتیک کلاس دیتابیس شما
        $this->db = Database::getConnection();
    }

    /**
     * نمایش فرم ورودی (View)
     */
    public function index()
    {
        // دریافت سمینار فعال
        // فرض می‌کنیم در جدول seminars ستونی به نام is_active دارید
        $stmt = $this->db->prepare("SELECT * FROM seminars WHERE is_active = 1 LIMIT 1");
        $stmt->execute();
        $activeSeminar = $stmt->fetch();

        // اگر سمینار فعالی پیدا نشد، یک مقدار پیش‌فرض بگذار تا صفحه ارور ندهد
        if (!$activeSeminar) {
            $activeSeminar = ['id' => 0, 'title' => 'سمینار فعالی یافت نشد'];
        }

        // آدرس‌دهی فایل View
        // با توجه به عکس شما: app/Controllers/CheckInController.php
        // ویو در: app/Views/guest/checkin_form.php
        require_once __DIR__ . '/../Views/guest/checkin_form.php';
    }

    /**
     * پردازش شماره موبایل (AJAX)
     */
   public function verify()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $rawPhone = $_POST['phone'] ?? '';
            $seminarId = $_POST['seminar_id'] ?? 0;

            // ۱. تمیزکاری شماره: حذف همه چیز غیر از عدد
            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);

            if (empty($cleanPhone) || strlen($cleanPhone) < 10) {
                echo json_encode(['success' => false, 'message' => 'شماره موبایل نامعتبر است']);
                exit;
            }

            // ۲. استخراج ۱۰ رقم آخر شماره ورودی (نرمال‌سازی)
            // مثلا اگر 09121111111 بیاید تبدیل می‌شود به 9121111111
            // اگر 9121111111 بیاید همان می‌ماند
            $last10Digits = substr($cleanPhone, -10);

            // ۳. جستجو در دیتابیس با شرط هوشمند (RIGHT)
            // دستور SQL: ۱۰ رقم راستِ شماره داخل دیتابیس را با ۱۰ رقم ورودی مقایسه کن
            $sql = "SELECT * FROM guests 
                    WHERE RIGHT(phone, 10) = :phone 
                    AND seminar_id = :seminar_id 
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':phone', $last10Digits);
            $stmt->bindParam(':seminar_id', $seminarId);
            $stmt->execute();
            $guest = $stmt->fetch();

            if ($guest) {
                
                if ($guest['is_present'] == 1) {
                    echo json_encode([
                        'success' => true, 
                        'guest_name' => $guest['full_name'],
                        'message' => 'این مهمان قبلاً ثبت شده است.'
                    ]);
                    exit;
                }

                // ثبت ورود
                $currentTime = date('Y-m-d H:i:s');
                $updateStmt = $this->db->prepare("UPDATE guests SET is_present = 1, checkin_time = :time WHERE id = :id");
                $updateStmt->bindParam(':time', $currentTime);
                $updateStmt->bindParam(':id', $guest['id']);
                $updateStmt->execute();

                echo json_encode([
                    'success' => true,
                    'guest_name' => $guest['full_name'],
                    'message' => 'ورود با موفقیت ثبت شد'
                ]);

            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'شماره در لیست یافت نشد'
                ]);
            }

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'خطای سرور: ' . $e->getMessage()]);
        }
        exit;
    }
}