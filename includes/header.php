<?php app_bootstrap(); ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($pageTitle ?? SITE_NAME) ?></title>
  <meta name="description" content="<?= e($pageDescription ?? SITE_DESCRIPTION) ?>" />
  <link rel="stylesheet" href="<?= asset_url('assets/css/styles.css') ?>" />
</head>
<body>
<header class="site-header">
  <div class="container nav-wrap">
    <a class="brand" href="<?= url('index.php') ?>">
      <span class="brand-mark">HT</span>
      <span class="brand-text">
        <strong><?= e(SITE_NAME) ?></strong>
        <small><?= e(SITE_TAGLINE) ?></small>
      </span>
    </a>
    <button class="menu-toggle" aria-label="فتح القائمة">☰</button>
    <nav class="main-nav">
      <a href="<?= url('index.php') ?>">الرئيسية</a>
      <a href="<?= url('report.php') ?>">تقرير اليوم</a>
      <a href="<?= url('reports.php') ?>">الأرشيف</a>
      <a href="<?= url('pricing.php') ?>">التسعير</a>
      <?php if (is_admin()): ?><a href="<?= url('admin/index.php') ?>" class="nav-secondary">الإدارة</a><?php endif; ?>
      <?php if (is_member()): ?>
        <a href="<?= url('account.php') ?>" class="nav-secondary">الحساب</a>
        <a href="<?= url('logout.php') ?>" class="btn btn-sm btn-outline">خروج</a>
      <?php else: ?>
        <a href="<?= url('login.php') ?>" class="nav-secondary">دخول</a>
        <a href="<?= url('subscribe.php') ?>" class="btn btn-sm btn-primary">اشترك الآن</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main>
