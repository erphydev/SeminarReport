<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<!-- استایل‌های اختصاصی این صفحه و فیکس کردن فوتر -->
<style>
    /* فیکس کردن فوتر به پایین صفحه */
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    .container {
        flex: 1; /* باعث می‌شود کانتینر فضای خالی را پر کند */
    }

    /* استایل ناحیه آپلود */
    .upload-zone {
        border: 2px dashed #dee2e6;
        border-radius: 10px;
        transition: all 0.3s;
        background-color: #f8f9fa;
        position: relative;
    }
    .upload-zone:hover {
        border-color: #0d6efd;
        background-color: #eff6ff;
    }
    .form-control-file {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        opacity: 0;
        cursor: pointer;
        z-index: 10;
    }
    .upload-icon {
        font-size: 3rem;
        color: #6c757d;
        transition: 0.3s;
    }
    .upload-zone:hover .upload-icon {
        color: #0d6efd;
        transform: translateY(-5px);
    }
</style>

<div class="row justify-content-center align-items-center" style="min-height: 60vh;">
    <div class="col-lg-5 col-md-7 col-sm-10">
        
        <!-- کارت اصلی -->
        <div class="card border-0 shadow-lg rounded-4">
            
            <!-- هدر کارت -->
            <div class="card-header bg-white border-0 pt-4 pb-0 text-center">
                <div class="mb-3">
                    <span class="bg-success bg-opacity-10 text-success p-3 rounded-circle d-inline-block">
                        <i class="bi bi-file-earmark-spreadsheet-fill fs-2"></i>
                    </span>
                </div>
                <h5 class="fw-bold text-dark">وارد کردن لیست مهمانان</h5>
                <p class="text-muted small">فایل اکسل (.xlsx) حاوی نام و شماره تماس را انتخاب کنید</p>
            </div>

            <div class="card-body p-4">
                
                <!-- دکمه دانلود نمونه -->
                <div class="alert alert-light border-start border-4 border-info shadow-sm d-flex align-items-center justify-content-between mb-4">
                    <div class="small text-muted">
                        <i class="bi bi-info-circle-fill text-info me-1"></i>
                        ابتدا فایل نمونه را دریافت کنید:
                    </div>
                    <a href="<?= BASE_URL ?>/admin/seminar/download-sample" class="btn btn-sm btn-outline-primary fw-bold rounded-pill px-3">
                        <i class="bi bi-download"></i> دانلود نمونه
                    </a>
                </div>

                <form action="<?= BASE_URL ?>/admin/seminar/import" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="seminar_id" value="<?= $_GET['id'] ?>">
                    
                    <!-- ناحیه آپلود فایل -->
                    <div class="mb-4">
                        <div class="upload-zone text-center p-4">
                            <input type="file" name="excel_file" class="form-control-file" required accept=".xlsx" onchange="updateFileName(this)">
                            <i class="bi bi-cloud-arrow-up upload-icon mb-2 d-block"></i>
                            <span class="fw-bold text-dark d-block" id="fileName">برای انتخاب فایل کلیک کنید</span>
                            <span class="small text-muted">فرمت مجاز: XLSX</span>
                        </div>
                    </div>

                    <!-- دکمه ارسال -->
                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 shadow-sm fw-bold">
                        <i class="bi bi-magic me-2"></i> شروع پردازش و ایمپورت
                    </button>
                </form>
            </div>

            <!-- فوتر کارت (دکمه بازگشت) -->
            <div class="card-footer bg-light border-0 text-center py-3 rounded-bottom-4">
                <a href="<?= BASE_URL ?>/admin" class="text-decoration-none text-muted fw-bold small">
                    <i class="bi bi-arrow-right"></i> بازگشت به داشبورد
                </a>
            </div>
        </div>
        
    </div>
</div>

<script>
    // اسکریپت ساده برای نمایش نام فایل انتخاب شده
    function updateFileName(input) {
        const fileNameSpan = document.getElementById('fileName');
        if (input.files && input.files.length > 0) {
            fileNameSpan.innerText = input.files[0].name;
            fileNameSpan.classList.add('text-success');
            fileNameSpan.classList.remove('text-dark');
        } else {
            fileNameSpan.innerText = 'برای انتخاب فایل کلیک کنید';
            fileNameSpan.classList.remove('text-success');
            fileNameSpan.classList.add('text-dark');
        }
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>