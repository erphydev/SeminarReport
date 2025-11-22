<?php
namespace App\Controllers;

use App\Models\Seminar;
use App\Models\Guest;

class CheckInController {

    public function index() {
        // پیدا کردن سمینار فعال
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

        $phone = $_POST['phone'] ?? '';
        $seminarId = $_POST['seminar_id'] ?? null;

        $guestModel = new Guest();
        $guest = $guestModel->findByPhone($phone, $seminarId);

        if ($guest) {
            // 🔴 تغییر مهم: نتیجه عملیات دیتابیس را چک می‌کنیم
            $isSuccess = $guestModel->checkIn($guest['id'], $seminarId);

            if ($isSuccess) {
                // فقط اگر واقعا در دیتابیس ثبت شد
                $guestName = $guest['full_name'];
                require_once __DIR__ . '/../Views/guest/success.php';
            } else {
                // اگر در دیتابیس خطا خورد
                $error = "❌ خطا در ثبت سیستم: عملیات دیتابیس شکست خورد. (جدول‌ها را چک کنید)";
                
                // دوباره فرم را نشان بده
                $seminarModel = new Seminar();
                $activeSeminar = $seminarModel->getActive();
                require_once __DIR__ . '/../Views/guest/checkin_form.php';
            }

        } else {
            $error = "شماره شما در لیست مهمانان این سمینار یافت نشد.";
            
            $seminarModel = new Seminar();
            $activeSeminar = $seminarModel->getActive();
            require_once __DIR__ . '/../Views/guest/checkin_form.php';
        }
    }
}