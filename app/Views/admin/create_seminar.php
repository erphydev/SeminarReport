<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">تعریف سمینار جدید</div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>/admin/seminar/create" method="POST">
                    <div class="mb-3">
                        <label>عنوان سمینار</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>تاریخ برگزاری</label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">ذخیره</button>
                <a href="<?= BASE_URL ?>/admin" class="btn btn-link">بازگشت</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>