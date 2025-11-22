<?php
namespace App\Controllers;

use App\Models\Seminar;
use App\Services\ExcelImporter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SeminarController {
    
    public function index() {
        $seminarModel = new Seminar();
        $seminars = $seminarModel->getAll();
        
        require_once __DIR__ . '/../Views/admin/dashboard.php';
    }

    public function create() {
        require_once __DIR__ . '/../Views/admin/create_seminar.php';
    }

    public function store() {
        $title = $_POST['title'];
        $date = $_POST['date'];

        $seminarModel = new Seminar();
        $seminarModel->create($title, $date);

        header('Location: ' . BASE_URL . '/admin');
        exit;
    }

    public function uploadPage() {
        $id = $_GET['id'] ?? null;
        if (!$id) die("ID نامعتبر");
        
        require_once __DIR__ . '/../Views/admin/upload_excel.php';
    }

    public function importExcel() {
        $seminarId = $_POST['seminar_id'];
        
        if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] == 0) {
            
            $fileTmpPath = $_FILES['excel_file']['tmp_name'];
            $fileName = $_FILES['excel_file']['name'];
            
            $uploadDir = __DIR__ . '/../../storage/uploads/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $dest_path = $uploadDir . $fileName;

            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $importer = new ExcelImporter();
                $count = $importer->import($dest_path, $seminarId);

                echo "<div style='color:green; text-align:center; font-family:tahoma; padding:50px; direction:rtl;'>
                        <h2>✅ عملیات موفق!</h2>
                        <p>تعداد <b>$count</b> مهمان به لیست اضافه شدند.</p>
                        <a href='" . BASE_URL . "/admin' style='font-size:18px;'>بازگشت به داشبورد</a>
                      </div>";
            } else {
                echo "خطا در آپلود فایل.";
            }
        } else {
            echo "فایلی انتخاب نشده است.";
        }
    }

    public function downloadSample() {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'نام و نام خانوادگی');
        $sheet->setCellValue('B1', 'شماره تماس');
        $sheet->setCellValue('C1', 'نام معرف (کارشناس)');

        $sheet->setCellValue('A2', 'علی رضایی');
        $sheet->setCellValue('B2', '09123456789');
        $sheet->setCellValue('C2', 'محمد محمدی');

        foreach(range('A','C') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="seminar_sample.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function activate() {
        $id = $_GET['id'] ?? null;
        
        if ($id) {
            $seminarModel = new Seminar();
            $seminarModel->setActive($id);
        }

        header('Location: ' . BASE_URL . '/admin');
        exit;
    }
}