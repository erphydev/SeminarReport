<?php
session_start();

// 1. تنظیمات نمایش خطا
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

use App\Controllers\SeminarController;
use App\Controllers\CheckInController;
use App\Controllers\ReportController;
use App\Controllers\AuthController;

// 2. محاسبه آدرس پایه (BASE_URL)
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$baseUrl = ($scriptName === '/') ? '' : $scriptName;
define('BASE_URL', $baseUrl);


// 3. دریافت و تمیز کردن آدرس درخواستی ($uri)
// ⚠️ این بخش حتما باید قبل از بخش امنیت باشد
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// حذف نام پوشه پروژه از ابتدای آدرس (برای اجرا در ساب‌فولدر)
if ($scriptName !== '/' && strpos($uri, $scriptName) === 0) {
    $uri = substr($uri, strlen($scriptName));
}
$uri = '/' . ltrim($uri, '/');


// 4. 🛡️ نگهبان امنیتی (Security Guard)
// حالا که $uri تعریف شده، می‌توانیم چکش کنیم
if (strpos($uri, '/admin') === 0) {
    // اگر کاربر ادمین نیست، برو به لاگین
    if (empty($_SESSION['is_admin'])) {
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}


// 5. تعریف مسیرها (Routes)
switch ($uri) {
    
    // --- 🔐 احراز هویت ---
    case '/login':
        (new AuthController())->showLoginForm();
        break;

    case '/auth/process':
        (new AuthController())->login();
        break;

    case '/logout':
        (new AuthController())->logout();
        break;


    // --- 🟢 بخش مهمان ---
    case '/':
    case '/checkin':
        (new CheckInController())->index();
        break;
    
    case '/checkin/verify':
        (new CheckInController())->verify();
        break;


    // --- 🔵 بخش ادمین ---
    case '/admin':
    case '/admin/dashboard':
        (new SeminarController())->index();
        break;

    case '/admin/seminar/create':
        $controller = new SeminarController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->store();
        } else {
            $controller->create();
        }
        break;

    case '/admin/seminar/upload':
        (new SeminarController())->uploadPage();
        break;

    case '/admin/seminar/import':
        (new SeminarController())->importExcel();
        break;

    case '/admin/seminar/download-sample':
        (new SeminarController())->downloadSample();
        break;
    
    case '/admin/seminar/activate':
        (new SeminarController())->activate();
        break;


    // --- 🟡 بخش گزارشات ---
    case '/admin/report':
        (new ReportController())->show();
        break;

    case '/admin/report/export-total':
        (new ReportController())->exportTotal();
        break;

    case '/admin/report/export-absent':
        (new ReportController())->exportAbsent();
        break;

    case '/admin/report/export-present':
        (new ReportController())->exportPresent();
        break;


    default:
        http_response_code(404);
        echo "<div style='text-align:center; margin-top:50px; font-family:tahoma;'>";
        echo "<h1>⚠️ خطای ۴۰۴</h1>";
        echo "<p>صفحه مورد نظر پیدا نشد.</p>";
        echo "<a href='".BASE_URL."/'>بازگشت به صفحه اصلی</a>";
        echo "</div>";
        break;
}