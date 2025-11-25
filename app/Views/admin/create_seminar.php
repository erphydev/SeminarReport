<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<!-- 1. لود کردن استایل‌های تقویم شمسی -->
<link rel="stylesheet" href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">

<style>
    /* تنظیم فونت تقویم */
    .datepicker-plot-area {
        font-family: 'Vazirmatn', sans-serif !important;
    }
    /* استایل اختصاصی فرم */
    .form-floating > label {
        right: 0; 
        left: auto;
        padding-right: 1.5rem; /* فاصله مناسب برای RTL */
    }
    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }
</style>

<div class="row justify-content-center align-items-center" style="min-height: 60vh;">
    <div class="col-lg-5 col-md-7 col-sm-10">
        
        <div class="card border-0 shadow-lg rounded-4">
            <!-- هدر کارت -->
            <div class="card-header bg-primary bg-gradient text-white border-0 py-3 rounded-top-4 d-flex align-items-center justify-content-between">
                <h5 class="fw-bold mb-0"><i class="bi bi-calendar-plus me-2"></i>تعریف سمینار جدید</h5>
                <i class="bi bi-pencil-square fs-5 opacity-50"></i>
            </div>

            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>/admin/seminar/create" method="POST">
                    
                    <!-- عنوان سمینار -->
                    <div class="form-floating mb-4">
                        <input type="text" name="title" class="form-control" id="titleInput" placeholder="عنوان" required>
                        <label for="titleInput" class="text-muted">عنوان سمینار (مثلاً: همایش هوش مصنوعی)</label>
                    </div>

                    <!-- تاریخ برگزاری (شمسی) -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary ms-1">تاریخ برگزاری</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-primary">
                                <i class="bi bi-calendar-event"></i>
                            </span>
                            <!-- نکته: تایپ باید text باشد تا تقویم باز شود، نه date -->
                            <input type="text" id="pcal" name="date" class="form-control border-start-0 p-3" 
                                   placeholder="انتخاب تاریخ..." required autocomplete="off">
                        </div>
                        <div class="form-text text-muted small me-2">تاریخ به صورت خودکار تبدیل می‌شود.</div>
                    </div>

                    <!-- دکمه‌ها -->
                    <div class="d-grid gap-2 mt-5">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm fw-bold rounded-3">
                            <i class="bi bi-check-lg me-1"></i> ذخیره و ایجاد
                        </button>
                        <a href="<?= BASE_URL ?>/admin" class="btn btn-light text-muted btn-sm mt-2">
                            انصراف و بازگشت
                        </a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<!-- 2. اسکریپت‌های مورد نیاز برای تقویم (jQuery + Persian Datepicker) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
<script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>

<script>
    $(document).ready(function() {
        $('#pcal').persianDatepicker({
            format: 'YYYY/MM/DD',    // فرمت خروجی (مثلاً 1403/09/05)
            initialValue: true,      // تاریخ امروز را پیش‌فرض بگذارد
            autoClose: true,         // بعد از انتخاب بسته شود
            calendar: {
                persian: {
                    locale: 'fa'     // زبان فارسی
                }
            },
            toolbox: {
                calendarSwitch: {
                    enabled: false   // دکمه تغییر به میلادی را مخفی کن
                }
            }
        });
    });
</script>