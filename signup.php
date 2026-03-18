<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = SITE_NAME . ' | إنشاء حساب';
include __DIR__ . '/includes/header.php';
?>
<section class="auth-shell">
  <div class="container auth-grid">
    <div>
      <span class="eyebrow">إنشاء حساب</span>
      <h1>واجهة التسجيل جاهزة للربط</h1>
      <p class="lead">في هذه النسخة، التسجيل ليس مفعلًا بعد. المسار التالي هو ربط التسجيل والدفع وقاعدة المستخدمين.</p>
    </div>
    <div class="card auth-card">
      <form class="stack-form">
        <input class="input" type="text" placeholder="الاسم الكامل" disabled />
        <input class="input" type="email" placeholder="البريد الإلكتروني" disabled />
        <input class="input" type="password" placeholder="كلمة المرور" disabled />
        <button class="btn btn-primary btn-block" type="button" disabled>سيُفعّل لاحقًا</button>
      </form>
      <p class="tiny-note">ابقِ هذه الصفحة كواجهة جاهزة إلى أن نربط Stripe/Auth.</p>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
