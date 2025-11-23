<?php
namespace App\Controllers;

use App\Models\Seminar;
use App\Models\Guest;

class CheckInController {

    public function index() {

        //find active seminar

        $seminarModel = new Seminar();
        $activeSeminar = $seminarModel->getActive();

        if (!$activeSeminar) {
            echo "<div style='text-align:center; margin-top:50px; font-family:tahoma;'>
                    <h2>⛔ هیچ سمینار فعالی وجود ندارد</h2>
                    <p>لطفاً با مدیریت تماس بگیرید.</p>
                  </div>";
            return;
        }

        require_once __DIR__ . '/../Views/guest/checkin_form.php';
    }

    public function verify() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/');
            exit;
        }

        $rawPhone = $_POST['phone'] ?? '';
        $seminarId = $_POST['seminar_id'] ?? null;

        $cleanPhone = trim($rawPhone);
        
        $phoneNoZero = ltrim($cleanPhone, '0');   // حالت بدون صفر (مثلا 912...)
        $phoneWithZero = '0' . $phoneNoZero;      // حالت با صفر (مثلا 0912...)

        $guestModel = new Guest();

        $guest = $guestModel->findByPhone($phoneWithZero, $seminarId);

        if (!$guest) {
            $guest = $guestModel->findByPhone($phoneNoZero, $seminarId);
        }

        if ($guest) {
            $isSuccess = $guestModel->checkIn($guest['id'], $seminarId);

            if ($isSuccess) {
                $guestName = $guest['full_name'];
                require_once __DIR__ . '/../Views/guest/success.php';
            } else {
                $error = "❌ خطا در ثبت سیستم: عملیات دیتابیس شکست خورد.";
                
                $seminarModel = new Seminar();
                $activeSeminar = $seminarModel->getActive();
                require_once __DIR__ . '/../Views/guest/checkin_form.php';
            }

        } else {
            $error = "شماره شما ($cleanPhone) در لیست مهمانان یافت نشد.";
            
            $seminarModel = new Seminar();
            $activeSeminar = $seminarModel->getActive();
            require_once __DIR__ . '/../Views/guest/checkin_form.php';
        }
    }
}