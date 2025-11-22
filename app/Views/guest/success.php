<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>خوش آمدید</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        body { font-family: 'Vazirmatn', sans-serif; background-color: #198754; display: flex; align-items: center; justify-content: center; height: 100vh; color: white; text-align: center; }
        .success-card { background: white; color: #333; padding: 40px; border-radius: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); max-width: 90%; width: 400px; animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .icon-circle { width: 80px; height: 80px; background: #d1e7dd; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
        .check-icon { font-size: 40px; color: #198754; }
        @keyframes popIn { from { transform: scale(0.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    </style>
</head>
<body>

<div class="success-card">
    <div class="icon-circle">
        <span class="check-icon">✔</span>
    </div>
    <h2 class="fw-bold text-success mb-3">خوش آمدید!</h2>
    <p class="fs-5 mb-4"><?= htmlspecialchars($guestName) ?> عزیز<br>حضور شما با موفقیت ثبت شد.</p>
    
    <a href="<?= BASE_URL ?>/" class="btn btn-outline-success w-100">بازگشت به صفحه ورود</a>
</div>

</body>
</html>