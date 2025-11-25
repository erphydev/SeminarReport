<?php
require_once __DIR__ . '/../layouts/header.php';
use App\Services\JalaliDate;

// --- 1. فیلتر کردن و تفکیک لیست‌ها ---
$walkIns = array_filter($allGuests, fn ($guest) => empty($guest['expert_id']));
$invitedPresents = array_filter($presents, fn ($guest) => !empty($guest['expert_id']));
$invitedAbsents = array_filter($absents, fn ($guest) => !empty($guest['expert_id']));

// --- 2. محاسبات آماری ---
$totalInvited = count(array_filter($allGuests, fn ($guest) => !empty($guest['expert_id'])));
$totalCount = count($allGuests);
$invitedPresentCount = count($invitedPresents);
$walkInCount = count($walkIns);
$absentCount = count($invitedAbsents);
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

// تابع کمکی برای ایجاد آواتار از حروف اول نام
function getInitials($name)
{
    $parts = explode(' ', trim($name));
    if (count($parts) >= 2) {
        return mb_substr($parts[0], 0, 1) . ' ' . mb_substr($parts[1], 0, 1);
    }
    return mb_substr($name, 0, 2);
}

// رنگ‌بندی داینامیک برای آواتارها
function getAvatarColor($name)
{
    $colors = ['#6366f1', '#8b5cf6', '#ec4899', '#f43f5e', '#f59e0b', '#10b981', '#06b6d4', '#3b82f6'];
    return $colors[abs(crc32($name)) % count($colors)];
}
?>

<!-- فونت و کتابخانه‌ها -->
<link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- کتابخانه Shepherd.js برای تور آموزشی -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js@10.0.1/dist/css/shepherd.css"/>

<style>
    :root {
        --font-family: 'Vazirmatn', system-ui, -apple-system, sans-serif;
        --bg-body: #f1f5f9;
        --text-main: #334155;
        --text-muted: #64748b;
        --card-bg: #ffffff;
        --card-radius: 16px;
        --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --primary-color: #4f46e5;
        --secondary-bg: #f8fafc;
    }

    body {
        font-family: var(--font-family) !important;
        background-color: var(--bg-body);
        color: var(--text-main);
        overflow-x: hidden;
    }

    /* --- کامپوننت‌های عمومی --- */
    .card {
        background: var(--card-bg);
        border: none;
        border-radius: var(--card-radius);
        box-shadow: var(--card-shadow);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    
    .btn-soft { background-color: white; border: 1px solid #e2e8f0; color: #475569; transition: all 0.2s; }
    .btn-soft:hover { background-color: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
    
    .btn-primary-gradient {
        background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
        color: white; border: none;
    }
    .btn-primary-gradient:hover { background: linear-gradient(135deg, #4338ca 0%, #3730a3 100%); color: white; transform: translateY(-1px); }

    /* --- کارت‌های آمار (Stat Cards) --- */
    .stat-card { position: relative; overflow: hidden; }
    .stat-card .icon-box {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }
    .stat-card.blue .icon-box { background: #e0e7ff; color: #4338ca; }
    .stat-card.green .icon-box { background: #dcfce7; color: #15803d; }
    .stat-card.yellow .icon-box { background: #fef9c3; color: #a16207; }
    .stat-card.red .icon-box { background: #fee2e2; color: #991b1b; }

    /* --- رتبه‌بندی کارشناسان --- */
    .expert-card {
        text-align: center; padding: 1.5rem; border: 1px solid #f1f5f9;
        background: linear-gradient(to bottom, #fff 0%, #f8fafc 100%);
    }
    .expert-rank-badge {
        width: 30px; height: 30px; border-radius: 50%;
        position: absolute; top: 10px; right: 10px;
        display: flex; align-items: center; justify-content: center;
        font-weight: bold; color: white; font-size: 0.8rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .rank-1 .expert-rank-badge { background: linear-gradient(45deg, #FFD700, #FDB931); }
    .rank-2 .expert-rank-badge { background: linear-gradient(45deg, #E0E0E0, #BDBDBD); }
    .rank-3 .expert-rank-badge { background: linear-gradient(45deg, #CD7F32, #A0522D); }
    .rank-other .expert-rank-badge { background: #cbd5e1; color: #475569; }

    /* --- جدول و تب‌ها --- */
    .nav-pills-custom {
        background: #e2e8f0; padding: 4px; border-radius: 12px; display: inline-flex;
    }
    .nav-pills-custom .nav-link {
        border-radius: 10px; color: #64748b; font-weight: 500; padding: 8px 16px;
    }
    .nav-pills-custom .nav-link.active {
        background: white; color: #0f172a; shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .table-modern thead th {
        background: transparent; border-bottom: 2px solid #f1f5f9;
        font-size: 0.8rem; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.5px;
        padding: 1rem 1.5rem;
    }
    .table-modern tbody td {
        padding: 1rem 1.5rem; vertical-align: middle; border-bottom: 1px solid #f8fafc;
        color: #334155; font-size: 0.95rem;
    }
    .table-modern tbody tr:last-child td { border-bottom: none; }
    .table-modern tbody tr:hover { background-color: #f8fafc; }

    .avatar {
        width: 38px; height: 38px; border-radius: 50%; color: white;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.85rem; font-weight: 600; margin-left: 10px;
    }

    /* --- مدال --- */
    .modal-content { border-radius: 20px; border: none; }
    .modal-header { border-bottom: 1px solid #f1f5f9; padding: 1.5rem; }
    .modal-footer { border-top: 1px solid #f1f5f9; padding: 1.5rem; }
    
    /* --- استایل سفارشی برای Shepherd.js --- */
    .shepherd-element {
        font-family: var(--font-family);
        box-shadow: var(--card-shadow);
        border-radius: var(--card-radius);
        max-width: 400px;
    }
    .shepherd-header {
        background-color: #f8fafc;
        padding: 1rem 1.5rem;
    }
    .shepherd-title {
        color: var(--text-main);
        font-weight: 700;
    }
    .shepherd-text {
        padding: 0 1.5rem 1rem;
        color: var(--text-muted);
        font-size: 0.95rem;
    }
    .shepherd-button {
        padding: 0.5rem 1.25rem;
        border-radius: 20px;
        font-weight: 500;
        transition: all 0.2s;
    }
    .shepherd-button-secondary {
        background: #e2e8f0;
        color: #475569;
    }
    .shepherd-button-primary {
        background-color: var(--primary-color);
    }

    @media print {
        .no-print { display: none !important; }
        .card { box-shadow: none; border: 1px solid #ccc; break-inside: avoid; }
        body { background: white; }
    }
</style>

<div class="container-fluid py-5 px-lg-5">

    <!-- بخش هدر -->
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-5 animate__animated animate__fadeIn" id="tour-step-1">
        <div>
            <div class="d-flex align-items-center mb-2">
                <span class="badge bg-white text-primary border me-2">ID: <?= $_GET['id'] ?></span>
                <span class="text-muted small"><i class="bi bi-calendar me-1"></i><?= JalaliDate::format(date('Y-m-d'), 'd F Y') ?></span>
            </div>
            <h2 class="fw-bolder text-dark mb-0 ls-tight">داشبورد گزارش رویداد</h2>
        </div>
        
        <div class="d-flex gap-2 mt-3 mt-lg-0 no-print flex-wrap" id="tour-step-8">
            <button onclick="window.print()" class="btn btn-soft shadow-sm"><i class="bi bi-printer me-2"></i>چاپ</button>
            <div class="dropdown">
                <button class="btn btn-soft shadow-sm dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-2"></i>اکسل
                </button>
                <ul class="dropdown-menu border-0 shadow-lg p-2 rounded-3">
                    <li><a class="dropdown-item rounded" href="<?= BASE_URL ?>/admin/report/export-total?id=<?= $_GET['id'] ?>">کل لیست</a></li>
                    <li><a class="dropdown-item rounded" href="<?= BASE_URL ?>/admin/report/export-present?id=<?= $_GET['id'] ?>">حاضرین</a></li>
                    <li><a class="dropdown-item rounded" href="<?= BASE_URL ?>/admin/report/export-absent?id=<?= $_GET['id'] ?>">غایبین</a></li>
                </ul>
            </div>
            <button class="btn btn-primary-gradient shadow-sm" data-bs-toggle="modal" data-bs-target="#addGuestModalReport">
                <i class="bi bi-person-plus-fill me-2"></i>ثبت مهمان
            </button>
            <button class="btn btn-warning text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#smsModal">
                <i class="bi bi-chat-text-fill me-2"></i>پیامک
            </button>
            <!-- دکمه شروع تور -->
            <button onclick="startTour()" class="btn btn-outline-primary shadow-sm"><i class="bi bi-compass me-2"></i>راهنما</button>
        </div>
    </div>

    <!-- کارت‌های آمار -->
    <div class="row g-4 mb-5" id="tour-step-2">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card blue h-100 p-4">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted fw-bold small text-uppercase mb-1">کل دعوت‌ها</p>
                        <h2 class="fw-bolder text-dark mb-0"><?= number_format($totalInvited) ?></h2>
                    </div>
                    <div class="icon-box"><i class="bi bi-people-fill"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card green h-100 p-4">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted fw-bold small text-uppercase mb-1">حاضرین (دعوتی)</p>
                        <h2 class="fw-bolder text-success mb-0"><?= number_format($invitedPresentCount) ?></h2>
                        <span class="badge bg-light text-success mt-2">نرخ <?= $presentPercent ?>%</span>
                    </div>
                    <div class="icon-box"><i class="bi bi-person-check-fill"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card yellow h-100 p-4">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted fw-bold small text-uppercase mb-1">ثبت دستی (Walk-in)</p>
                        <h2 class="fw-bolder text-warning mb-0"><?= number_format($walkInCount) ?></h2>
                    </div>
                    <div class="icon-box"><i class="bi bi-person-plus-fill"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card red h-100 p-4">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted fw-bold small text-uppercase mb-1">غایبین</p>
                        <h2 class="fw-bolder text-danger mb-0"><?= number_format($absentCount) ?></h2>
                    </div>
                    <div class="icon-box"><i class="bi bi-person-x-fill"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- نمودارها (با اصلاح باگ اسکرول) -->
    <div class="row g-4 mb-5">
        <div class="col-lg-8" id="tour-step-3">
            <div class="card h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-dark">📊 عملکرد کارشناسان</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <div style="position: relative; height: 320px; width: 100%;">
                        <canvas id="expertsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4" id="tour-step-4">
            <div class="card h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-dark">📈 وضعیت کلی حضور</h5>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <div style="position: relative; height: 250px; width: 100%;">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                    <div class="mt-3 text-center small text-muted">
                        میانگین حضور مهمانان در رویداد
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- رتبه‌بندی کارشناسان -->
    <div class="mb-5" id="tour-step-5">
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
                        <div class="mb-2">
                            <span class="h4 fw-bolder text-dark"><?= $rate ?></span><small class="text-muted">%</small>
                        </div>
                        <h6 class="text-truncate fw-bold mb-1" title="<?= $row['expert_name'] ?>"><?= htmlspecialchars($row['expert_name']) ?></h6>
                        <small class="text-muted d-block mb-3">نرخ تبدیل</small>
                        <div class="d-flex justify-content-center gap-3 border-top pt-2">
                            <div class="text-center"><span class="d-block fw-bold text-success"><?= $row['total_present'] ?></span><small style="font-size:10px">حاضر</small></div>
                            <div class="text-center"><span class="d-block fw-bold text-secondary"><?= $row['total_invited'] ?></span><small style="font-size:10px">کل</small></div>
                        </div>
                    </div>
                </div>
            <?php $rank++;
            endforeach; ?>
        </div>
    </div>

    <!-- لیست مهمانان -->
    <div class="card">
        <div class="card-header bg-white border-0 py-4 px-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <ul class="nav nav-pills-custom" id="tour-step-6" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#present">حاضرین دعوتی</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#walkin">ثبت دستی</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#absent">غایبین</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#total">کل لیست</button></li>
            </ul>
            <div class="position-relative w-100 w-md-auto" id="tour-step-7">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" id="tableSearch" class="form-control bg-light border-0 ps-5" style="border-radius:10px" placeholder="جستجو...">
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="tab-content">
                <!-- Tab: Present -->
                <div class="tab-pane fade show active" id="present">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0 w-100">
                            <thead><tr><th class="ps-4">مهمان</th><th>تلفن تماس</th><th>کارشناس</th><th class="text-end pe-4">ورود</th></tr></thead>
                            <tbody>
                                <?php foreach ($invitedPresents as $guest): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar" style="background:<?= getAvatarColor($guest['full_name']) ?>"><?= getInitials($guest['full_name']) ?></div>
                                            <div class="ms-2"><div class="fw-bold"><?= htmlspecialchars($guest['full_name']) ?></div></div>
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
                                            <div class="ms-2"><div class="fw-bold"><?= htmlspecialchars($guest['full_name']) ?></div></div>
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
                                            <div class="ms-2"><div class="fw-bold text-secondary"><?= htmlspecialchars($guest['full_name']) ?></div></div>
                                        </div>
                                    </td>
                                    <td class="text-muted font-monospace"><?= $guest['phone'] ?></td>
                                    <td><?= htmlspecialchars($guest['expert_name']) ?></td>
                                    <td class="text-end pe-4">
                                        <a href="tel:<?= $guest['phone'] ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 no-print">تماس</a>
                                    </td>
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
                    <input type="hidden" name="seminar_id" value="<?= $_GET['id'] ?>">
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
                    <button type="submit" class="btn btn-primary-gradient rounded-pill px-4 shadow-sm">ذخیره اطلاعات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Send SMS -->
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
                    <input type="hidden" name="seminar_id" value="<?= $_GET['id'] ?>">
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

<!-- اسکریپت‌های چارت و جستجو -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    // تنظیمات سراسری چارت
    Chart.defaults.font.family = "'Vazirmatn', sans-serif";
    Chart.defaults.color = '#64748b';

    // 1. Bar Chart (عملکرد کارشناسان)
    const ctxBar = document.getElementById('expertsChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: <?= json_encode($expertNames) ?>,
            datasets: [
                { 
                    label: 'حاضرین', 
                    data: <?= json_encode($expertPresents) ?>, 
                    backgroundColor: '#4f46e5', 
                    borderRadius: 6, 
                    barPercentage: 0.6 
                },
                { 
                    label: 'کل دعوت‌ها', 
                    data: <?= json_encode($expertInvites) ?>, 
                    backgroundColor: '#e2e8f0', 
                    borderRadius: 6, 
                    barPercentage: 0.6,
                    grouped: false,
                    order: 1
                }
            ]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            scales: { 
                x: { grid: { display: false } }, 
                y: { beginAtZero: true, grid: { borderDash: [5, 5], color: '#f1f5f9' } } 
            },
            plugins: { legend: { display: false } }
        }
    });

    // 2. Doughnut Chart (حضور و غیاب)
    const ctxDoughnut = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctxDoughnut, {
        type: 'doughnut',
        data: {
            labels: ['دعوتی حاضر', 'ثبت دستی', 'غایب'],
            datasets: [{
                data: [<?= $invitedPresentCount ?>, <?= $walkInCount ?>, <?= $absentCount ?>],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'], 
                borderWidth: 0,
                hoverOffset: 6
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            cutout: '75%', 
            plugins: { legend: { display: false } } 
        },
        plugins: [{
            id: 'centerText',
            beforeDraw: function(chart) {
                const { width, height, ctx } = chart;
                ctx.restore();
                const fontSize = (height / 120).toFixed(2);
                ctx.font = `bold ${fontSize}em Vazirmatn`;
                ctx.textBaseline = 'middle';
                ctx.textAlign = 'center';
                ctx.fillStyle = '#334155';
                ctx.fillText('<?= $presentPercent ?>%', width / 2, height / 2 - 10);
                
                ctx.font = `normal ${fontSize * 0.45}em Vazirmatn`;
                ctx.fillStyle = '#94a3b8';
                ctx.fillText('نرخ حضور', width / 2, height / 2 + 20);
                ctx.save();
            }
        }]
    });

    // 3. جستجوی زنده در جدول
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

<!-- اسکریپت تور آموزشی Shepherd.js -->
<script src="https://cdn.jsdelivr.net/npm/shepherd.js@10.0.1/dist/js/shepherd.min.js"></script>
<script>
    let tour;

    function startTour() {
        tour = new Shepherd.Tour({
            useModalOverlay: true,
            defaultStepOptions: {
                classes: 'shadow-md bg-light-100',
                scrollTo: { behavior: 'smooth', block: 'center' }
            }
        });

        // تعریف مراحل تور
        tour.addStep({
            id: 'step-1',
            title: 'خوش آمدید!',
            text: 'اینجا داشبورد گزارش رویداد شماست. در چند مرحله با بخش‌های مختلف آن آشنا خواهید شد.',
            attachTo: { element: '#tour-step-1', on: 'bottom' },
            buttons: [{ text: 'بعدی', action: tour.next }]
        });
        
        tour.addStep({
            id: 'step-2',
            title: 'آمار کلی',
            text: 'در این بخش می‌توانید خلاصه‌ای از آمار رویداد مانند تعداد کل دعوت‌ها، حاضرین، غایبین و ثبت‌نام‌های دستی را ببینید.',
            attachTo: { element: '#tour-step-2', on: 'bottom' },
            buttons: [{ text: 'قبلی', action: tour.back }, { text: 'بعدی', action: tour.next }]
        });

        tour.addStep({
            id: 'step-3',
            title: 'عملکرد کارشناسان',
            text: 'این نمودار، تعداد مهمانان دعوت‌شده (خاکستری) و حاضرشده (بنفش) را به تفکیک هر کارشناس نمایش می‌دهد.',
            attachTo: { element: '#tour-step-3', on: 'bottom' },
            buttons: [{ text: 'قبلی', action: tour.back }, { text: 'بعدی', action: tour.next }]
        });
        
        tour.addStep({
            id: 'step-4',
            title: 'وضعیت کلی حضور',
            text: 'این نمودار دایره‌ای، ترکیب حاضرین (دعوتی و دستی) و غایبین را به همراه نرخ کلی حضور نمایش می‌دهد.',
            attachTo: { element: '#tour-step-4', on: 'left' },
            buttons: [{ text: 'قبلی', action: tour.back }, { text: 'بعدی', action: tour.next }]
        });

        tour.addStep({
            id: 'step-5',
            title: 'برترین کارشناسان',
            text: 'در این قسمت، کارشناسان بر اساس بالاترین "نرخ تبدیل" (درصد مهمانان حاضر به کل دعوت‌ها) رتبه‌بندی شده‌اند.',
            attachTo: { element: '#tour-step-5', on: 'top' },
            buttons: [{ text: 'قبلی', action: tour.back }, { text: 'بعدی', action: tour.next }]
        });

        tour.addStep({
            id: 'step-6',
            title: 'لیست مهمانان',
            text: 'با استفاده از این تب‌ها می‌توانید لیست مهمانان را بر اساس وضعیت (حاضرین، غایبین و...) فیلتر کنید.',
            attachTo: { element: '#tour-step-6', on: 'bottom' },
            buttons: [{ text: 'قبلی', action: tour.back }, { text: 'بعدی', action: tour.next }]
        });

        tour.addStep({
            id: 'step-7',
            title: 'جستجو در لیست',
            text: 'برای پیدا کردن سریع یک مهمان، نام یا شماره تلفن او را در این کادر وارد کنید.',
            attachTo: { element: '#tour-step-7', on: 'bottom' },
            buttons: [{ text: 'قبلی', action: tour.back }, { text: 'بعدی', action: tour.next }]
        });

        tour.addStep({
            id: 'step-8',
            title: 'دکمه‌های عملیاتی',
            text: 'از اینجا می‌توانید گزارش را چاپ کنید، خروجی اکسل بگیرید، مهمان جدید ثبت کنید یا پیامک انبوه ارسال نمایید.',
            attachTo: { element: '#tour-step-8', on: 'bottom' },
            buttons: [{ text: 'قبلی', action: tour.back }, { text: 'پایان', action: tour.complete }]
        });
        
        tour.start();
    }
</script>


<?php require_once __DIR__ . '/../layouts/footer.php'; ?>