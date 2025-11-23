<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Guest {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // add new guest
    public function create($seminarId, $expertId, $fullName, $phone) {
        $sql = "INSERT IGNORE INTO guests (seminar_id, expert_id, full_name, phone) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$seminarId, $expertId, $fullName, $phone]);
    }

    //find guest from phone number
    public function findByPhone($phone, $seminarId) {
        $stmt = $this->db->prepare("SELECT * FROM guests WHERE phone = ? AND seminar_id = ?");
        $stmt->execute([$phone, $seminarId]);
        return $stmt->fetch();
    }

    // Check in and log    
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
    
    // Get absnt Lists
    public function getAbsents($seminarId) {
        $sql = "SELECT g.full_name, g.phone, e.name as expert_name 
                FROM guests g
                JOIN experts e ON g.expert_id = e.id
                WHERE g.seminar_id = ? AND g.is_present = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$seminarId]);
        return $stmt->fetchAll();
    }

    public function getAllBySeminar($seminarId) {
        $sql = "SELECT g.*, e.name as expert_name 
                FROM guests g 
                JOIN experts e ON g.expert_id = e.id 
                WHERE g.seminar_id = ?
                ORDER BY g.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$seminarId]);
        return $stmt->fetchAll();
    }

        public function getPresents($seminarId) {
        $sql = "SELECT g.*, e.name as expert_name 
                FROM guests g 
                JOIN experts e ON g.expert_id = e.id 
                WHERE g.seminar_id = ? AND g.is_present = 1
                ORDER BY g.checkin_time DESC"; // مرتب‌سازی بر اساس زمان ورود
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$seminarId]);
        return $stmt->fetchAll();
    }
}