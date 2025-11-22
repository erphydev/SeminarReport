<?php
namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Expert;
use App\Models\Guest;

class ExcelImporter {
    
    public function import($filePath, $seminarId) {
        //Load exel file
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        //del first row
        array_shift($rows); 

        $expertModel = new Expert();
        $guestModel = new Guest();

        $count = 0;

        foreach ($rows as $row) {
            // فرض بر این است که ساختار اکسل اینگونه است:
            // ستون A (0): نام و نام خانوادگی
            // ستون B (1): شماره تماس
            // ستون C (2): نام معرف (کارشناس)
            
            $fullName = trim($row[0] ?? '');
            $phone = trim($row[1] ?? '');
            $expertName = trim($row[2] ?? '');

            //if data not compelet
            if (empty($fullName) || empty($phone) || empty($expertName)) {
                continue;
            }

            //find or creat expert
            $expertId = $expertModel->findOrCreate($expertName);

            //add gust 
            $guestModel->create($seminarId, $expertId, $fullName, $phone);
            
            $count++;
        }

        return $count; 
    }
}