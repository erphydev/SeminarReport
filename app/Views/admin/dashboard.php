<?php
require_once __DIR__ . '/../layouts/header.php';

// --- اتصال به دیتابیس برای خواندن وضعیت تنظیمات ---
$host = $_SERVER['DB_HOST'] ?? 'localhost';
$dbName = $_SERVER['DB_NAME'] ?? 'salescoaching_seminar';
$user = $_SERVER['DB_USER'] ?? 'root';
$pass = $_SERVER['DB_PASS'] ?? '';

$isNoPayActive = true; // پیش‌فرض
try {
    $pdoSet = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $user, $pass);
    $stmtSet = $pdoSet->prepare("SELECT setting_value FROM settings WHERE setting_key = 'enable_no_prepayment'");
    $stmtSet->execute();
    $isNoPayActive = ($stmtSet->fetchColumn() === '1');
} catch (Exception $e) { /* خطا مهم نیست، با پیش‌فرض ادامه می‌دهیم */ }
?>

    <!-- CSS Shepherd.js -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js@10.0.1/dist/css/shepherd.css"/>

    <style>
        .btn-action { width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; }
        .status-pulse { width: 8px; height: 8px; background-color: #198754; border-radius: 50%; display: inline-block; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7); } 70% { box-shadow: 0 0 0 6px rgba(25, 135, 84, 0); } 100% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); } }
        .table td { vertical-align: middle; }
        .shepherd-button { background: #0d6efd; color: white; border-radius: 6px; padding: 8px 16px; margin-left: 5px; }
        .shepherd-button:hover { background: #0b5ed7; }
        .shepherd-footer .shepherd-button:last-child { background: #198754; }
        .shepherd-header { background-color: #f8f9fa; padding: 10px; border-bottom: 1px solid #dee2e6; }
        .shepherd-content { padding: 15px; }
    </style>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <div id="tour-step-1">
            <h3 class="fw-bolder text-dark mb-0">📋 لیست سمینارها</h3>
            <span class="text-muted small">مدیریت رویدادها</span>
        </div>
        <div class="d-flex gap-2">
            <!-- دکمه تنظیمات (جدید) -->
            <button class="btn btn-secondary shadow-sm" data-bs-toggle="modal" data-bs-target="#settingsModal">
                <i class="bi bi-gear-fill me-2"></i>تنظیمات
            </button>

            <!-- دکمه شروع آموزش -->
            <button id="startTourBtn" class="btn btn-outline-dark shadow-sm px-4"><i class="bi bi-mortarboard-fill"></i> شروع آموزش</button>
            <a id="tour-step-2" href="<?= BASE_URL ?>/admin/seminar/create" class="btn btn-primary shadow-sm px-4"><i class="bi bi-plus-lg"></i> سمینار جدید</a>
        </div>
    </div>

<?php if (isset($_GET['status'])): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4">
        <?= match($_GET['status']) {
            'guest_added' => '✅ مهمان جدید ثبت شد.',
            'duplicate_error' => '⚠️ شماره تکراری است.',
            'settings_updated' => '⚙️ تنظیمات با موفقیت ذخیره شد.',
            default => 'عملیات موفق بود.'
        } ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (empty($seminars)): ?>
    <div class="card border-0 shadow-sm text-center py-5">
        <div class="card-body">
            <h5 class="fw-bold text-dark">هیچ سمیناری تعریف نشده است!</h5>
            <a href="<?= BASE_URL ?>/admin/seminar/create" class="btn btn-outline-primary rounded-pill px-4 mt-3">+ شروع کار</a>
        </div>
    </div>
<?php else: ?>
    <div id="tour-step-3" class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                <tr><th class="ps-4">#</th><th>عنوان</th><th>تاریخ</th><th>وضعیت</th><th class="text-end pe-4">عملیات</th></tr>
                </thead>
                <tbody>
                <?php foreach ($seminars as $key => $seminar): ?>
                    <tr <?= $key === 0 ? 'id="first-seminar-row"' : '' ?>>
                        <td class="ps-4 fw-bold"><?= $seminar['id'] ?></td>
                        <td><span class="fw-bold text-dark"><?= htmlspecialchars($seminar['title']) ?></span></td>
                        <td><span class="text-muted small"><?= $seminar['date'] ?></span></td>
                        <td class="seminar-status">
                            <?php if ($seminar['is_active']): ?>
                                <span class="text-success fw-bold fs-7"><div class="status-pulse"></div> در حال برگزاری</span>
                            <?php else: ?>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">غیرفعال</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-4 seminar-actions">
                            <div class="d-flex justify-content-end gap-1">
                                <button class="btn btn-action btn-outline-success" data-bs-toggle="modal" data-bs-target="#addGuestModal"
                                        data-id="<?= $seminar['id'] ?>" data-title="<?= htmlspecialchars($seminar['title']) ?>" title="ثبت دستی">
                                    <i class="bi bi-person-plus-fill"></i>
                                </button>
                                <div class="vr mx-1"></div>
                                <a href="<?= BASE_URL ?>/admin/seminar/upload?id=<?= $seminar['id'] ?>" class="btn btn-action btn-outline-primary" title="آپلود"><i class="bi bi-upload"></i></a>
                                <!-- دکمه گزارش (Report) که به فایل بعدی اشاره دارد -->
                                <a href="<?= BASE_URL ?>/admin/report?id=<?= $seminar['id'] ?>" class="btn btn-action btn-outline-info" title="گزارش"><i class="bi bi-bar-chart-line"></i></a>
                                <?php if (!$seminar['is_active']): ?>
                                    <a href="<?= BASE_URL ?>/admin/seminar/activate?id=<?= $seminar['id'] ?>" class="btn btn-sm btn-light border text-warning fw-bold" onclick="return confirm('فعال شود؟')"><i class="bi bi-lightning-fill"></i> فعال</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

    <!-- مودال ثبت مهمان -->
    <div class="modal fade" id="addGuestModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold">ثبت مهمان برای: <span id="mTitle"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>/admin/guest/store" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="seminar_id" id="mId">
                        <input type="text" name="full_name" class="form-control mb-3" required placeholder="نام و نام خانوادگی">
                        <input type="tel" name="phone" class="form-control mb-3" required placeholder="شماره موبایل" maxlength="11">
                        <div class="form-check form-switch bg-light p-3 rounded">
                            <input class="form-check-input" type="checkbox" name="is_present" value="1" id="chkP" checked>
                            <label class="form-check-label fw-bold" for="chkP">ثبت حضور هم‌زمان؟</label>
                        </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">لغو</button><button type="submit" class="btn btn-success">ثبت</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- مودال تنظیمات (جدید) -->
    <div class="modal fade" id="settingsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title">تنظیمات سیستم پرداخت</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>/admin/settings/update" method="POST">
                    <div class="modal-body">
                        <div class="form-check form-switch p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                            <div>
                                <label class="form-check-label fw-bold ms-2" for="chkNoPay">گزینه "بدون پیش‌پرداخت"</label>
                                <small class="d-block text-muted" style="font-size: 11px;">نمایش گزینه در فرم پرداخت مهمان</small>
                            </div>
                            <input class="form-check-input m-0" type="checkbox" name="enable_no_prepayment" value="1" id="chkNoPay" <?= $isNoPayActive ? 'checked' : '' ?> style="width: 3em; height: 1.5em;">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">ذخیره تغییرات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- اسکریپت مودال -->
    <script>
        document.getElementById('addGuestModal').addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            document.getElementById('mId').value = btn.getAttribute('data-id');
            document.getElementById('mTitle').textContent = btn.getAttribute('data-title');
        });
    </script>

    <!-- JS Shepherd.js -->
    <script src="https://cdn.jsdelivr.net/npm/shepherd.js@10.0.1/dist/js/shepherd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startBtn = document.getElementById('startTourBtn');
            const tourEnabled = <?php echo !empty($seminars) ? 'true' : 'false'; ?>;

            if (!tourEnabled || !startBtn) {
                if(startBtn) startBtn.style.display = 'none';
                return;
            }

            const tour = new Shepherd.Tour({
                useModalOverlay: true,
                defaultStepOptions: {
                    classes: 'shadow-lg rounded-3',
                    scrollTo: true,
                    cancelIcon: { enabled: true, label: 'بستن' },
                    buttons: [
                        { action() { return this.back(); }, secondary: true, text: 'قبلی' },
                        { action() { return this.next(); }, text: 'بعدی' }
                    ]
                }
            });

            tour.addStep({
                id: 'step-1',
                title: 'داشبورد سمینارها',
                text: 'در این صفحه لیست تمام سمینارها را مشاهده می‌کنید.',
                attachTo: { element: '#tour-step-1', on: 'bottom' }
            });

            // آموزش دکمه جدید تنظیمات
            tour.addStep({
                id: 'step-settings',
                title: 'تنظیمات پرداخت',
                text: 'از اینجا می‌توانید گزینه "بدون پیش‌پرداخت" را برای فرم مهمانان فعال یا غیرفعال کنید.',
                attachTo: { element: '[data-bs-target="#settingsModal"]', on: 'bottom' }
            });

            tour.addStep({
                id: 'step-2',
                title: 'ایجاد سمینار',
                text: 'برای تعریف یک رویداد جدید کلیک کنید.',
                attachTo: { element: '#tour-step-2', on: 'bottom' }
            });

            tour.addStep({
                id: 'step-5',
                title: 'مدیریت و گزارش‌ها',
                text: 'با کلیک روی دکمه آبی رنگ (نمودار)، وارد صفحه گزارشات و لیست واریزی‌ها می‌شوید.',
                attachTo: { element: '.btn-outline-info', on: 'left' },
                buttons: [
                    { action() { return this.back(); }, secondary: true, text: 'قبلی' },
                    { action() { return this.complete(); }, text: 'پایان' }
                ]
            });

            startBtn.addEventListener('click', () => { tour.start(); });
        });
    </script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>