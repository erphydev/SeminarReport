<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به سمینار</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Vazirmatn', sans-serif; 
            background: linear-gradient(135deg, #cb2d3e, #ef473a); 
            min-height: 100vh; 
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card { 
            width: 100%; 
            max-width: 420px; 
            background: #ffffff;
            border: none; 
            border-radius: 25px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            overflow: hidden;
            animation: fadeInUp 0.6s ease-out; 
        }

        .card-header-custom {
            background-color: white;
            padding: 30px 20px 10px;
            text-align: center;
        }
        
        .seminar-title {
            color: #cb2d3e; 
            font-weight: 900;
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .seminar-subtitle {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .form-control-custom { 
            height: 60px; 
            font-size: 1.5rem; 
            text-align: center; 
            letter-spacing: 4px; 
            border: 2px solid #eee;
            border-radius: 15px; 
            background-color: #fafafa;
            transition: all 0.3s ease;
            font-weight: bold;
            color: #333;
        }

        .form-control-custom:focus {
            border-color: #cb2d3e; 
            box-shadow: 0 0 0 0.25rem rgba(203, 45, 62, 0.15);
            background-color: #fff;
            outline: none;
        }

        .btn-submit { 
            height: 55px; 
            font-size: 1.2rem; 
            border-radius: 15px; 
            background: linear-gradient(90deg, #cb2d3e, #ef473a);
            border: none;
            font-weight: 800;
            transition: transform 0.2s;
            box-shadow: 0 10px 20px rgba(203, 45, 62, 0.3);
        }

        .btn-submit:hover {
            background: linear-gradient(90deg, #b01c2d, #d63429);
            transform: translateY(-2px);
        }
        
        .btn-submit:active {
            transform: translateY(1px);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .icon-wrapper {
            width: 80px;
            height: 80px;
            background: #fff0f0;
            color: #cb2d3e;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            margin: 0 auto 20px;
        }
    </style>
</head>
<body>

<div class="login-card">
    
    <div class="card-header-custom">
        <div class="icon-wrapper">
            👋
        </div>
        <h1 class="seminar-title">خوش آمدید</h1>
        <p class="seminar-subtitle">
            <?= htmlspecialchars($activeSeminar['title'] ?? 'سیستم حضور و غیاب') ?>
        </p>
    </div>

    <div class="card-body p-4 pt-2">
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger text-center rounded-4 shadow-sm mb-4">
                ⚠️ <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/checkin/verify" method="POST">
            <input type="hidden" name="seminar_id" value="<?= $activeSeminar['id'] ?>">
            
            <div class="mb-4 text-center">
                <label for="phone" class="form-label text-muted small mb-3">شماره موبایل خود را وارد کنید</label>
                <input type="tel" class="form-control form-control-custom" 
                       id="phone" name="phone" 
                       placeholder="0912..." 
                       maxlength="11"
                       autocomplete="off"
                       required autofocus>
            </div>

            <button type="submit" class="btn btn-primary w-100 btn-submit text-white">
                ثبت حضور
            </button>
        </form>
    </div>
</div>

</body>
</html>