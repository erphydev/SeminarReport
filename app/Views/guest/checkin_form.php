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
        /* تعریف متغیرهای رنگی برای مدیریت آسان */
        :root {
            --bg-color: #f3f4f6; /* خاکستری بسیار روشن */
            --card-bg-color: #ffffff;
            --text-color: #1f2937;
            --text-muted-color: #6b7280;
            --accent-color: #4f46e5; /* آبی-بنفش مدرن */
            --accent-color-dark: #4338ca;
            --success-color: #10b981; /* سبز */
            --error-color: #ef4444;   /* قرمز */
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

        /* کارت ورود با طراحی تمیز و سایه مدرن */
        .login-card {
            width: 100%;
            max-width: 420px;
            background-color: var(--card-bg-color);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            /* سایه نرم و چندلایه برای ایجاد عمق */
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

        /* آیکون بالای کارت */
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

        .login-card .text-muted {
            color: var(--text-muted-color) !important;
        }

        /* فیلد ورودی مدرن */
        .form-control-lg-custom {
            font-size: 1.8rem;
            text-align: center;
            letter-spacing: 5px;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 15px;
            background-color: #f9fafb; /* پس‌زمینه کمی متفاوت از بدنه */
            font-weight: 700;
            color: var(--text-color);
            transition: all 0.2s ease-in-out;
        }
        .form-control-lg-custom::placeholder {
            color: #9ca3af;
        }
        .form-control-lg-custom:focus {
            background-color: white;
            border-color: var(--accent-color);
            /* افکت حلقه دور فیلد در حالت فوکس */
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
            outline: none;
        }

        /* دکمه اصلی */
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
        .btn-checkin:active {
            transform: translateY(0);
            box-shadow: none;
        }
        .btn-checkin:disabled {
            background-color: #9ca3af;
            box-shadow: none;
            cursor: not-allowed;
        }

        /* لایه نمایش نتیجه */
        .result-overlay {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(5px);
            opacity: 0; visibility: hidden;
            transition: all 0.3s ease;
            transform: scale(0.95);
            z-index: 100;
        }
        .show-result {
            opacity: 1;
            visibility: visible;
            transform: scale(1);
        }
        /* در اینجا فقط رنگ آیکون و متن تغییر می‌کند */
        .result-overlay.success .result-icon, .result-overlay.success h2 {
            color: var(--success-color);
        }
        .result-overlay.error .result-icon, .result-overlay.error h2 {
            color: var(--error-color);
        }

        .result-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="brand-icon"><i class="bi bi-person-check"></i></div>
    <h2 class="fw-bold mb-1">ورود به رویداد</h2>
    <p class="text-muted small mb-4"><?= htmlspecialchars($activeSeminar['title'] ?? 'سیستم حضور و غیاب') ?></p>

    <form id="checkinForm" autocomplete="off">
        <input type="hidden" name="seminar_id" value="<?= $activeSeminar['id'] ?? 0 ?>">
        <div class="mb-3">
            <input type="tel" class="form-control form-control-lg-custom" id="phone" name="phone" placeholder="09123456789" maxlength="11" autofocus required>
        </div>
        <button type="submit" class="btn btn-checkin" id="submitBtn">ثبت ورود</button>
    </form>

    <div id="resultBox" class="result-overlay">
        <i id="resultIcon" class="bi"></i>
        <h2 class="fw-bold" id="resultTitle"></h2>
        <p class="fs-5 px-3" id="resultMessage"></p>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById('checkinForm');
        const phoneInput = document.getElementById('phone');
        const resultBox = document.getElementById('resultBox');
        const submitBtn = document.getElementById('submitBtn');
        const audioSuccess = new Audio('https://actions.google.com/sounds/v1/cartoon/pop.ogg');
        const audioError = new Audio('https://actions.google.com/sounds/v1/cartoon/clang_and_wobble.ogg');

        phoneInput.focus();
        document.body.addEventListener('click', (e) => {
            if (e.target !== phoneInput && !submitBtn.contains(e.target)) {
                phoneInput.focus();
            }
        });

        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitBtn.disabled = true;
            submitBtn.innerText = 'در حال بررسی...';

            // شبیه‌سازی فراخوانی سرور برای تست
            // در استفاده واقعی این بخش را حذف و fetch را از کامنت خارج کنید
            /*
            setTimeout(() => {
                const isSuccess = Math.random() > 0.4; // شانس موفقیت
                const data = isSuccess ?
                    { success: true, message: 'موفق', guest_name: 'علی رضایی' } :
                    { success: false, message: 'شماره موبایل یافت نشد.' };
                showResult(data.success, data.message, data.guest_name);
                submitBtn.disabled = false;
                submitBtn.innerText = 'ثبت ورود';
            }, 1000);
            */
            
            // کد اصلی برای ارتباط با سرور
            fetch('<?= BASE_URL ?>/checkin/verify', {
                method: 'POST',
                body: new FormData(form)
            })
            .then(response => response.json())
            .then(data => showResult(data.success, data.message, data.guest_name))
            .catch(error => showResult(false, 'خطا در ارتباط با سرور'))
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerText = 'ثبت ورود';
            });
        });

        function showResult(isSuccess, message, guestName) {
            const icon = document.getElementById('resultIcon');
            const title = document.getElementById('resultTitle');
            const msg = document.getElementById('resultMessage');
            let duration = 1500;

            resultBox.classList.remove('success', 'error');

            if (isSuccess) {
                resultBox.classList.add('success');
                icon.className = 'bi bi-check-circle-fill result-icon';
                title.innerText = guestName || 'خوش آمدید';
                msg.innerText = 'ورود شما با موفقیت ثبت شد.';
                audioSuccess.play().catch(e => {});
            } else {
                resultBox.classList.add('error');
                icon.className = 'bi bi-x-octagon-fill result-icon';
                title.innerText = 'خطا';
                msg.innerText = message;
                audioError.play().catch(e => {});
                duration = 2500;
            }

            resultBox.classList.add('show-result');

            setTimeout(() => {
                resultBox.classList.remove('show-result');
                phoneInput.value = '';
                phoneInput.focus();
            }, duration);
        }
    });
</script>

</body>
</html>