<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Seminar {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    //Get list of seminars for admin panel 
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM seminars ORDER BY date DESC, id DESC");
        return $stmt->fetchAll();
    }

    //Creat new semniar
    public function create($title, $date) {
        $stmt = $this->db->prepare("INSERT INTO seminars (title, date) VALUES (?, ?)");
        return $stmt->execute([$title, $date]);
    }

    //find active seminar 
    public function getActive() {
        $stmt = $this->db->query("SELECT * FROM seminars WHERE is_active = 1 LIMIT 1");
        return $stmt->fetch();
    }

    //chanage seminar status 
    public function setActive($id) {
        $this->db->query("UPDATE seminars SET is_active = 0");
        $stmt = $this->db->prepare("UPDATE seminars SET is_active = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}