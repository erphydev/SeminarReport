<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<style>
    .btn-action { width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; }
    .status-pulse { width: 8px; height: 8px; background-color: #198754; border-radius: 50%; display: inline-block; animation: pulse 2s infinite; }
    @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7); } 70% { box-shadow: 0 0 0 6px rgba(25, 135, 84, 0); } 100% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); } }
    .table td { vertical-align: middle; }
</style>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="fw-bolder text-dark mb-0">📋 لیست سمینارها</h3>
        <span class="text-muted small">مدیریت رویدادها</span>
    </div>
    <a href="<?= BASE_URL ?>/admin/seminar/create" class="btn btn-primary shadow-sm px-4"><i class="bi bi-plus-lg"></i> سمینار جدید</a>
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
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr><th class="ps-4">#</th><th>عنوان</th><th>تاریخ</th><th>وضعیت</th><th class="text-end pe-4">عملیات</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($seminars as $seminar): ?>
                        <tr>
                            <td class="ps-4 fw-bold"><?= $seminar['id'] ?></td>
                            <td><span class="fw-bold text-dark"><?= htmlspecialchars($seminar['title']) ?></span></td>
                            <td><span class="text-muted small"><?= $seminar['date'] ?></span></td>
                            <td>
                                <?php if ($seminar['is_active']): ?>
                                    <span class="text-success fw-bold fs-7"><div class="status-pulse"></div> در حال برگزاری</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">غیرفعال</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
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

<script>
    document.getElementById('addGuestModal').addEventListener('show.bs.modal', function (e) {
        var btn = e.relatedTarget;
        document.getElementById('mId').value = btn.getAttribute('data-id');
        document.getElementById('mTitle').textContent = btn.getAttribute('data-title');
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>