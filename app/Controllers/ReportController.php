<?php

namespace App\Controllers;

use App\Models\Guest;
use App\Services\SmsService;

class ReportController {

    public function show() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: " . BASE_URL . "/admin");
            exit;
        }

        $guestModel = new Guest();
        
        // ۱. دریافت کل مهمان‌ها
        // (چون در مدل LEFT JOIN زدیم، ثبت‌نام‌های دستی هم می‌آیند)
        $allGuests = $guestModel->getAllBySeminar($id);

        // --- مرتب‌سازی اولیه: کل لیست بر اساس نام (الفبا) ---
        usort($allGuests, function($a, $b) {
            return strcmp($a['full_name'], $b['full_name']);
        });

        // ۲. تفکیک لیست‌ها
        $presents = array_filter($allGuests, function($guest) {
            return $guest['is_present'] == 1;
        });

        $absents = array_filter($allGuests, function($guest) {
            return $guest['is_present'] == 0;
        });

        // --- مرتب‌سازی ثانویه: حاضرین بر اساس زمان ورود (جدیدترین بالا) ---
        usort($presents, function($a, $b) {
            $t1 = $a['checkin_time'] ?? '0';
            $t2 = $b['checkin_time'] ?? '0';
            return strcmp($t2, $t1); // نزولی
        });

        // ۳. آمار کارشناسان
        $rawStats = method_exists($guestModel, 'getExpertStats') ? $guestModel->getExpertStats($id) : [];
        
        $stats = [];
        foreach ($rawStats as $row) {
            if ($row['total_invited'] > 0) {
                $row['conversion_rate'] = ($row['total_present'] / $row['total_invited']) * 100;
            } else {
                $row['conversion_rate'] = 0;
            }
            $stats[] = $row;
        }

        // --- مرتب‌سازی کارشناسان: بر اساس درصد موفقیت ---
        usort($stats, function($a, $b) {
            if ($a['conversion_rate'] == $b['conversion_rate']) {
                return 0;
            }
            return ($a['conversion_rate'] > $b['conversion_rate']) ? -1 : 1;
        });

        // فراخوانی ویو (مسیر اصلاح شده به داشبورد ادمین)
        require_once __DIR__ . '/../Views/reports/report_view.php';
    }

    // متد ارسال پیامک
    public function sendSms() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $seminarId = $_POST['seminar_id'];
            $message = $_POST['message'];

            $guestModel = new Guest();
            $allGuests = $guestModel->getAllBySeminar($seminarId);
            
            // فیلتر کردن حاضرین
            $presents = array_filter($allGuests, function($guest) {
                return $guest['is_present'] == 1;
            });

            // استخراج شماره موبایل‌ها
            $phoneNumbers = array_column($presents, 'phone');

            if (empty($phoneNumbers)) {
                header("Location: " . BASE_URL . "/admin/report?id=" . $seminarId . "&status=empty_list");
                exit;
            }

            $smsService = new SmsService();
            $result = $smsService->sendBulk(array_values($phoneNumbers), $message);

            // بررسی نتیجه ارسال
            if ($result && !is_numeric($result) && (strpos($result, 'Error') !== false || $result == '')) {
                header("Location: " . BASE_URL . "/admin/report?id=" . $seminarId . "&status=api_error");
            } else {
                header("Location: " . BASE_URL . "/admin/report?id=" . $seminarId . "&status=sent");
            }
            exit;
        }
    }

    // ==========================================
    // بخش خروجی اکسل (Excel Export)
    // ==========================================

    public function exportTotal() { $this->exportExcel($_GET['id'], 'all'); }
    public function exportPresent() { $this->exportExcel($_GET['id'], 'present'); }
    public function exportAbsent() { $this->exportExcel($_GET['id'], 'absent'); }

    private function exportExcel($seminarId, $type) {
        $guestModel = new Guest();
        $guests = $guestModel->getAllBySeminar($seminarId);

        // مرتب‌سازی بر اساس نام
        usort($guests, function($a, $b) {
            return strcmp($a['full_name'], $b['full_name']);
        });

        // فیلتر کردن داده‌ها
        $titleFileType = '';
        if ($type === 'present') {
            $guests = array_filter($guests, fn($g) => $g['is_present'] == 1);
            $titleFileType = 'حاضرین';
        } elseif ($type === 'absent') {
            $guests = array_filter($guests, fn($g) => $g['is_present'] == 0);
            $titleFileType = 'غایبین';
        } else {
            $titleFileType = 'کل مهمانان';
        }

        // نام فایل خروجی
        $filename = 'Report_' . $type . '_' . date('Y-m-d_H-i') . '.xls';

        // هدرهای مخصوص اکسل
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head>';
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
        echo '<style>
                table { border-collapse: collapse; width: 100%; font-family: Tahoma, sans-serif; }
                th { background-color: #4CAF50; color: white; border: 1px solid #000; padding: 10px; }
                td { border: 1px solid #ddd; padding: 8px; text-align: center; }
                .present { color: green; font-weight: bold; }
                .absent { color: red; font-weight: bold; }
              </style>';
        echo '</head>';
        echo '<body>';
        
        echo '<h2 style="text-align:center;">گزارش ' . $titleFileType . ' سمینار</h2>';
        echo '<table>';
        
        echo '<thead>
                <tr>
                    <th>نام و نام خانوادگی</th>
                    <th>شماره تماس</th>
                    <th>نام کارشناس</th>
                    <th>وضعیت</th>
                    <th>زمان ورود</th>
                </tr>
              </thead>';
        
        echo '<tbody>';
        foreach ($guests as $row) {
            $statusText = $row['is_present'] ? 'حاضر ✅' : 'غایب ❌';
            $statusClass = $row['is_present'] ? 'present' : 'absent';
            $checkin = $row['checkin_time'] ?? '-';
            
            // اصلاح نام کارشناس برای اکسل (اگر خالی بود بنویس ثبت دستی)
            $expertName = !empty($row['expert_name']) ? $row['expert_name'] : 'ثبت دستی / آزاد';

            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['full_name']) . '</td>';
            echo '<td style="mso-number-format:\@;">' . $row['phone'] . '</td>'; 
            echo '<td>' . htmlspecialchars($expertName) . '</td>';
            echo '<td class="'.$statusClass.'">' . $statusText . '</td>';
            echo '<td dir="ltr">' . $checkin . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</body>';
        echo '</html>';
        exit;
    }
}