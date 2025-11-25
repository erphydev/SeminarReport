<?php 
require_once __DIR__ . '/../layouts/header.php';
use App\Services\JalaliDate;

// --- محاسبات آماری ---
$totalCount = count($allGuests);
$presentCount = count($presents);
$absentCount = count($absents);
$presentPercent = $totalCount > 0 ? round(($presentCount / $totalCount) * 100) : 0;

// داده‌های چارت
$expertNames = [];
$expertInvites = [];
$expertPresents = [];
foreach ($stats as $s) {
    $expertNames[] = $s['expert_name'];
    $expertInvites[] = $s['total_invited'];
    $expertPresents[] = $s['total_present'];
}
?>

<!-- فونت و استایل‌ها -->
<link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    :root {
        --bs-primary-rgb: 13, 110, 253;
        --card-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    body { font-family: 'Vazirmatn', sans-serif !important; background-color: #f3f6f9; color: #3f4254; }
    
    /* کارت‌ها */
    .card { border: none; border-radius: 1rem; box-shadow: var(--card-shadow); margin-bottom: 1.5rem; transition: all 0.3s ease; }
    .card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.08); }
    
    /* استایل KPI ها */
    .stat-card { position: relative; overflow: hidden; }
    .stat-card .bg-icon { position: absolute; left: -20px; bottom: -20px; font-size: 8rem; opacity: 0.05; transform: rotate(15deg); }
    
    /* جداول */
    .table thead th { background-color: #f9fafb; color: #6c757d; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #eaecf0; padding: 1rem; }
    .table tbody td { padding: 1rem; vertical-align: middle; }
    
    /* رتبه‌بندی */
    .rank-badge { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: 800; font-size: 0.9rem; }
    .rank-1 { background: #fff4e5; color: #ff9800; border: 2px solid #ffe0b2; } /* طلا */
    .rank-2 { background: #f8f9fa; color: #adb5bd; border: 2px solid #dee2e6; } /* نقره */
    .rank-3 { background: #fff0f0; color: #d68c76; border: 2px solid #eddcd9; } /* برنز */
    .rank-other { background: #f1f1f1; color: #777; }
    
    /* تب‌ها */
    .nav-pills .nav-link { border-radius: 0.75rem; font-weight: 600; padding: 0.75rem 1.5rem; color: #5e6278; transition: all 0.2s; }
    .nav-pills .nav-link.active { background-color: #009ef7; color: #fff; box-shadow: 0 4px 15px rgba(0, 158, 247, 0.3); }
    
    /* اسکرول بار زیبا */
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: #f1f1f1; }
    ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
    ::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }

    /* استایل پرینت */
    @media print {
        .no-print, .btn, .nav-pills, .alert { display: none !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; break-inside: avoid; }
        body { background-color: #fff; }
        .tab-content .tab-pane { display: block !important; opacity: 1 !important; margin-bottom: 20px; }
        .collapse { display: block !important; }
    }
</style>

<div class="container-fluid py-4">

    <!-- بخش پیام‌ها -->
    <?php if (isset($_GET['status'])): 
        $status = $_GET['status'];
        $msgData = match($status) {
            'sent' => ['success', 'پیامک‌ها با موفقیت در صف ارسال قرار گرفتند.'],
            'empty_list' => ['warning', 'لیست حاضرین خالی است.'],
            'api_error' => ['danger', 'خطا در ارتباط با پنل پیامک.'],
            default => ['info', 'عملیات انجام شد.']
        };
    ?>
        <div class="alert alert-<?= $msgData[0] ?> alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i> <?= $msgData[1] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- هدر داشبورد -->
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-5 gap-3">
        <div>
            <h2 class="fw-bolder text-dark mb-1">داشبورد مدیریت رویداد</h2>
            <div class="text-muted">
                گزارش لحظه‌ای وضعیت سمینار | شناسه: <span class="badge bg-light text-dark border">#<?= $_GET['id'] ?></span>
            </div>
        </div>
        
        <div class="d-flex gap-2 flex-wrap no-print">
            <button onclick="window.print()" class="btn btn-light border shadow-sm">
                <i class="bi bi-printer"></i> چاپ گزارش
            </button>
            
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-file-earmark-excel"></i> خروجی اکسل
                </button>
                <ul class="dropdown-menu text-end shadow">
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/report/export-total?id=<?= $_GET['id'] ?>">کل لیست</a></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/report/export-present?id=<?= $_GET['id'] ?>">فقط حاضرین</a></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/report/export-absent?id=<?= $_GET['id'] ?>">فقط غایبین</a></li>
                </ul>
            </div>
            
            <button type="button" class="btn btn-warning fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#smsModal">
                <i class="bi bi-chat-text-fill"></i> ارسال پیامک
            </button>
            
            <a href="<?= BASE_URL ?>/admin" class="btn btn-secondary">بازگشت</a>
        </div>
    </div>

    <!-- کارت‌های آمار (KPI Cards) -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card stat-card h-100 bg-white">
                <div class="card-body p-4">
                    <i class="bi bi-people-fill bg-icon text-primary"></i>
                    <h6 class="text-muted fw-bold mb-3">کل دعوت‌شدگان</h6>
                    <div class="d-flex align-items-baseline">
                        <h2 class="fw-bolder mb-0 display-6 text-dark"><?= $totalCount ?></h2>
                        <span class="text-muted ms-2 fs-6">نفر</span>
                    </div>
                    <div class="mt-3">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card stat-card h-100 bg-white">
                <div class="card-body p-4">
                    <i class="bi bi-check-circle-fill bg-icon text-success"></i>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-success fw-bold mb-0">حاضرین در سالن</h6>
                        <span class="badge bg-light-success text-success px-3 py-2 rounded-pill"><?= $presentPercent ?>% تحقق</span>
                    </div>
                    <div class="d-flex align-items-baseline">
                        <h2 class="fw-bolder mb-0 display-6 text-success"><?= $presentCount ?></h2>
                        <span class="text-muted ms-2 fs-6">نفر</span>
                    </div>
                    <div class="mt-3">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $presentPercent ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card stat-card h-100 bg-white">
                <div class="card-body p-4">
                    <i class="bi bi-x-circle-fill bg-icon text-danger"></i>
                    <h6 class="text-danger fw-bold mb-3">غایبین</h6>
                    <div class="d-flex align-items-baseline">
                        <h2 class="fw-bolder mb-0 display-6 text-danger"><?= $absentCount ?></h2>
                        <span class="text-muted ms-2 fs-6">نفر</span>
                    </div>
                    <div class="mt-3">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: <?= 100 - $presentPercent ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- نمودارها و رتبه‌بندی -->
    <div class="row g-4 mb-5">
        <!-- جدول رتبه‌بندی -->
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                    <h5 class="fw-bold mb-0">🏆 رتبه‌بندی عملکرد کارشناسان</h5>
                    <p class="text-muted small mb-0">بر اساس نرخ تبدیل (دعوت به حضور)</p>
                </div>
                <div class="card-body px-0 pt-2">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="sticky-top">
                                <tr>
                                    <th class="ps-4">رتبه</th>
                                    <th>نام کارشناس</th>
                                    <th class="text-center">نرخ تبدیل</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                foreach ($stats as $row): 
                                    $rate = round($row['conversion_rate'], 1);
                                    $rankClass = match($rank) { 1 => 'rank-1', 2 => 'rank-2', 3 => 'rank-3', default => 'rank-other' };
                                    $medal = match($rank) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => '' };
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="rank-badge <?= $rankClass ?>"><?= $rank ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['expert_name']) ?> <?= $medal ?></div>
                                        <div class="small text-muted">دعوت: <?= $row['total_invited'] ?> | حضور: <?= $row['total_present'] ?></div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border fs-7"><?= $rate ?>%</span>
                                    </td>
                                </tr>
                                <?php $rank++; endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- نمودارها -->
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold">📊 نمای گرافیکی آمار</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-pills mb-3 bg-light p-1 rounded no-print" id="chartTabs" role="tablist">
                        <li class="nav-item flex-fill text-center" role="presentation">
                            <button class="nav-link w-100 active" data-bs-toggle="pill" data-bs-target="#chart-bar">مقایسه کارشناسان</button>
                        </li>
                        <li class="nav-item flex-fill text-center" role="presentation">
                            <button class="nav-link w-100" data-bs-toggle="pill" data-bs-target="#chart-pie">وضعیت حضور</button>
                        </li>
                    </ul>
                    <div class="tab-content h-100">
                        <div class="tab-pane fade show active h-100" id="chart-bar">
                            <canvas id="expertsChart" style="max-height: 300px;"></canvas>
                        </div>
                        <div class="tab-pane fade h-100 d-flex justify-content-center" id="chart-pie">
                            <div style="width: 300px; height: 300px;">
                                <canvas id="attendanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- لیست جزئیات با تب‌ها و جستجو -->
    <div class="card">
        <div class="card-header bg-white border-bottom-0 pt-4 px-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <ul class="nav nav-pills" id="listTabs" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#present">✅ حاضرین</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#absent">❌ غایبین</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#total">📋 کل لیست</button></li>
            </ul>
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" id="tableSearch" class="form-control bg-light border-start-0" placeholder="جستجوی نام یا شماره...">
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="tab-content">
                <!-- تب حاضرین -->
                <div class="tab-pane fade show active" id="present">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 searchable-table">
                            <thead class="sticky-top bg-white">
                                <tr>
                                    <th class="ps-4">نام مهمان</th>
                                    <th>شماره تماس</th>
                                    <th>کارشناس</th>
                                    <th class="text-end pe-4">زمان ورود</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($presents as $guest): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($guest['full_name']) ?></td>
                                    <td><?= $guest['phone'] ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($guest['expert_name']) ?></span></td>
                                    <td class="text-end pe-4 text-muted" dir="ltr">
                                        <?= JalaliDate::format($guest['checkin_time'], 'H:i') ?> <small class="text-muted ms-1"><?= JalaliDate::format($guest['checkin_time'], 'Y/m/d') ?></small>
                                    </td>                            
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- تب غایبین -->
                <div class="tab-pane fade" id="absent">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 searchable-table">
                            <thead class="sticky-top bg-white">
                                <tr>
                                    <th class="ps-4">نام مهمان</th>
                                    <th>شماره تماس</th>
                                    <th>کارشناس</th>
                                    <th class="text-end pe-4">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($absents as $guest): ?>
                                <tr>
                                    <td class="ps-4"><?= htmlspecialchars($guest['full_name']) ?></td>
                                    <td><?= $guest['phone'] ?></td>
                                    <td><?= htmlspecialchars($guest['expert_name']) ?></td>
                                    <td class="text-end pe-4">
                                        <a href="tel:<?= $guest['phone'] ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 no-print">
                                            <i class="bi bi-telephone-fill"></i> تماس
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- تب کل -->
                <div class="tab-pane fade" id="total">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 searchable-table">
                            <thead class="sticky-top bg-white">
                                <tr>
                                    <th class="ps-4">نام</th>
                                    <th>شماره</th>
                                    <th>کارشناس</th>
                                    <th class="text-center">وضعیت</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allGuests as $guest): ?>
                                <tr>
                                    <td class="ps-4"><?= htmlspecialchars($guest['full_name']) ?></td>
                                    <td><?= $guest['phone'] ?></td>
                                    <td><?= htmlspecialchars($guest['expert_name']) ?></td>
                                    <td class="text-center">
                                        <?php if($guest['is_present']): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success">حاضر</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">غایب</span>
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

<!-- Modal SMS -->
<div class="modal fade" id="smsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-chat-quote-fill me-2"></i>ارسال پیامک انبوه</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>/admin/report/send-sms" method="POST">
                <div class="modal-body p-4">
                    <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-warning d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                        <div>
                            پیامک فقط برای <strong><?= $presentCount ?></strong> نفر حاضر در سالن ارسال می‌شود.
                        </div>
                    </div>
                    <input type="hidden" name="seminar_id" value="<?= $_GET['id'] ?>">
                    <label class="form-label fw-bold text-muted">متن پیام:</label>
                    <textarea name="message" class="form-control bg-light" rows="5" placeholder="مثال: از حضور ارزشمند شما سپاسگزاریم..." required></textarea>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light text-muted" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">تایید و ارسال</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // تنظیمات چارت‌ها
    Chart.defaults.font.family = "'Vazirmatn', sans-serif";
    Chart.defaults.color = '#6c757d';

    // 1. Bar Chart
    new Chart(document.getElementById('expertsChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($expertNames) ?>,
            datasets: [
                {
                    label: 'دعوت',
                    data: <?= json_encode($expertInvites) ?>,
                    backgroundColor: '#e9ecef',
                    borderRadius: 5,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                },
                {
                    label: 'حضور',
                    data: <?= json_encode($expertPresents) ?>,
                    backgroundColor: '#0d6efd',
                    borderRadius: 5,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top', align: 'end' } },
            scales: {
                y: { grid: { borderDash: [2, 4], drawBorder: false } },
                x: { grid: { display: false } }
            }
        }
    });

    // 2. Pie Chart
    new Chart(document.getElementById('attendanceChart'), {
        type: 'doughnut',
        data: {
            labels: ['حاضرین', 'غایبین'],
            datasets: [{
                data: [<?= $presentCount ?>, <?= $absentCount ?>],
                backgroundColor: ['#198754', '#dc3545'],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            cutout: '70%',
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // 3. اسکریپت جستجو در جدول
    document.getElementById('tableSearch').addEventListener('keyup', function() {
        const value = this.value.toLowerCase();
        // جستجو در تب فعال
        const activeTab = document.querySelector('.tab-pane.active');
        const rows = activeTab.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.indexOf(value) > -1 ? '' : 'none';
        });
    });
    
    // وقتی تب عوض شد، جستجو ریست نشود اما روی جدول جدید اعمال شود
    const triggerTabList = [].slice.call(document.querySelectorAll('#listTabs button'))
    triggerTabList.forEach(function (triggerEl) {
        triggerEl.addEventListener('shown.bs.tab', function (event) {
            document.getElementById('tableSearch').dispatchEvent(new Event('keyup'));
        })
    })
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>