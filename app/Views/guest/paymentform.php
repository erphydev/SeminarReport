<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فرم ارسال فیش واریزی</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #0ba360 0%, #3cba92 100%);
            --bg-gradient: linear-gradient(135deg, #e0f7fa 0%, #f1f8e9 100%);
            --card-bg: rgba(255, 255, 255, 0.92);
        }

        body {
            font-family: 'Vazirmatn', Tahoma, sans-serif;
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }

        /* Card Styling */
        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Inputs */
        .form-control, .btn {
            border-radius: 12px;
            padding: 12px 15px;
            font-size: 0.95rem;
        }

        .form-control {
            background-color: #f8f9fa;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background-color: #fff;
            border-color: #3cba92;
            box-shadow: 0 0 0 4px rgba(60, 186, 146, 0.1);
        }

        .form-label {
            font-weight: 600;
            color: #444;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        /* Custom Input Group for Check Button */
        .input-group-custom {
            background: #f8f9fa;
            border-radius: 14px;
            padding: 5px;
            display: flex;
            border: 1px solid #eee;
            transition: border-color 0.3s;
        }
        
        .input-group-custom:focus-within {
            border-color: #3cba92;
            box-shadow: 0 0 0 4px rgba(60, 186, 146, 0.1);
        }

        .input-group-custom input {
            border: none;
            background: transparent;
            box-shadow: none !important;
        }

        .btn-check-custom {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 10px !important;
            transition: transform 0.2s;
        }

        .btn-check-custom:hover {
            transform: scale(1.05);
            color: white;
        }

        /* Upload Area */
        .upload-zone {
            border: 2px dashed #cbd5e0;
            border-radius: 16px;
            background-color: #fbfbfc;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .upload-zone:hover {
            border-color: #3cba92;
            background-color: #f0fdf4;
        }

        .upload-zone i {
            font-size: 2.5rem;
            color: #3cba92;
            transition: transform 0.3s;
        }

        .upload-zone:hover i {
            transform: translateY(-5px);
        }

        /* Submit Button */
        .btn-submit-custom {
            background: var(--primary-gradient);
            border: none;
            color: white;
            font-weight: 700;
            letter-spacing: 0.5px;
            box-shadow: 0 10px 20px rgba(11, 163, 96, 0.2);
            transition: all 0.3s;
        }

        .btn-submit-custom:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px rgba(11, 163, 96, 0.3);
        }

        .btn-submit-custom:disabled {
            background: #bdc3c7;
            transform: none;
        }

        /* Readonly Field */
        .readonly-custom {
            background-color: #e9ecef;
            color: #495057;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        /* Alerts */
        .alert {
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card-header-custom {
            background: transparent;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding-bottom: 20px;
            text-align: center;
        }
        
        .icon-circle {
            width: 70px;
            height: 70px;
            background: rgba(60, 186, 146, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: #0ba360;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center w-100 m-0">
            <div class="col-md-8 col-lg-5 p-0">
                
                <?php if (isset($_GET['status'])): ?>
                    <div class="alert alert-dismissible fade show mb-4 d-flex align-items-center <?= $_GET['status'] == 'success' ? 'alert-success' : 'alert-danger' ?>">
                        <i class="bi <?= $_GET['status'] == 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> fs-4 me-2"></i>
                        <div>
                            <?= match($_GET['status']) { 
                                'success' => 'اطلاعات شما با موفقیت ثبت و ارسال شد.', 
                                'guest_not_found' => 'شماره وارد شده در لیست مهمان‌ها یافت نشد.', 
                                'upload_error' => 'مشکلی در آپلود فایل پیش آمده است.', 
                                'db_error' => 'خطای پایگاه داده رخ داده است.',
                                default => 'خطای ناشناخته رخ داده است.' 
                            } ?>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="glass-card p-4 p-md-5">
                    <div class="card-header-custom">
                        <div class="icon-circle">
                            <i class="bi bi-credit-card-2-front-fill fs-1"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-1">ثبت فیش واریزی</h4>
                        <p class="text-muted small mb-0">لطفا اطلاعات پرداخت را با دقت وارد نمایید</p>
                    </div>
                    
                    <form action="<?= BASE_URL ?>/payment/submit" method="POST" enctype="multipart/form-data" id="payForm" class="mt-4">
                        <!-- آیدی مخفی مهمان -->
                        <input type="hidden" name="guest_id" id="guest_id">

                        <div class="row g-4">
                            
                            <!-- بخش استعلام شماره -->
                            <div class="col-12">
                                <label class="form-label">شماره موبایل ثبت‌نام شده</label>
                                <div class="input-group-custom">
                                    <input type="tel" id="phoneInput" name="phone" class="form-control" required placeholder="مثال: 09123456789" maxlength="11" autocomplete="off">
                                    <button class="btn-check-custom" type="button" id="checkBtn">
                                        <span id="btnText">بررسی</span>
                                        <div id="btnLoader" class="spinner-border spinner-border-sm d-none" role="status"></div>
                                    </button>
                                </div>
                                <div id="search-msg" class="small mt-2 ms-2 fw-bold" style="min-height: 20px;"></div>
                            </div>

                            <!-- نام و نام خانوادگی (خودکار) -->
                            <div class="col-12">
                                <label class="form-label">نام مهمان</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0 rounded-end-3 ps-3"><i class="bi bi-person text-muted"></i></span>
                                    <input type="text" id="fullNameDisplay" class="form-control readonly-custom rounded-start-3" readonly placeholder="پس از تایید شماره نمایش داده می‌شود">
                                </div>
                            </div>

<!-- کارشناس ثبت نام -->
                                <div class="col-12">
                                    <label class="form-label fw-bold text-muted small">نام کارشناس ثبت کننده فیش</label>
                                    <input type="text" name="payment_expert_name" class="form-control bg-light border-0 py-2" required placeholder="مثال: خانم رضایی">
                                </div>

                                <!-- مبلغ پرداختی (جدید) -->
                                <div class="col-12">
                                    <label class="form-label fw-bold text-muted small">مبلغ واریزی (تومان)</label>
                                    <input type="text" name="amount" id="amountInput" class="form-control bg-light border-0 py-2 fs-5 fw-bold text-primary" required placeholder="مثال: 1,000,000" onkeyup="formatCurrency(this)">
                                    <small class="text-muted" id="amountText"></small>
                                </div>


                            <!-- آپلود -->
                            <div class="col-12">
                                <label class="form-label">تصویر فیش واریزی</label>
                                <div class="upload-zone p-4 text-center cursor-pointer position-relative">
                                    <input type="file" name="receipt_image" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" style="cursor: pointer;" accept="image/*,.pdf" required onchange="showFileName(this)">
                                    <div id="upload-placeholder">
                                        <i class="bi bi-cloud-arrow-up-fill mb-2 d-block"></i>
                                        <span class="fw-bold text-dark d-block">انتخاب فایل</span>
                                        <small class="text-muted">تصویر یا فایل PDF را اینجا رها کنید</small>
                                    </div>
                                    <div id="file-name-display" class="d-none mt-2">
                                        <div class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fs-6">
                                            <i class="bi bi-check2-circle me-1"></i> <span id="file-name-text"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-submit-custom w-100 py-3" id="submitBtn" disabled>
                                    ثبت نهایی اطلاعات <i class="bi bi-arrow-left ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <p class="text-center text-muted opacity-50 mt-4 small">طراحی شده برای سیستم مدیریت همایش</p>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showFileName(input) {
            const placeholder = document.getElementById('upload-placeholder');
            const display = document.getElementById('file-name-display');
            const text = document.getElementById('file-name-text');

            if(input.files && input.files[0]) {
                placeholder.classList.add('d-none');
                display.classList.remove('d-none');
                text.innerText = input.files[0].name;
            } else {
                placeholder.classList.remove('d-none');
                display.classList.add('d-none');
            }
        }

        // اسکریپت AJAX
        const phoneInput = document.getElementById('phoneInput');
        const checkBtn = document.getElementById('checkBtn');
        const btnText = document.getElementById('btnText');
        const btnLoader = document.getElementById('btnLoader');
        const msgDiv = document.getElementById('search-msg');
        const nameDisplay = document.getElementById('fullNameDisplay');
        const guestIdInput = document.getElementById('guest_id');
        const submitBtn = document.getElementById('submitBtn');

        function checkPhone() {
            const phone = phoneInput.value;
            if(phone.length < 10) {
                msgDiv.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> شماره نامعتبر است</span>';
                phoneInput.focus();
                return;
            }

            // UI Loading State
            msgDiv.innerHTML = '';
            btnText.classList.add('d-none');
            btnLoader.classList.remove('d-none');
            checkBtn.disabled = true;
            
            const formData = new FormData();
            formData.append('phone', phone);

            fetch('<?= BASE_URL ?>/payment/check-phone', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    msgDiv.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill"></i> تایید شد</span>';
                    nameDisplay.value = data.full_name;
                    guestIdInput.value = data.guest_id;
                    
                    // فعال سازی دکمه و افکت
                    submitBtn.disabled = false;
                    phoneInput.classList.add('is-valid');
                    phoneInput.classList.remove('is-invalid');
                } else {
                    msgDiv.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-circle"></i> ' + data.message + '</span>';
                    nameDisplay.value = '';
                    guestIdInput.value = '';
                    submitBtn.disabled = true;
                    phoneInput.classList.add('is-invalid');
                    phoneInput.classList.remove('is-valid');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                msgDiv.innerHTML = '<span class="text-danger">خطا در ارتباط با سرور</span>';
            })
            .finally(() => {
                // Reset Button State
                btnText.classList.remove('d-none');
                btnLoader.classList.add('d-none');
                checkBtn.disabled = false;
            });
        }

        checkBtn.addEventListener('click', checkPhone);
        
        // اینتر روی اینپوت هم کار کنه
        phoneInput.addEventListener("keypress", function(event) {
            if (event.key === "Enter") {
                event.preventDefault();
                checkBtn.click();
            }
        });

        // ریست کردن وضعیت در صورت تغییر شماره
        phoneInput.addEventListener('input', function() {
            submitBtn.disabled = true;
            nameDisplay.value = '';
            msgDiv.innerHTML = '';
            phoneInput.classList.remove('is-valid', 'is-invalid');
            document.getElementById('file-name-display').classList.add('d-none');
            document.getElementById('upload-placeholder').classList.remove('d-none');
        });
        
         function formatCurrency(input) {
            // حذف هر کاراکتری جز عدد
            let val = input.value.replace(/[^0-9]/g, '');
            if (!val) return;
            
            // فرمت کردن عدد
            input.value = parseInt(val).toLocaleString();
        }
    </script>
</body>
</html>