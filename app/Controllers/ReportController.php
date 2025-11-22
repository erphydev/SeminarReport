<?php
namespace App\Controllers;

use App\Models\Expert;
use App\Models\Guest;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController {

    public function show() {
        $seminarId = $_GET['id'] ?? null;
        if (!$seminarId) die("شناسه سمینار نامعتبر است.");

        $expertModel = new Expert();
        $stats = $expertModel->getStatsBySeminar($seminarId);

        $guestModel = new Guest();
        
        // 1. لیست کل
        $allGuests = $guestModel->getAllBySeminar($seminarId);
        // 2. لیست غایبین
        $absents = $guestModel->getAbsents($seminarId);
        // 3. لیست حاضرین (جدید)
        $presents = $guestModel->getPresents($seminarId);

        require_once __DIR__ . '/../Views/reports/conversion_rate.php';
    }

    // اکسل کل
    public function exportTotal() { $this->generateExcel('all'); }
    
    // اکسل غایبین
    public function exportAbsent() { $this->generateExcel('absent'); }

    // اکسل حاضرین (جدید)
    public function exportPresent() { $this->generateExcel('present'); }

    private function generateExcel($type) {
        $seminarId = $_GET['id'] ?? null;
        if (!$seminarId) die("ID نامعتبر");

        $guestModel = new Guest();
        
        // انتخاب دیتا بر اساس نوع درخواست
        if ($type === 'absent') {
            $data = $guestModel->getAbsents($seminarId);
            $filename = "absent_list.xlsx";
        } elseif ($type === 'present') {
            $data = $guestModel->getPresents($seminarId);
            $filename = "present_list.xlsx";
        } else {
            $data = $guestModel->getAllBySeminar($seminarId);
            $filename = "total_guests.xlsx";
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'نام مهمان');
        $sheet->setCellValue('B1', 'شماره تماس');
        $sheet->setCellValue('C1', 'نام کارشناس');
        $sheet->setCellValue('D1', 'زمان ورود / وضعیت');

        $rowNum = 2;
        foreach ($data as $row) {
            $sheet->setCellValue('A' . $rowNum, $row['full_name']);
            $sheet->setCellValue('B' . $rowNum, $row['phone']);
            $sheet->setCellValue('C' . $rowNum, $row['expert_name']);
            
            // ستون وضعیت/زمان
            if ($type === 'present') {
                $status = $row['checkin_time'];
            } elseif ($type === 'absent') {
                $status = 'غایب';
            } else {
                $status = $row['is_present'] ? 'حاضر (' . $row['checkin_time'] . ')' : 'غایب';
            }
            
            $sheet->setCellValue('D' . $rowNum, $status);
            $rowNum++;
        }

        foreach(range('A','D') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}