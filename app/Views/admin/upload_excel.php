<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="alert alert-info">
            <a href="<?= BASE_URL ?>/admin/seminar/download-sample">📥 دانلود فایل نمونه</a>
        </div>

        <div class="card">
            <div class="card-header">آپلود لیست مهمانان</div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>/admin/seminar/import" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="seminar_id" value="<?= $_GET['id'] ?>">
                    
                    <div class="mb-3">
                        <label>فایل اکسل (.xlsx)</label>
                        <input type="file" name="excel_file" class="form-control" required accept=".xlsx">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">شروع ایمپورت</button>
                </form>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/admin">بازگشت</a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>