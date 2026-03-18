<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = SITE_NAME . ' | الاشتراك';
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact">
  <div class="container narrow">
    <span class="eyebrow">الاشتراك</span>
    <h1>صفحة تهيئة الدفع</h1>
    <p class="lead">هذه الصفحة مخصصة للخطوة التالية: ربط Stripe Checkout أو بوابة دفع مناسبة لخطة العضوية.</p>
  </div>
</section>
<section class="section">
  <div class="container subscribe-grid">
    <div class="card">
      <h3>ما الذي يعمل الآن؟</h3>
      <ul class="clean-list">
        <li>واجهة تسعير</li>
        <li>Paywall بسيط</li>
        <li>دخول عضو تجريبي</li>
        <li>أرشيف وتقرير ديناميكي</li>
      </ul>
    </div>
    <div class="card">
      <h3>ما الذي سيُربط لاحقًا؟</h3>
      <ul class="clean-list">
        <li>Stripe Checkout</li>
        <li>Webhook لتحديث حالة الاشتراك</li>
        <li>ترقية الحساب تلقائيًا بعد الدفع</li>
      </ul>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
