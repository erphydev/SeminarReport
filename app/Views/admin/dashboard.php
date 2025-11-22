<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-secondary">📋 لیست سمینارها</h2>
    <a href="<?= BASE_URL ?>/admin/seminar/create" class="btn btn-success">
        + ایجاد سمینار جدید
    </a>
</div>

<?php if (empty($seminars)): ?>
    <div class="alert alert-info text-center">
        هنوز هیچ سمیناری تعریف نشده است. اولین سمینار خود را بسازید!
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>عنوان سمینار</th>
                            <th>تاریخ برگزاری</th>
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- 🔴 اصلاح مهم: نام متغیر حلقه را seminar گذاشتیم -->
                        <?php foreach ($seminars as $seminar): ?>
                            <tr>
                                <td><?= $seminar['id'] ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($seminar['title']) ?></td>
                                <td><?= $seminar['date'] ?></td>
                                
                                <!-- ستون وضعیت -->
                                <td>
                                    <?php if ($seminar['is_active']): ?>
                                        <span class="badge bg-success fs-6">✅ در حال برگزاری</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">غیرفعال</span>
                                    <?php endif; ?>
                                </td>

                                <!-- ستون عملیات -->
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/seminar/upload?id=<?= $seminar['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary" title="آپلود اکسل">
                                        📂 آپلود
                                    </a>
                                    
                                    <a href="<?= BASE_URL ?>/admin/report?id=<?= $seminar['id'] ?>" 
                                       class="btn btn-sm btn-outline-info" title="مشاهده گزارش">
                                        📊 گزارش
                                    </a>

                                    <!-- دکمه فعال‌سازی (فقط برای سمینارهای غیرفعال) -->
                                    <?php if (!$seminar['is_active']): ?>
                                        <a href="<?= BASE_URL ?>/admin/seminar/activate?id=<?= $seminar['id'] ?>" 
                                           class="btn btn-sm btn-warning fw-bold text-dark ms-1"
                                           onclick="return confirm('آیا مطمئن هستید؟ با فعال کردن این سمینار، بقیه سمینارها غیرفعال می‌شوند.')">
                                           ⚡ فعال‌سازی
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>