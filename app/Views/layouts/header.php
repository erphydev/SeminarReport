<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سیستم مدیریت سمینار</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        /* --- فیکس کردن فوتر برای کل سایت --- */
        html, body {
            height: 100%;
        }
        
        body { 
            font-family: 'Vazirmatn', sans-serif; 
            background-color: #f4f6f9;
            /* این سه خط جادو می‌کنند: */
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* این کلاس باعث می‌شود محتوا کش بیاید و فوتر را هل دهد پایین */
        .main-wrapper {
            flex: 1;
        }
        /* ---------------------------------- */

        .navbar-custom {
            background: linear-gradient(135deg, #212529 0%, #343a40 100%);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .navbar-brand { font-weight: 800; font-size: 1.3rem; }
        .nav-link:hover { color: #ffc107 !important; transform: translateY(-1px); transition: 0.2s; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top mb-4">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>/admin">
            <i class="bi bi-mortarboard-fill text-warning"></i> مدیریت سمینار
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav mb-2 mb-lg-0 me-3">
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin">داشبورد</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/seminar/create">افزودن سمینار</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2 ms-auto mt-2 mt-lg-0">
                <a href="<?= BASE_URL ?>/" class="btn btn-outline-light btn-sm">ورود مهمان</a>
                <a href="<?= BASE_URL ?>/logout" class="btn btn-danger btn-sm">خروج</a>
            </div>
        </div>
    </div>
</nav>

<!-- شروع کانتینر اصلی با کلاس اختصاصی برای هل دادن فوتر -->
<div class="container main-wrapper pb-5">