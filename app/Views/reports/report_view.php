<?php

require_once __DIR__ . '/../layouts/header.php';
use App\Services\JalaliDate;

// -------------------------------------------------------------------
// تنظیمات اتصال به دیتابیس
// -------------------------------------------------------------------
$host = $_SERVER['DB_HOST'] ?? 'localhost';
$dbName = $_SERVER['DB_NAME'] ?? 'salescoaching_seminar';
$user = $_SERVER['DB_USER'] ?? 'root';
$pass = $_SERVER['DB_PASS'] ?? '';

$seminarId = $_GET['id'] ?? 0;
$paymentList = [];
$totalRevenue = 0;

try {
    $pdoReport = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $user, $pass);
    $pdoReport->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. دریافت واریزی‌ها (فقط مخصوص همین سمینار)
    $stmtPay = $pdoReport->prepare("
        SELECT 
            p.*, 
            g.full_name, 
            g.phone,
            g.seminar_id
        FROM payments p
        INNER JOIN guests g ON p.guest_id = g.id
        WHERE g.seminar_id = :id
        ORDER BY p.created_at DESC
    ");
    
    $stmtPay->execute([':id' => $seminarId]);
    $paymentList = $stmtPay->fetchAll(PDO::FETCH_ASSOC);

    // محاسبه درآمد
    foreach ($paymentList as $p) {
        $totalRevenue += $p['amount'];
    }

} catch (Exception $e) {
    $paymentList = [];
}

// -------------------------------------------------------------------
// 2. فیلتر کردن و محاسبات آماری مهمانان
// -------------------------------------------------------------------
$allGuests = $allGuests ?? [];
$presents = $presents ?? [];
$absents = $absents ?? [];
$stats = $stats ?? [];

$walkIns = array_filter($allGuests, fn ($guest) => empty($guest['expert_id']));
$invitedPresents = array_filter($presents, fn ($guest) => !empty($guest['expert_id']));
$invitedAbsents = array_filter($absents, fn ($guest) => !empty($guest['expert_id']));

$totalInvited = count(array_filter($allGuests, fn ($guest) => !empty($guest['expert_id'])));
$invitedPresentCount = count($invitedPresents);
$walkInCount = count($walkIns);
$absentCount = count($invitedAbsents);
$paymentCount = count($paymentList); 
$presentCount = $invitedPresentCount + $walkInCount;
$presentPercent = $totalInvited > 0 ? round(($invitedPresentCount / $totalInvited) * 100) : 0;

// داده‌های چارت
$expertNames = [];
$expertInvites = [];
$expertPresents = [];
foreach ($stats as $s) {
    if (!empty($s['expert_name'])) {
        $expertNames[] = $s['expert_name'];
        $expertInvites[] = $s['total_invited'];
        $expertPresents[] = $s['total_present'];
    }
}

// توابع کمکی
function getInitials($name) {
    $parts = explode(' ', trim($name ?? ''));
    if (count($parts) >= 2) return mb_substr($parts[0], 0, 1) . ' ' . mb_substr($parts[1], 0, 1);
    return mb_substr($name ?? 'U', 0, 2);
}
function getAvatarColor($name) {
    $colors = ['#6366f1', '#8b5cf6', '#ec4899', '#f43f5e', '#f59e0b', '#10b981', '#06b6d4', '#3b82f6'];
    return $colors[abs(crc32($name ?? 'def')) % count($colors)];
}
?>

<!-- استایل‌ها -->
<link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js@10.0.1/dist/css/shepherd.css"/>

<style>
    :root {
        --font-main: 'Vazirmatn', sans-serif;
        --bg-body: #f8fafc;
        --card-bg: #ffffff;
        --text-main: #1e293b;
        --primary: #4f46e5;
        --card-radius: 16px;
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    }
    body { font-family: var(--font-main) !important; background-color: var(--bg-body); color: var(--text-main); }
    .card { border: none; border-radius: var(--card-radius); background: var(--card-bg); box-shadow: var(--shadow-md); transition: transform 0.2s; }
    .card:hover { transform: translateY(-2px); }
    .btn-gradient { background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); color: white; border: none; }
    .stat-card { position: relative; overflow: hidden; border-radius: var(--card-radius); }
    .stat-card .icon-box { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    .stat-card.blue .icon-box { background: #e0e7ff; color: #4338ca; }
    .stat-card.green .icon-box { background: #dcfce7; color: #15803d; }
    .stat-card.teal .icon-box { background: #ccfbf1; color: #0f766e; }
    .stat-card.yellow .icon-box { background: #fef9c3; color: #a16207; }
    .stat-card.red .icon-box { background: #fee2e2; color: #991b1b; }
    .expert-card { text-align: center; padding: 1.5rem; border: 1px solid #f1f5f9; background: linear-gradient(to bottom, #fff 0%, #f8fafc 100%); }
    .expert-rank-badge { width: 30px; height: 30px; border-radius: 50%; position: absolute; top: 10px; right: 10px; display: flex; align-items: center; justify-content: center; font-weight: bold; color: white; font-size: 0.8rem; }
    .rank-1 .expert-rank-badge { background: linear-gradient(45deg, #FFD700, #FDB931); }
    .rank-other .expert-rank-badge { background: #cbd5e1; color: #475569; }
    .table-modern thead th { background: #f8fafc; color: #64748b; font-size: 0.85rem; font-weight: 600; padding: 1rem; border-bottom: 2px solid #e2e8f0; }
    .table-modern tbody td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; color: #334155; }
    .nav-pills-custom { background: #f1f5f9; padding: 5px; border-radius: 12px; display: inline-flex; }
    .nav-pills-custom .nav-link { color: #64748b; font-weight: 500; padding: 8px 18px; border-radius: 10px; transition: all 0.3s; }
    .nav-pills-custom .nav-link.active { background: white; color: #0f172a; box-shadow: var(--shadow-md); }
    .avatar { width: 36px; height: 36px; border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: bold; }
    @media print { .no-print { display: none !important; } .card { box-shadow: none; border: 1px solid #ddd; } }
</style>

<div class="container-fluid py-5 px-lg-5">

    <!-- هدر صفحه -->
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-5 animate__animated animate__fadeIn">
        <div>
            <div class="d-flex align-items-center mb-2">
                <span class="badge bg-white text-primary border me-2 rounded-pill">سمینار ID: <?= $seminarId ?></span>
                <span class="text-muted small"><i class="bi bi-calendar-event me-1"></i><?= JalaliDate::format(date('Y-m-d'), 'd F Y') ?></span>
            </div>
            <h2 class="fw-bolder text-dark mb-0">داشبورد گزارش رویداد</h2>
        </div>
        
        <div class="d-flex gap-2 mt-3 mt-lg-0 no-print flex-wrap">
            <button onclick="window.print()" class="btn btn-white border shadow-sm"><i class="bi bi-printer me-2"></i>چاپ</button>
            <div class="dropdown">
                <button class="btn btn-white border shadow-sm dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-2"></i>اکسل
                </button>
                <ul class="dropdown-menu border-0 shadow-lg p-2 rounded-3">
                    <li><a class="dropdown-item rounded" href="<?= BASE_URL ?>/admin/report/export-total?id=<?= $seminarId ?>">کل لیست</a></li>
                    <li><a class="dropdown-item rounded" href="<?= BASE_URL ?>/admin/report/export-present?id=<?= $seminarId ?>">حاضرین</a></li>
                    <li><a class="dropdown-item rounded" href="<?= BASE_URL ?>/admin/report/export-absent?id=<?= $seminarId ?>">غایبین</a></li>
                </ul>
            </div>
            <button class="btn btn-gradient shadow-sm" data-bs-toggle="modal" data-bs-target="#addGuestModalReport">
                <i class="bi bi-person-plus-fill me-2"></i>ثبت مهمان
            </button>
            <button class="btn btn-warning text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#smsModal">
                <i class="bi bi-chat-text-fill me-2"></i>پیامک
            </button>
        </div>
    </div>

    <!-- آمار کلی -->
    <div class="row g-4 mb-5">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card stat-card blue h-100 p-4">
                <div class="d-flex justify-content-between">
                    <div><p class="text-muted fw-bold small mb-1">کل دعوت‌ها</p><h4 class="fw-bolder text-dark mb-0"><?= number_format($totalInvited) ?></h4></div>
                    <div class="icon-box"><i class="bi bi-people-fill"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card stat-card green h-100 p-4">
                <div class="d-flex justify-content-between">
                    <div><p class="text-muted fw-bold small mb-1">حاضرین</p><h4 class="fw-bolder text-success mb-0"><?= number_format($invitedPresentCount) ?></h4></div>
                    <div class="icon-box"><i class="bi bi-person-check-fill"></i></div>
                </div>
            </div>
        </div>
        <!-- باکس درآمد کل -->
        <div class="col-xl-4 col-md-8 col-12">
            <div class="card stat-card teal h-100 p-4 border-primary border-opacity-25" style="background: linear-gradient(to right, #ffffff, #f0fdfa);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted fw-bold small mb-1 text-uppercase">مجموع درآمد این سمینار</p>
                        <h2 class="fw-bolder text-dark mb-0 mt-1"><?= number_format($totalRevenue) ?> <span class="fs-6 text-muted fw-normal">تومان</span></h2>
                    </div>
                    <div class="icon-box" style="width:55px;height:55px;"><i class="bi bi-wallet2"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card stat-card yellow h-100 p-4">
                <div class="d-flex justify-content-between">
                    <div><p class="text-muted fw-bold small mb-1">ثبت دستی</p><h4 class="fw-bolder text-warning mb-0"><?= number_format($walkInCount) ?></h4></div>
                    <div class="icon-box"><i class="bi bi-person-plus-fill"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card stat-card red h-100 p-4">
                <div class="d-flex justify-content-between">
                    <div><p class="text-muted fw-bold small mb-1">غایبین</p><h4 class="fw-bolder text-danger mb-0"><?= number_format($absentCount) ?></h4></div>
                    <div class="icon-box"><i class="bi bi-person-x-fill"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- نمودارها -->
    <div class="row g-4 mb-5">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-dark">📊 عملکرد کارشناسان</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="expertsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-dark">📈 وضعیت کلی حضور</h5>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <div style="position: relative; height: 220px; width: 100%;">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                    <div class="mt-3 text-center small text-muted">میانگین حضور مهمانان</div>
                </div>
            </div>
        </div>
    </div>

    <!-- رتبه‌بندی کارشناسان -->
    <div class="mb-5">
        <h5 class="fw-bold text-dark mb-4 px-1">🏆 برترین کارشناسان</h5>
        <div class="row g-3">
            <?php
            $rank = 1;
            foreach ($stats as $row) :
                if (empty($row['expert_name'])) continue;
                $rate = round($row['conversion_rate']);
                $rankClass = $rank <= 3 ? "rank-$rank" : "rank-other";
                if ($rank > 6) break;
            ?>
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card expert-card h-100 <?= $rankClass ?>">
                        <div class="expert-rank-badge"><?= $rank ?></div>
                        <div class="mb-2"><span class="h4 fw-bolder text-dark"><?= $rate ?></span><small class="text-muted">%</small></div>
                        <h6 class="text-truncate fw-bold mb-1" title="<?= $row['expert_name'] ?>"><?= htmlspecialchars($row['expert_name']) ?></h6>
                        <small class="text-muted d-block mb-3">نرخ تبدیل</small>
                        <div class="d-flex justify-content-center gap-3 border-top pt-2">
                            <div class="text-center"><span class="d-block fw-bold text-success"><?= $row['total_present'] ?></span><small style="font-size:10px">حاضر</small></div>
                            <div class="text-center"><span class="d-block fw-bold text-secondary"><?= $row['total_invited'] ?></span><small style="font-size:10px">کل</small></div>
                        </div>
                    </div>
                </div>
            <?php $rank++; endforeach; ?>
        </div>
    </div>

    <!-- جداول -->
    <div class="card">
        <div class="card-header bg-white border-0 py-4 px-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <ul class="nav nav-pills-custom" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#payments">💳 واریزی‌ها <span class="badge bg-dark rounded-pill ms-1"><?= $paymentCount ?></span></button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#present">حاضرین دعوتی</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#walkin">ثبت دستی</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#absent">غایبین</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#total">کل لیست</button></li>
            </ul>
            <div class="position-relative w-100 w-md-auto">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" id="tableSearch" class="form-control bg-light border-0 ps-5" style="border-radius:10px" placeholder="جستجو...">
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="tab-content">
                
                <!-- Tab: Payments (تمیز شده و فیلتر شده) -->
                <div class="tab-pane fade show active" id="payments">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0 w-100">
                            <thead>
                                <tr>
                                    <th class="ps-4">نام پرداخت کننده</th>
                                    <th>تلفن</th>
                                    <th>مبلغ (تومان)</th>
                                    <th>کارشناس ثبت</th>
                                    <th>تاریخ پرداخت</th>
                                    <th class="text-end pe-4">فیش</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($paymentList)): ?>
                                    <tr><td colspan="6" class="text-center py-5 text-muted">هیچ واریزی برای این سمینار ثبت نشده است.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($paymentList as $pay): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                <?= htmlspecialchars($pay['full_name'] ?? 'نامشخص') ?>
                                            </div>
                                        </td>
                                        <td class="font-monospace text-muted"><?= $pay['phone'] ?></td>
                                        <td class="fw-bold text-primary fs-6">
                                            <?= number_format($pay['amount']) ?>
                                        </td>
                                        <td><span class="badge bg-secondary bg-opacity-10 text-dark border"><?= htmlspecialchars($pay['registrar_expert'] ?? '-') ?></span></td>
                                        <td class="text-muted small"><?= JalaliDate::format($pay['created_at'], 'Y/m/d H:i') ?></td>
                                        <td class="text-end pe-4">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" 
                                                    onclick="showReceipt('<?= BASE_URL ?>/public/uploads/receipts/<?= $pay['receipt_image'] ?>')">
                                                <i class="bi bi-eye-fill me-1"></i> مشاهده
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab: Present -->
                <div class="tab-pane fade" id="present">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0 w-100">
                            <thead><tr><th class="ps-4">مهمان</th><th>تلفن تماس</th><th>کارشناس</th><th class="text-end pe-4">ورود</th></tr></thead>
                            <tbody>
                                <?php foreach ($invitedPresents as $guest): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar" style="background:<?= getAvatarColor($guest['full_name']) ?>"><?= getInitials($guest['full_name']) ?></div>
                                            <div class="ms-2 fw-bold"><?= htmlspecialchars($guest['full_name']) ?></div>
                                        </div>
                                    </td>
                                    <td class="text-muted font-monospace"><?= $guest['phone'] ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($guest['expert_name']) ?></span></td>
                                    <td class="text-end pe-4 font-monospace text-muted" dir="ltr"><?= JalaliDate::format($guest['checkin_time'], 'H:i') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab: Walkin -->
                <div class="tab-pane fade" id="walkin">
                     <div class="table-responsive">
                        <table class="table table-modern mb-0 w-100">
                            <thead><tr><th class="ps-4">مهمان</th><th>تلفن تماس</th><th>نوع</th><th class="text-end pe-4">ورود</th></tr></thead>
                            <tbody>
                                <?php foreach ($walkIns as $guest): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-warning text-dark"><i class="bi bi-person"></i></div>
                                            <div class="ms-2 fw-bold"><?= htmlspecialchars($guest['full_name']) ?></div>
                                        </div>
                                    </td>
                                    <td class="text-muted font-monospace"><?= $guest['phone'] ?></td>
                                    <td><span class="badge bg-warning text-dark bg-opacity-25">ثبت دستی</span></td>
                                    <td class="text-end pe-4 font-monospace text-muted" dir="ltr"><?= JalaliDate::format($guest['checkin_time'], 'H:i') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab: Absent -->
                <div class="tab-pane fade" id="absent">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0 w-100">
                            <thead><tr><th class="ps-4">مهمان</th><th>تلفن تماس</th><th>کارشناس</th><th class="text-end pe-4">عملیات</th></tr></thead>
                            <tbody>
                                <?php foreach ($invitedAbsents as $guest): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-light text-secondary"><i class="bi bi-person"></i></div>
                                            <div class="ms-2 fw-bold text-secondary"><?= htmlspecialchars($guest['full_name']) ?></div>
                                        </div>
                                    </td>
                                    <td class="text-muted font-monospace"><?= $guest['phone'] ?></td>
                                    <td><?= htmlspecialchars($guest['expert_name']) ?></td>
                                    <td class="text-end pe-4"><a href="tel:<?= $guest['phone'] ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 no-print">تماس</a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab: Total -->
                <div class="tab-pane fade" id="total">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0 w-100">
                            <thead><tr><th class="ps-4">نام</th><th>تلفن</th><th>کارشناس</th><th class="text-center">وضعیت</th></tr></thead>
                            <tbody>
                                <?php foreach ($allGuests as $guest): ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?= htmlspecialchars($guest['full_name']) ?></td>
                                    <td class="font-monospace text-muted"><?= $guest['phone'] ?></td>
                                    <td><?= !empty($guest['expert_name']) ? htmlspecialchars($guest['expert_name']) : '<span class="text-muted small">--</span>' ?></td>
                                    <td class="text-center">
                                        <?php if($guest['is_present']): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">حاضر</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">غایب</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Show Receipt -->
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-body text-center position-relative p-0">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 p-2 bg-dark rounded-circle" data-bs-dismiss="modal" style="z-index: 10;"></button>
                <img id="receiptImageSrc" src="" class="img-fluid rounded-3 shadow-lg" style="max-height: 85vh; object-fit: contain;">
                <div class="mt-3">
                    <a id="downloadLink" href="" download class="btn btn-light rounded-pill px-4 shadow"><i class="bi bi-download me-2"></i>دانلود تصویر</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Guest -->
<div class="modal fade" id="addGuestModalReport" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">ثبت مهمان جدید</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>/admin/guest/store" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="seminar_id" value="<?= $seminarId ?>">
                    <div class="mb-3">
                        <label class="form-label small text-muted">نام و نام خانوادگی</label>
                        <input type="text" name="full_name" class="form-control bg-light border-0 py-3" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small text-muted">شماره موبایل</label>
                        <input type="tel" name="phone" class="form-control bg-light border-0 py-3" required>
                    </div>
                    <div class="form-check form-switch p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                        <label class="form-check-label fw-bold ms-2" for="chkPR">ثبت حضور هم‌زمان</label>
                        <input class="form-check-input m-0" type="checkbox" name="is_present" value="1" id="chkPR" checked style="width: 3em; height: 1.5em;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-gradient rounded-pill px-4 shadow-sm">ذخیره اطلاعات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: SMS -->
<div class="modal fade" id="smsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">ارسال پیامک انبوه</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>/admin/report/send-sms" method="POST">
                <div class="modal-body">
                    <div class="alert alert-warning border-0 d-flex align-items-center mb-4">
                        <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                        <div>این پیام برای <strong><?= $presentCount ?> نفر</strong> (کل حاضرین) ارسال خواهد شد.</div>
                    </div>
                    <input type="hidden" name="seminar_id" value="<?= $seminarId ?>">
                    <label class="form-label small text-muted">متن پیامک</label>
                    <textarea name="message" class="form-control bg-light border-0" rows="5" required placeholder="پیام خود را اینجا بنویسید..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">لغو</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-4">ارسال</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
function showReceipt(imgUrl) {
    document.getElementById('receiptImageSrc').src = imgUrl;
    document.getElementById('downloadLink').href = imgUrl;
    var myModal = new bootstrap.Modal(document.getElementById('receiptModal'));
    myModal.show();
}

document.addEventListener("DOMContentLoaded", function() {
    Chart.defaults.font.family = "'Vazirmatn', sans-serif";
    Chart.defaults.color = '#64748b';

    const ctxBar = document.getElementById('expertsChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: <?= json_encode($expertNames) ?>,
            datasets: [
                { label: 'حاضرین', data: <?= json_encode($expertPresents) ?>, backgroundColor: '#4f46e5', borderRadius: 6, barPercentage: 0.6 },
                { label: 'کل دعوت‌ها', data: <?= json_encode($expertInvites) ?>, backgroundColor: '#e2e8f0', borderRadius: 6, barPercentage: 0.6, grouped: false, order: 1 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { x: { grid: { display: false } }, y: { beginAtZero: true, grid: { borderDash: [5, 5], color: '#f1f5f9' } } }, plugins: { legend: { display: false } } }
    });

    const ctxDoughnut = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctxDoughnut, {
        type: 'doughnut',
        data: {
            labels: ['دعوتی حاضر', 'ثبت دستی', 'غایب'],
            datasets: [{ data: [<?= $invitedPresentCount ?>, <?= $walkInCount ?>, <?= $absentCount ?>], backgroundColor: ['#10b981', '#f59e0b', '#ef4444'], borderWidth: 0, hoverOffset: 6 }]
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { display: false } } },
        plugins: [{
            id: 'centerText',
            beforeDraw: function(chart) {
                const { width, height, ctx } = chart;
                ctx.restore();
                const fontSize = (height / 120).toFixed(2);
                ctx.font = `bold ${fontSize}em Vazirmatn`; ctx.textBaseline = 'middle'; ctx.textAlign = 'center'; ctx.fillStyle = '#334155';
                ctx.fillText('<?= $presentPercent ?>%', width / 2, height / 2 - 10);
                ctx.font = `normal ${fontSize * 0.45}em Vazirmatn`; ctx.fillStyle = '#94a3b8';
                ctx.fillText('نرخ حضور', width / 2, height / 2 + 20);
                ctx.save();
            }
        }]
    });

    document.getElementById('tableSearch').addEventListener('keyup', function() {
        const val = this.value.toLowerCase().trim();
        const activeTab = document.querySelector('.tab-pane.show.active');
        if(activeTab){
            activeTab.querySelectorAll('tbody tr').forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(val) ? '' : 'none';
            });
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>