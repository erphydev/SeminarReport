<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class GuestController
{
    private $db;

    public function __construct()
    {
        // اتصال به دیتابیس
        $this->db = Database::getConnection();
    }

   public function store()
    {
        // دریافت اطلاعات
        $seminarId = $_POST['seminar_id'] ?? null;
        $fullName = $_POST['full_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        
        // نکته کلیدی برای رفع ارور 1452:
        // مقدار کارشناس برای ثبت دستی باید دقیقاً null باشد
        $expertId = null; 
        
        $isPresent = isset($_POST['is_present']) ? 1 : 0;
        $checkinTime = $isPresent ? date('Y-m-d H:i:s') : null;

        if (empty($seminarId) || empty($fullName) || empty($phone)) {
            die("اطلاعات ناقص است.");
        }

        try {
            // 1. چک کردن شماره تکراری
            $checkStmt = $this->db->prepare("SELECT id FROM guests WHERE phone = :phone AND seminar_id = :seminar_id");
            $checkStmt->execute([':phone' => $phone, ':seminar_id' => $seminarId]);

            if ($checkStmt->rowCount() > 0) {
                // اگر تکراری بود برگرد
                header("Location: " . BASE_URL . "/admin?status=duplicate_error");
                exit;
            }

            // 2. درج در دیتابیس
            $sql = "INSERT INTO guests (seminar_id, full_name, phone, expert_id, is_present, checkin_time) 
                    VALUES (:seminar_id, :full_name, :phone, :expert_id, :is_present, :checkin_time)";
            
            $stmt = $this->db->prepare($sql);
            
            // بایند کردن پارامترها (به خصوص expert_id)
            $stmt->bindParam(':seminar_id', $seminarId);
            $stmt->bindParam(':full_name', $fullName);
            $stmt->bindParam(':phone', $phone);
            
            // نکته: برای NULL باید نوع پارامتر را مشخص کنیم تا به عنوان رشته خالی یا 0 رد نشود
            $stmt->bindValue(':expert_id', null, PDO::PARAM_NULL);
            
            $stmt->bindParam(':is_present', $isPresent);
            $stmt->bindParam(':checkin_time', $checkinTime);
            
            $stmt->execute();

            // موفقیت
            header("Location: " . BASE_URL . "/admin?status=guest_added");
            exit;

        } catch (\Exception $e) {
            // نمایش خطا برای دیباگ
            die("خطای دیتابیس: " . $e->getMessage());
        }
    }
}