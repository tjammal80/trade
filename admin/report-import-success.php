<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
use HubTrend\Services\ReportRepository;
$repo = new ReportRepository();
$slug = trim((string)($_GET['slug'] ?? ''));
$next = trim((string)($_GET['next'] ?? 'edit'));
$report = $slug !== '' ? $repo->findBySlug($slug) : null;
if (!$report) {
    admin_flash('تعذر العثور على التقرير بعد الاستيراد.', 'error');
    header('Location: /admin/report-import.php');
    exit;
}
$target = $next === 'preview'
    ? '/report.php?slug=' . urlencode($slug)
    : '/admin/report-edit.php?slug=' . urlencode($slug);
$countdown = 3;
$pageTitle = SITE_NAME . ' | تم استيراد التقرير';
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero compact">
  <div class="container narrow">
    <span class="eyebrow">Admin</span>
    <h1>تم استيراد التقرير بنجاح</h1>
    <p class="lead">تم حفظ التقرير، وسيتم تحويلك تلقائيًا خلال <strong id="countdown"><?= $countdown ?></strong> ثوانٍ.</p>
  </div>
</section>
<section class="section">
  <div class="container narrow">
    <article class="card admin-panel success-panel">
      <div class="success-badge">✓</div>
      <h2><?= e($report['title'] ?? 'تقرير جديد') ?></h2>
      <div class="meta-list import-summary-grid">
        <div><span>Slug</span><strong><?= e($report['slug'] ?? '') ?></strong></div>
        <div><span>التاريخ</span><strong><?= e(format_ar_date($report['report_date'] ?? '')) ?></strong></div>
        <div><span>الحالة</span><strong><?= e(($report['status'] ?? 'draft') === 'published' ? 'منشور' : 'مسودة') ?></strong></div>
        <div><span>الوصول</span><strong><?= e(report_access_label($report['access'] ?? 'free')) ?></strong></div>
      </div>
      <div class="hero-actions compact-actions wrap-actions">
        <a class="btn btn-primary" href="<?= e($target) ?>"><?= $next === 'preview' ? 'الانتقال إلى المعاينة الآن' : 'الانتقال إلى صفحة التعديل الآن' ?></a>
        <a class="btn btn-outline" href="/admin/report-import.php">رفع تقرير آخر</a>
        <a class="btn btn-outline" href="/admin/index.php">لوحة الإدارة</a>
      </div>
    </article>
  </div>
</section>
<script>
(function(){
  let remaining = <?= $countdown ?>;
  const node = document.getElementById('countdown');
  const target = <?= json_encode($target, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
  const timer = setInterval(function(){
    remaining -= 1;
    if (node) node.textContent = remaining > 0 ? String(remaining) : '0';
    if (remaining <= 0) {
      clearInterval(timer);
      window.location.href = target;
    }
  }, 1000);
})();
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
