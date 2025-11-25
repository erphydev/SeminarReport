<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<!-- افزودن CSS کتابخانه Shepherd.js برای استایل راهنمای تعاملی -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js@10.0.1/dist/css/shepherd.css"/>

<style>
    .btn-action { width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; }
    .status-pulse { width: 8px; height: 8px; background-color: #198754; border-radius: 50%; display: inline-block; animation: pulse 2s infinite; }
    @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7); } 70% { box-shadow: 0 0 0 6px rgba(25, 135, 84, 0); } 100% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); } }
    .table td { vertical-align: middle; }

    /* استایل‌های سفارشی برای دکمه‌های راهنما */
    .shepherd-button { background: #0d6efd; color: white; border-radius: 6px; padding: 8px 16px; margin-left: 5px; } /* کمی فاصله بین دکمه ها */
    .shepherd-button:hover { background: #0b5ed7; }
    .shepherd-footer .shepherd-button:last-child { background: #198754; } /* استایل دکمه "بعدی" یا "پایان" */
    .shepherd-header { background-color: #f8f9fa; padding: 10px; border-bottom: 1px solid #dee2e6; } /* استایل هدر پاپ آپ */
    .shepherd-content { padding: 15px; } /* استایل محتوای اصلی پاپ آپ */
</style>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
    <div id="tour-step-1">
        <h3 class="fw-bolder text-dark mb-0">📋 لیست سمینارها</h3>
        <span class="text-muted small">مدیریت رویدادها</span>
    </div>
    <div class="d-flex gap-2">
        <!-- دکمه شروع آموزش -->
        <button id="startTourBtn" class="btn btn-outline-dark shadow-sm px-4"><i class="bi bi-mortarboard-fill"></i> شروع آموزش</button>
        <a id="tour-step-2" href="<?= BASE_URL ?>/admin/seminar/create" class="btn btn-primary shadow-sm px-4"><i class="bi bi-plus-lg"></i> سمینار جدید</a>
    </div>
</div>

<?php if (isset($_GET['status'])): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4">
        <?= match($_GET['status']) { 'guest_added' => '✅ مهمان جدید ثبت شد.', 'duplicate_error' => '⚠️ شماره تکراری است.', default => 'عملیات موفق بود.' } ?>
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
                        <!-- برای مراحل راهنما، اولین ردیف جدول را با یک ID خاص علامت‌گذاری می‌کنیم -->
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
                                    <!-- دکمه ثبت دستی مهمان -->
                                    <button class="btn btn-action btn-outline-success" data-bs-toggle="modal" data-bs-target="#addGuestModal"
                                            data-id="<?= $seminar['id'] ?>" data-title="<?= htmlspecialchars($seminar['title']) ?>" title="ثبت دستی">
                                        <i class="bi bi-person-plus-fill"></i>
                                    </button>
                                    <div class="vr mx-1"></div>
                                    <a href="<?= BASE_URL ?>/admin/seminar/upload?id=<?= $seminar['id'] ?>" class="btn btn-action btn-outline-primary" title="آپلود"><i class="bi bi-upload"></i></a>
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

<!-- اسکریپت مودال (برای پر کردن مقادیر مودال هنگام کلیک) -->
<script>
    document.getElementById('addGuestModal').addEventListener('show.bs.modal', function (e) {
        var btn = e.relatedTarget; // دکمه‌ای که مودال را باز کرده است
        document.getElementById('mId').value = btn.getAttribute('data-id');
        document.getElementById('mTitle').textContent = btn.getAttribute('data-title');
    });
</script>

<!-- افزودن JS کتابخانه Shepherd.js -->
<script src="https://cdn.jsdelivr.net/npm/shepherd.js@10.0.1/dist/js/shepherd.min.js"></script>

<!-- اسکریپت راهنمای تعاملی (نسخه بهبود یافته) -->
<script>
    // منتظر می‌مانیم تا کل محتوای صفحه به طور کامل بارگذاری شود
    document.addEventListener('DOMContentLoaded', function() {
        
        const startBtn = document.getElementById('startTourBtn');
        const tourEnabled = <?php echo !empty($seminars) ? 'true' : 'false'; ?>; // بررسی وضعیت فعال بودن تور با PHP

        // اگر تور نباید فعال باشد (چون سمیناری نیست)، دکمه شروع را مخفی می‌کنیم
        if (!tourEnabled) {
            if(startBtn) startBtn.style.display = 'none';
            return; // از ادامه اجرای اسکریپت جلوگیری می‌کنیم
        }

        // بررسی می‌کنیم که آیا دکمه شروع و ردیف اول جدول وجود دارند یا خیر
        const firstRow = document.getElementById('first-seminar-row');
        if (!startBtn || !firstRow) {
            console.warn('Shepherd Tour: دکمه شروع آموزش یا ردیف اول سمینار پیدا نشد. آموزش غیرفعال است.');
            if(startBtn) startBtn.style.display = 'none'; // اگر دکمه وجود نداشت، آن را مخفی می‌کنیم
            return; // از ادامه اجرای اسکریپت جلوگیری می‌کنیم
        }
        
        // تنظیمات کلی تور
        const tour = new Shepherd.Tour({
            useModalOverlay: true, // یک لایه تیره پشت پاپ‌آپ می‌اندازد تا تمرکز روی عنصر باشد
            defaultStepOptions: {
                classes: 'shadow-lg rounded-3', // کلاس برای استایل پاپ‌آپ
                scrollTo: true, // اسکرول کردن صفحه به سمت عنصر هدف
                cancelIcon: {
                    enabled: true,
                    label: 'بستن آموزش' // متن آیکون بستن
                },
                buttons: [
                    {
                        action() { return this.back(); }, // عملکرد دکمه قبلی
                        secondary: true, // استایل دکمه ثانویه
                        text: 'قبلی' // متن دکمه قبلی
                    },
                    {
                        action() { return this.next(); }, // عملکرد دکمه بعدی
                        text: 'بعدی' // متن دکمه بعدی
                    }
                ]
            }
        });

        // تعریف مراحل آموزش
        tour.addStep({
            id: 'step-1',
            title: 'خوش آمدید!',
            text: 'اینجا صفحه مدیریت سمینارها است. در این راهنما با بخش‌های مختلف آن آشنا می‌شوید.',
            attachTo: { element: '#tour-step-1', on: 'bottom' } // اتصال پاپ‌آپ به عنصر با ID tour-step-1 در پایین آن
        });

        tour.addStep({
            id: 'step-2',
            title: 'ایجاد سمینار جدید',
            text: 'برای تعریف یک رویداد یا سمینار جدید، روی این دکمه کلیک کنید.',
            attachTo: { element: '#tour-step-2', on: 'bottom' } // اتصال به دکمه ایجاد سمینار جدید
        });

        tour.addStep({
            id: 'step-3',
            title: 'لیست سمینارها',
            text: 'تمام سمینارهایی که تعریف کرده‌اید در این جدول نمایش داده می‌شوند.',
            attachTo: { element: '#tour-step-3', on: 'top' } // اتصال به کانتینر جدول
        });
        
        tour.addStep({
            id: 'step-4',
            title: 'وضعیت سمینار',
            text: 'در این ستون می‌توانید وضعیت فعال یا غیرفعال بودن سمینار را ببینید. نقطه سبز چشمک‌زن به معنی "در حال برگزاری" است.',
            attachTo: { element: '#first-seminar-row .seminar-status', on: 'left' } // اتصال به ستون وضعیت اولین سمینار
        });

        tour.addStep({
            id: 'step-5',
            title: 'بخش عملیات',
            text: 'از اینجا می‌توانید کارهای مختلفی مانند ثبت‌نام دستی مهمان، آپلود فایل، مشاهده گزارش و فعال‌سازی سمینار را انجام دهید.',
            attachTo: { element: '#first-seminar-row .seminar-actions', on: 'left' } // اتصال به ستون عملیات اولین سمینار
        });

        tour.addStep({
            id: 'step-6',
            title: 'ثبت دستی مهمان',
            text: 'با کلیک روی این دکمه، یک فرم باز می‌شود که می‌توانید اطلاعات یک مهمان, را به صورت دستی وارد و ثبت‌نام کنید.',
            attachTo: { element: '#first-seminar-row .seminar-actions .btn-outline-success', on: 'top' }, // اتصال به دکمه ثبت مهمان
            buttons: [ // اورراید کردن دکمه‌های پیش‌فرض برای مرحله آخر
                {
                    action() { return this.back(); },
                    secondary: true,
                    text: 'قبلی'
                },
                {
                    action() { return this.complete(); }, // پایان دادن به تور
                    text: 'پایان آموزش'
                }
            ]
        });

        // اتصال رویداد کلیک به دکمه "شروع آموزش"
        startBtn.addEventListener('click', () => {
            tour.start(); // شروع تور هنگام کلیک روی دکمه
        });

    }); // پایان DOMContentLoaded
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>