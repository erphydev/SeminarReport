<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سیستم سمینار</title>
    <!-- بوت‌استرپ RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <!-- فونت وزیر -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        body { font-family: 'Vazirmatn', sans-serif; background-color: #f4f6f9; }
        .navbar { box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .card { border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">

        <a class="navbar-brand" href="<?= BASE_URL ?>/admin">مدیریت سمینار</a>

        <a class="nav-link" href="<?= BASE_URL ?>/admin">داشبورد</a>

        <a class="nav-link" href="<?= BASE_URL ?>/admin/seminar/create">افزودن سمینار</a>

        <a href="<?= BASE_URL ?>/" class="btn btn-warning">ورود مهمان</a>
        
        <a href="<?= BASE_URL ?>/logout" class="btn btn-danger btn-sm fw-bold">خروج 🔒</a>

    </div>
</nav>

<div class="container">
