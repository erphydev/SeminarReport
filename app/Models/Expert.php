<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Expert {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    //find expert name
    public function findOrCreate($name) {
        $name = trim($name);
        
        //search
        $stmt = $this->db->prepare("SELECT id FROM experts WHERE name = ?");
        $stmt->execute([$name]);
        $result = $stmt->fetch();

        if ($result) {
            return $result['id'];
        }

        //if not find
        $stmt = $this->db->prepare("INSERT INTO experts (name) VALUES (?)");
        $stmt->execute([$name]);
        return $this->db->lastInsertId();
    }

    //convertion rate
    public function getStatsBySeminar($seminarId) {
        $sql = "SELECT 
                    e.name AS expert_name,
                    COUNT(g.id) AS total_invited,
                    SUM(CASE WHEN g.is_present = 1 THEN 1 ELSE 0 END) AS total_present,
                    (SUM(CASE WHEN g.is_present = 1 THEN 1 ELSE 0 END) / COUNT(g.id)) * 100 AS conversion_rate
                FROM experts e
                JOIN guests g ON e.id = g.expert_id
                WHERE g.seminar_id = ?
                GROUP BY e.id
                ORDER BY conversion_rate DESC"; 

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$seminarId]);
        return $stmt->fetchAll();
    }
}