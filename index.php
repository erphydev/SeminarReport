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
use App\Controllers\PaymentController;

// ---------------------------------------------------------
// گام ۱: تشخیص مسیر و تعریف BASE_URL (اصلاح شده)
// ---------------------------------------------------------

// 1. به دست آوردن نام پوشه پروژه (مثلاً /Sem)
$basePath = dirname($_SERVER['SCRIPT_NAME']);
// تبدیل بک‌اسلش به اسلش (برای سازگاری لینوکس و ویندوز)
$basePath = str_replace('\\', '/', $basePath);

// 2. به دست آوردن آدرس درخواستی تمیز (بدون پارامترهای ؟id=...)
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 3. حذف نام پوشه از آدرس درخواستی (برای درست کار کردن Switch Case)
// مثال: تبدیل /Sem/payment به /payment
if ($basePath !== '/' && strpos($requestUri, $basePath) === 0) {
    $relativePath = substr($requestUri, strlen($basePath));
} else {
    $relativePath = $requestUri;
}

// 4. استانداردسازی نهایی متغیر uri (برای استفاده در switch)
$uri = '/' . trim($relativePath, '/');

// 5. تعریف ثابت BASE_URL (برای استفاده در لینک‌های HTML و ریدایرکت‌ها)
// اگر اسکریپت در پوشه Sem باشد، این مقدار /Sem می‌شود تا لینک‌ها شکسته نشوند
define('BASE_URL', ($basePath === '/') ? '' : $basePath);

// دیباگ موقت (اگر باز هم کار نکرد، خط زیر را از کامنت درآورید تا ببینید چه چیزی چاپ می‌شود)
// echo "Base: $basePath <br> URI: $uri"; exit;



// ---------------------------------------------------------
// گام ۲: بررسی امنیت و لاگین ادمین
// ---------------------------------------------------------
if (strpos($uri, '/admin') === 0) {
    if (empty($_SESSION['is_admin'])) {
        // حالا BASE_URL تعریف شده و خطا نمی‌دهد
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}

// ---------------------------------------------------------
// گام ۳: روتینگ (Switch Case)
// ---------------------------------------------------------
switch ($uri) {
    
    // --- Auth ---
    case '/login':
        (new AuthController())->showLoginForm();
        break;

    case '/auth/process':
        (new AuthController())->login();
        break;

    case '/logout':
        (new AuthController())->logout();
        break;


    // --- Guests / Check-in ---
    case '/':
    case '/checkin':
        (new CheckInController())->index();
        break;
    
    case '/checkin/verify':
        (new CheckInController())->verify();
        break;

    case '/checkin/register':
        (new CheckInController())->register();
        break;


    // --- Admin Dashboard ---
    case '/admin':
    case '/admin/dashboard':
        (new SeminarController())->index();
        break;

    // --- Seminars ---
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


    // --- Reports ---
    case '/admin/report':
    case '/admin/conversion_rate':
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

    case '/admin/report/send-sms':
        (new ReportController())->sendSms();
        break;

    // --- Guests Admin ---
    case '/admin/guest/store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new \App\Controllers\GuestController())->store();
        }
        break;

    // --- PAYMENT SECTION (بخش پرداخت) ---
    case '/payment':
        if (class_exists('App\Controllers\PaymentController')) {
            (new PaymentController())->index();
        } else {
            die("Error: PaymentController not found. Run 'composer dump-autoload'");
        }
        break;

    case '/payment/check-phone':
         // روت جدید برای بررسی شماره موبایل
        if (class_exists('App\Controllers\PaymentController')) {
            (new PaymentController())->checkPhone();
        }
        break;

    case '/payment/submit':
        if (class_exists('App\Controllers\PaymentController')) {
            (new PaymentController())->submit();
        }
        break;

    // --- 404 Error ---
    default:
        http_response_code(404);
        echo "<div style='text-align:center; margin-top:50px; font-family:tahoma; direction:rtl;'>";
        echo "<h1>⚠️ خطای ۴۰۴</h1>";
        echo "<p>صفحه مورد نظر پیدا نشد.</p>";
        echo "<p style='color:gray; font-size:12px;'>مسیر درخواست شده: <b>" . htmlspecialchars($uri) . "</b></p>";
        echo "<a href='".BASE_URL."/'>بازگشت به صفحه اصلی</a>";
        echo "</div>";
        break;
}