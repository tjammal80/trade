<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = SITE_NAME . ' | التسعير';
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact">
  <div class="container narrow">
    <span class="eyebrow">التسعير</span>
    <h1>خطتان فقط في النسخة الثانية</h1>
    <p class="lead">الهدف الآن ليس تعقيد التسعير، بل إثبات أن المستخدم يفهم الفرق بين المجاني والنسخة الكاملة.</p>
  </div>
</section>
<section class="section">
  <div class="container pricing-grid">
    <article class="card pricing-card">
      <div class="plan-tag">مجاني</div>
      <h3>0$</h3>
      <p>ملخصات + معاينة التقارير + وصول محدود.</p>
      <ul class="check-list">
        <li>ملخص تنفيذي</li>
        <li>بداية جدول التشخيص</li>
        <li>معاينة الأرشيف</li>
      </ul>
      <a href="<?= url('signup.php') ?>" class="btn btn-outline">ابدأ مجانًا</a>
    </article>
    <article class="card pricing-card featured">
      <div class="plan-tag">Premium</div>
      <h3>19$ <small>/ شهريًا</small></h3>
      <p>الوصول الكامل للتقارير + الأرشيف + المستويات الفنية + المحفزات.</p>
      <ul class="check-list">
        <li>التقرير الكامل</li>
        <li>الأرشيف الكامل</li>
        <li>تفعيل الـ Paywall</li>
        <li>جاهز للربط لاحقًا بـ Stripe</li>
      </ul>
      <a href="<?= url('subscribe.php') ?>" class="btn btn-primary">جهّز الاشتراك</a>
    </article>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
