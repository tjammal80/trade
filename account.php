<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (!is_member()) {
    header('Location: /login.php');
    exit;
}
$pageTitle = SITE_NAME . ' | الحساب';
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact">
  <div class="container narrow">
    <span class="eyebrow">الحساب</span>
    <h1>بيانات العضوية</h1>
    <p class="lead">هذه الصفحة جاهزة لربطها لاحقًا بحالة الدفع والاشتراك الفعلية.</p>
  </div>
</section>
<section class="section">
  <div class="container account-grid">
    <div class="card">
      <h3>الحالة الحالية</h3>
      <div class="meta-list">
        <div><span>البريد</span><strong><?= e($_SESSION['member_email'] ?? '') ?></strong></div>
        <div><span>الخطة</span><strong><?= e(member_plan()) ?></strong></div>
        <div><span>الوصول</span><strong>Premium Demo</strong></div>
      </div>
    </div>
    <div class="card">
      <h3>الخطوة التالية</h3>
      <p>عند ربط Stripe لاحقًا، ستتحول هذه الصفحة إلى مركز إدارة الخطة وتاريخ التجديد والتنبيهات.</p>
      <a href="<?= url('logout.php') ?>" class="btn btn-outline">خروج</a>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
