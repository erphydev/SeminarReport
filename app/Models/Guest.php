<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Guest {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // افزودن مهمان جدید
    public function create($seminarId, $expertId, $fullName, $phone) {
        $sql = "INSERT IGNORE INTO guests (seminar_id, expert_id, full_name, phone) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$seminarId, $expertId, $fullName, $phone]);
    }

    // پیدا کردن مهمان با شماره
    public function findByPhone($phone, $seminarId) {
        // برای پیدا کردن مهمان نیازی به جوین نیست، چون فقط اطلاعات خودش را می‌خواهیم
        $stmt = $this->db->prepare("SELECT * FROM guests WHERE phone = ? AND seminar_id = ?");
        $stmt->execute([$phone, $seminarId]);
        return $stmt->fetch();
    }

    // ثبت ورود و لاگ
    public function checkIn($guestId, $seminarId) {
        try {    
            $this->db->beginTransaction();

            $stmt1 = $this->db->prepare("UPDATE guests SET is_present = 1, checkin_time = NOW() WHERE id = ?");
            $stmt1->execute([$guestId]);

            $stmt2 = $this->db->prepare("INSERT INTO attendance_logs (guest_id, seminar_id) VALUES (?, ?)");
            $stmt2->execute([$guestId, $seminarId]);

            $this->db->commit();
            return true;

        } catch (\Exception $e) {    
            $this->db->rollBack();
            return false;
        }
    }
    
    // دریافت لیست غایبین (اصلاح شده)
    public function getAbsents($seminarId) {
        // تغییر JOIN به LEFT JOIN
        $sql = "SELECT g.full_name, g.phone, e.name as expert_name 
                FROM guests g
                LEFT JOIN experts e ON g.expert_id = e.id
                WHERE g.seminar_id = ? AND g.is_present = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$seminarId]);
        return $stmt->fetchAll();
    }

    // دریافت کل لیست (اصلاح شده)
    public function getAllBySeminar($seminarId) {
        // تغییر JOIN به LEFT JOIN
        $sql = "SELECT g.*, e.name as expert_name 
                FROM guests g 
                LEFT JOIN experts e ON g.expert_id = e.id 
                WHERE g.seminar_id = ?
                ORDER BY g.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$seminarId]);
        return $stmt->fetchAll();
    }

    // دریافت لیست حاضرین (اصلاح شده)
    public function getPresents($seminarId) {
        // تغییر JOIN به LEFT JOIN
        $sql = "SELECT g.*, e.name as expert_name 
                FROM guests g 
                LEFT JOIN experts e ON g.expert_id = e.id 
                WHERE g.seminar_id = ? AND g.is_present = 1
                ORDER BY g.checkin_time DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$seminarId]);
        return $stmt->fetchAll();
    }

    // دریافت شماره‌های حاضرین (برای پیامک)
    public function getPresentPhonesBySeminar($seminarId)
    {
        $stmt = $this->db->prepare("SELECT phone FROM guests WHERE seminar_id = :id AND is_present = 1");
        $stmt->execute(['id' => $seminarId]);
        
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    // آمار کارشناسان (اصلاح شده)
    public function getExpertStats($seminarId)
    {
        // اینجا هم LEFT JOIN کردیم تا آمار "ثبت دستی" هم به عنوان یک گروه (با نام NULL) بیاید
        $sql = "SELECT 
                    e.name as expert_name,
                    COUNT(g.id) as total_invited,
                    SUM(CASE WHEN g.is_present = 1 THEN 1 ELSE 0 END) as total_present
                FROM guests g
                LEFT JOIN experts e ON g.expert_id = e.id
                WHERE g.seminar_id = :id
                GROUP BY e.id, e.name
                ORDER BY total_present DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $seminarId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}