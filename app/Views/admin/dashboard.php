<?php 
// ۱. استفاده از کلاس‌ها قبل از لود هدر
use App\Services\JalaliDate;

require_once __DIR__ . '/../layouts/header.php'; 
?>

<style>
    /* استایل اختصاصی برای دکمه‌های عملیاتی */
    .btn-action {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .btn-action:hover { transform: translateY(-2px); }
    
    /* انیمیشن برای وضعیت فعال */
    .status-pulse {
        width: 10px;
        height: 10px;
        background-color: #198754;
        border-radius: 50%;
        display: inline-block;
        margin-left: 5px;
        box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7);
        animation: pulse-green 2s infinite;
    }

    @keyframes pulse-green {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(25, 135, 84, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); }
    }
    
    .table tbody tr td { vertical-align: middle; padding: 1rem 0.75rem; }
</style>

<!-- هدر صفحه -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="fw-bolder text-dark mb-0">📋 لیست سمینارها</h3>
        <span class="text-muted small">مدیریت و مشاهده رویدادهای برگزار شده</span>
    </div>
    <a href="<?= BASE_URL ?>/admin/seminar/create" class="btn btn-primary shadow-sm px-4">
        <i class="bi bi-plus-lg"></i> ایجاد سمینار جدید
    </a>
</div>

<?php if (empty($seminars)): ?>
    <!-- حالت خالی (Empty State) -->
    <div class="card border-0 shadow-sm text-center py-5">
        <div class="card-body">
            <div class="mb-3 text-muted opacity-25">
                <i class="bi bi-calendar-x" style="font-size: 5rem;"></i>
            </div>
            <h5 class="fw-bold text-dark">هنوز هیچ سمیناری تعریف نشده است!</h5>
            <p class="text-muted mb-4">برای شروع مدیریت مهمانان، اولین سمینار خود را ایجاد کنید.</p>
            <a href="<?= BASE_URL ?>/admin/seminar/create" class="btn btn-outline-primary rounded-pill px-4">
                + شروع کار
            </a>
        </div>
    </div>
<?php else: ?>
    <!-- جدول سمینارها -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary">
                    <tr>
                        <th class="ps-4" style="width: 50px;">#</th>
                        <th>عنوان سمینار</th>
                        <th>تاریخ برگزاری</th>
                        <th>وضعیت</th>
                        <th class="text-end pe-4">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($seminars as $seminar): ?>
                        <tr>
                            <td class="ps-4 text-muted fw-bold"><?= $seminar['id'] ?></td>
                            
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded p-2 me-3 d-none d-md-block">
                                        <i class="bi bi-easel2-fill"></i>
                                    </div>
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($seminar['title']) ?></span>
                                </div>
                            </td>
                            
                            <td>
                                <span class="text-muted small">
                                    <i class="bi bi-calendar4-week me-1"></i>
                                    <?= $seminar['date'] ?> 
                                    <!-- اگر تاریخ نیاز به تبدیل دارد: JalaliDate::format($seminar['date']) -->
                                </span>
                            </td>
                            
                            <td>
                                <?php if ($seminar['is_active']): ?>
                                    <div class="d-flex align-items-center text-success fw-bold fs-7">
                                        <div class="status-pulse"></div>
                                        در حال برگزاری
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border fw-normal">غیرفعال</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    
                                    <!-- دکمه آپلود -->
                                    <a href="<?= BASE_URL ?>/admin/seminar/upload?id=<?= $seminar['id'] ?>" 
                                       class="btn btn-action btn-outline-primary bg-white" 
                                       data-bs-toggle="tooltip" title="آپلود لیست اکسل">
                                        <i class="bi bi-file-earmark-arrow-up"></i>
                                    </a>
                                    
                                    <!-- دکمه گزارش -->
                                    <a href="<?= BASE_URL ?>/admin/report?id=<?= $seminar['id'] ?>" 
                                       class="btn btn-action btn-outline-info bg-white" 
                                       data-bs-toggle="tooltip" title="مشاهده گزارش و آمار">
                                        <i class="bi bi-bar-chart-line"></i>
                                    </a>

                                    <!-- جدا کننده -->
                                    <div class="vr mx-1 bg-secondary opacity-25"></div>

                                    <!-- دکمه فعال‌سازی (فقط اگر غیرفعال است) -->
                                    <?php if (!$seminar['is_active']): ?>
                                        <a href="<?= BASE_URL ?>/admin/seminar/activate?id=<?= $seminar['id'] ?>" 
                                           class="btn btn-sm btn-light border text-warning fw-bold d-flex align-items-center gap-1"
                                           data-bs-toggle="tooltip" title="فعال کردن این سمینار"
                                           onclick="return confirm('⚠️ توجه:\nبا فعال کردن این سمینار، سایر سمینارها غیرفعال می‌شوند.\nآیا مطمئن هستید؟')">
                                           <i class="bi bi-lightning-charge-fill"></i> فعال‌سازی
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small d-flex align-items-center ms-2" style="opacity: 0.5;">
                                            <i class="bi bi-check2-circle me-1"></i> فعال
                                        </span>
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>