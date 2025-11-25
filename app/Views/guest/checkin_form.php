<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به سمینار</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --bg-color: #f3f4f6;
            --card-bg-color: #ffffff;
            --text-color: #1f2937;
            --text-muted-color: #6b7280;
            --accent-color: #4f46e5;
            --accent-color-dark: #4338ca;
            --success-color: #10b981;
            --error-color: #ef4444;
            --border-color: #e5e7eb;
        }

        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: var(--bg-color);
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: var(--text-color);
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background-color: var(--card-bg-color);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.05), 0 4px 6px -4px rgb(0 0 0 / 0.05);
            padding: 3rem 2.5rem;
            position: relative;
            text-align: center;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .brand-icon {
            width: 80px;
            height: 80px;
            background-color: var(--accent-color);
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: -80px;
            margin-bottom: 20px;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
            font-size: 2.2rem;
        }

        .form-control-lg-custom {
            font-size: 1.8rem;
            text-align: center;
            letter-spacing: 5px;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 15px;
            background-color: #f9fafb;
            font-weight: 700;
            color: var(--text-color);
            transition: all 0.2s ease-in-out;
        }
        .form-control-lg-custom:focus {
            background-color: white;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
            outline: none;
        }

        .btn-checkin {
            background-color: var(--accent-color);
            border: none;
            padding: 16px;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 12px;
            margin-top: 20px;
            color: white;
            width: 100%;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }
        .btn-checkin:hover {
            background-color: var(--accent-color-dark);
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(79, 70, 229, 0.2);
        }
        
        /* استایل دکمه ثبت نام جدید */
        .btn-register {
            background: transparent;
            border: 1px dashed var(--accent-color);
            color: var(--accent-color);
            padding: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 12px;
            margin-top: 15px;
            width: 100%;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-register:hover {
            background: rgba(79, 70, 229, 0.05);
        }

        /* لایه نتیجه */
        .result-overlay {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.98);
            opacity: 0; visibility: hidden;
            transition: all 0.3s ease;
            transform: scale(0.95);
            z-index: 100;
        }
        .show-result { opacity: 1; visibility: visible; transform: scale(1); }
        .result-overlay.success .result-icon, .result-overlay.success h2 { color: var(--success-color); }
        .result-overlay.error .result-icon, .result-overlay.error h2 { color: var(--error-color); }
        .result-icon { font-size: 5rem; margin-bottom: 1rem; }
    </style>
</head>
<body>

<!-- کارت اصلی -->
<div class="login-card">
    <div class="brand-icon"><i class="bi bi-person-check"></i></div>
    <h2 class="fw-bold mb-1">ورود به رویداد</h2>
    <p class="text-muted small mb-4"><?= htmlspecialchars($activeSeminar['title'] ?? 'سیستم حضور و غیاب') ?></p>

    <!-- فرم ورود (اصلی) -->
    <form id="checkinForm" autocomplete="off">
        <input type="hidden" name="seminar_id" value="<?= $activeSeminar['id'] ?? 0 ?>">
        <div class="mb-3">
            <input type="tel" class="form-control form-control-lg-custom" id="phone" name="phone" placeholder="09--" maxlength="11" autofocus required>
        </div>
        <button type="submit" class="btn btn-checkin shadow-sm" id="submitBtn">ثبت ورود</button>
        
        <!-- دکمه باز کردن مودال ثبت نام -->
        <button type="button" class="btn btn-register" data-bs-toggle="modal" data-bs-target="#registerModal">
            <i class="bi bi-plus-circle me-1"></i> ثبت نام مهمان جدید
        </button>
    </form>

    <!-- لایه نتیجه (پیام سبز/قرمز) -->
    <div id="resultBox" class="result-overlay">
        <i id="resultIcon" class="bi"></i>
        <h2 class="fw-bold" id="resultTitle"></h2>
        <p class="fs-5 px-3" id="resultMessage"></p>
    </div>
</div>

<!-- مودال ثبت نام مهمان جدید (پنهان) -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 bg-light rounded-top-4">
                <h5 class="modal-title fw-bold text-dark">ثبت نام سریع</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="registerForm">
                    <input type="hidden" name="seminar_id" value="<?= $activeSeminar['id'] ?? 0 ?>">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">نام و نام خانوادگی</label>
                        <input type="text" name="full_name" class="form-control form-control-lg bg-light border-0" required placeholder="مثال: علی محمدی">
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">شماره موبایل</label>
                        <input type="tel" name="phone" class="form-control form-control-lg bg-light border-0" required placeholder="09..." maxlength="11">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 fw-bold" id="regSubmitBtn">
                        ثبت و ورود همزمان
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- اسکریپت‌ها -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById('checkinForm');
        const registerForm = document.getElementById('registerForm');
        const phoneInput = document.getElementById('phone');
        const resultBox = document.getElementById('resultBox');
        const submitBtn = document.getElementById('submitBtn');
        const regSubmitBtn = document.getElementById('regSubmitBtn');
        
        // صداها
        const audioSuccess = new Audio('https://actions.google.com/sounds/v1/cartoon/pop.ogg');
        const audioError = new Audio('https://actions.google.com/sounds/v1/cartoon/clang_and_wobble.ogg');

        // 1. فوکوس خودکار (حالت کیوسک)
        phoneInput.focus();
        document.body.addEventListener('click', (e) => {
            // اگر مودال باز نیست و روی چیز خاصی کلیک نشده، فوکوس برگردد
            if (!document.querySelector('.modal.show') && e.target !== phoneInput && !submitBtn.contains(e.target)) {
                phoneInput.focus();
            }
        });

        // محدودیت ورودی به عدد
        phoneInput.addEventListener('input', function() { this.value = this.value.replace(/[^0-9]/g, ''); });

        // 2. هندل کردن فرم ورود (Check-in)
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            handleRequest('<?= BASE_URL ?>/checkin/verify', new FormData(form), submitBtn);
        });

        // 3. هندل کردن فرم ثبت نام (Register)
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // بستن مودال
            const modalEl = document.getElementById('registerModal');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            modalInstance.hide();

            // ارسال درخواست
            handleRequest('<?= BASE_URL ?>/checkin/register', new FormData(registerForm), regSubmitBtn);
        });

        // تابع مشترک برای ارسال درخواست AJAX
        function handleRequest(url, formData, btn) {
            btn.disabled = true;
            const originalText = btn.innerText;
            btn.innerText = 'در حال پردازش...';

            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => showResult(data.success, data.message, data.guest_name))
            .catch(error => showResult(false, 'خطا در ارتباط با سرور'))
            .finally(() => {
                btn.disabled = false;
                btn.innerText = originalText;
            });
        }

        // نمایش نتیجه (سبز یا قرمز)
        function showResult(isSuccess, message, guestName) {
            const icon = document.getElementById('resultIcon');
            const title = document.getElementById('resultTitle');
            const msg = document.getElementById('resultMessage');
            let duration = 800; // زمان نمایش پیام موفقیت (0.8 ثانیه)

            resultBox.classList.remove('success', 'error');

            if (isSuccess) {
                resultBox.classList.add('success');
                icon.className = 'bi bi-check-circle-fill result-icon';
                title.innerText = guestName || 'خوش آمدید';
                msg.innerText = '✅ ورود ثبت شد.';
                audioSuccess.play().catch(e => {});
            } else {
                resultBox.classList.add('error');
                icon.className = 'bi bi-x-octagon-fill result-icon';
                title.innerText = 'خطا';
                msg.innerText = message;
                audioError.play().catch(e => {});
                duration = 2500; // زمان بیشتر برای خطا
            }

            resultBox.classList.add('show-result');

            setTimeout(() => {
                resultBox.classList.remove('show-result');
                phoneInput.value = '';
                phoneInput.focus();
                
                // اگر فرم ثبت نام بود، آن را هم خالی کن
                registerForm.reset();
            }, duration);
        }
    });
</script>

</body>
</html>