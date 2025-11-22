<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود مدیریت</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        body { font-family: 'Vazirmatn', sans-serif; background-color: #212529; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { width: 100%; max-width: 400px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .btn-primary { background-color: #0d6efd; padding: 10px; font-size: 1.1rem; }
    </style>
</head>
<body>

<div class="card p-4">
    <div class="text-center mb-4">
        <h3 class="fw-bold"> ورود به پنل مدیریت</h3>
        <p class="text-muted">لطفاً مشخصات خود را وارد کنید</p>
    </div>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger text-center"><?= $error ?></div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/auth/process" method="POST">
        <div class="mb-3">
            <label class="form-label">نام کاربری</label>
            <input type="text" name="username" class="form-control" required autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label">رمز عبور</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-bold">ورود</button>
    </form>
    <div class="text-center mt-3">
        <a href="<?= BASE_URL ?>/" class="text-decoration-none">بازگشت به صفحه اصلی</a>
    </div>
</div>

</body>
</html>