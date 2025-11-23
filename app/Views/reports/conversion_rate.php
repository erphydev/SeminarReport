<?php require_once __DIR__ . '/../layouts/header.php';
      use App\Services\JalaliDate;
?>

<div class="container-fluid mt-3 px-0">
    <?php 
    if (isset($_GET['status'])) {
        $status = $_GET['status'];
        $alertType = 'info';
        $message = '';
        $icon = '';

        switch ($status) {
            case 'sent':
                $alertType = 'success';
                $message = '✅ پیامک‌ها با موفقیت در صف ارسال قرار گرفتند.';
                break;
            
            case 'empty_list':
                $alertType = 'warning';
                $message = '⚠️ هیچ فرد "حاضری" در لیست وجود ندارد. پیامکی ارسال نشد.';
                break;

            case 'api_error':
                $alertType = 'danger';
                $message = '❌ خطا در اتصال به پنل پیامک! لطفاً شارژ پنل یا نام کاربری/رمز عبور را بررسی کنید.';
                break;
                
            default:
                $alertType = 'info';
                $message = 'عملیات انجام شد.';
        }
    ?>
        <div class="alert alert-<?= $alertType ?> alert-dismissible fade show shadow-sm" role="alert">
            <span class="fw-bold"><?= $message ?></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark">📊 داشبورد گزارشات</h3>
        <span class="text-muted">شناسه سمینار: <?= $_GET['id'] ?></span>
    </div>
    
    <div class="btn-group">
        <!-- دکمه مودال پیامک -->
        <button type="button" class="btn btn-warning fw-bold" data-bs-toggle="modal" data-bs-target="#smsModal">
            📨 ارسال پیامک
        </button>
        
        <a href="<?= BASE_URL ?>/admin/report/export-total?id=<?= $_GET['id'] ?>" class="btn btn-dark">📥 کل</a>
        <a href="<?= BASE_URL ?>/admin/report/export-present?id=<?= $_GET['id'] ?>" class="btn btn-success">📥 حاضرین</a>
        <a href="<?= BASE_URL ?>/admin/report/export-absent?id=<?= $_GET['id'] ?>" class="btn btn-danger">📥 غایبین</a>
    </div>
    <a href="<?= BASE_URL ?>/admin" class="btn btn-secondary ms-2">بازگشت</a>
</div>

<!-- آمار کلی بالای صفحه -->
<div class="row mb-4 text-center">
    <div class="col-md-4">
        <div class="card bg-light border-0 shadow-sm py-2">
            <h5 class="text-muted">کل دعوت‌شدگان</h5>
            <h2 class="fw-bold"><?= count($allGuests) ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white border-0 shadow-sm py-2">
            <h5>تعداد حاضرین ✅</h5>
            <h2 class="fw-bold"><?= count($presents) ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white border-0 shadow-sm py-2">
            <h5>تعداد غایبین ❌</h5>
            <h2 class="fw-bold"><?= count($absents) ?></h2>
        </div>
    </div>
</div>

<!-- جدول نرخ تبدیل -->
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-primary text-white fw-bold">🏆 عملکرد کارشناسان</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>رتبه</th>
                        <th>نام کارشناس</th>
                        <th class="text-center">دعوت</th>
                        <th class="text-center">حاضر</th>
                        <th>نرخ تبدیل</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    foreach ($stats as $row): 
                        $rate = round($row['conversion_rate'], 1);
                        $color = $rate > 50 ? 'success' : ($rate > 20 ? 'warning' : 'danger');
                    ?>
                    <tr>
                        <td><span class="badge bg-dark rounded-pill"><?= $rank++ ?></span></td>
                        <td class="fw-bold"><?= htmlspecialchars($row['expert_name']) ?></td>
                        <td class="text-center"><?= $row['total_invited'] ?></td>
                        <td class="text-center fw-bold text-primary"><?= $row['total_present'] ?></td>
                        <td style="width: 30%;">
                            <div class="d-flex align-items-center">
                                <span class="me-2 fw-bold text-<?= $color ?>"><?= $rate ?>%</span>
                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <div class="progress-bar bg-<?= $color ?>" style="width: <?= $rate ?>%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- تب‌ها -->
<ul class="nav nav-tabs mb-3" id="reportTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active text-success fw-bold" id="present-tab" data-bs-toggle="tab" data-bs-target="#present" type="button">
            ✅ حاضرین (<?= count($presents) ?>)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link text-danger fw-bold" id="absent-tab" data-bs-toggle="tab" data-bs-target="#absent" type="button">
            ❌ غایبین (<?= count($absents) ?>)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link text-dark fw-bold" id="total-tab" data-bs-toggle="tab" data-bs-target="#total" type="button">
            📋 کل لیست (<?= count($allGuests) ?>)
        </button>
    </li>
</ul>

<div class="tab-content" id="reportTabsContent">
    
    <!-- تب ۱: حاضرین -->
    <div class="tab-pane fade show active" id="present" role="tabpanel">
        <div class="card shadow-sm border-success">
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="table-success sticky-top">
                            <tr>
                                <th>نام مهمان</th>
                                <th>شماره تماس</th>
                                <th>کارشناس</th>
                                <th>زمان ورود</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($presents as $guest): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($guest['full_name']) ?></td>
                                <td><?= $guest['phone'] ?></td>
                                <td><?= htmlspecialchars($guest['expert_name']) ?></td>
                                <td dir="ltr" class="text-end">
                                    <?= JalaliDate::format($guest['checkin_time'], 'Y/m/d - H:i') ?>
                                </td>                            
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- تب ۲: غایبین -->
    <div class="tab-pane fade" id="absent" role="tabpanel">
        <div class="card shadow-sm border-danger">
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="table-danger sticky-top">
                            <tr>
                                <th>نام مهمان</th>
                                <th>شماره تماس</th>
                                <th>کارشناس</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($absents as $guest): ?>
                            <tr>
                                <td><?= htmlspecialchars($guest['full_name']) ?></td>
                                <td><a href="tel:<?= $guest['phone'] ?>" class="text-decoration-none"><?= $guest['phone'] ?></a></td>
                                <td><?= htmlspecialchars($guest['expert_name']) ?></td>
                                <td>
                                    <a href="tel:<?= $guest['phone'] ?>" class="btn btn-sm btn-outline-danger">📞 تماس</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- تب ۳: کل لیست -->
    <div class="tab-pane fade" id="total" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-striped mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>نام</th>
                                <th>شماره</th>
                                <th>کارشناس</th>
                                <th>وضعیت</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allGuests as $guest): ?>
                            <tr>
                                <td><?= htmlspecialchars($guest['full_name']) ?></td>
                                <td><?= $guest['phone'] ?></td>
                                <td><?= htmlspecialchars($guest['expert_name']) ?></td>
                                <td><?= $guest['is_present'] ? '✅' : '❌' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


</div>

<div class="modal fade" id="smsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">ارسال پیامک به حاضرین (<?= count($presents) ?> نفر)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>/admin/report/send-sms" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="seminar_id" value="<?= $_GET['id'] ?>">
                    <textarea name="message" class="form-control" rows="5" placeholder="متن پیام..." required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">ارسال</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>