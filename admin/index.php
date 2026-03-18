<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
use HubTrend\Services\ReportRepository;
use HubTrend\Services\MarketDataService;
$repo = new ReportRepository();
$reports = $repo->all(false);
$invalid = $repo->invalidReports();
$stats = $repo->stats();
$market = (new MarketDataService())->getSnapshot();
$pageTitle = SITE_NAME . ' | لوحة الإدارة';
$flash = admin_flash();
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero compact">
  <div class="container narrow">
    <span class="eyebrow">Admin</span>
    <h1>لوحة إدارة التقارير</h1>
    <p class="lead">v3.3.2 تركز على تسهيل النشر اليومي: رفع JSON بسرعة، رسالة إتمام واضحة، ثم انتقال مباشر إلى الخطوة التالية.</p>
  </div>
</section>
<section class="section">
  <div class="container">
    <?php if ($flash): ?><div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
    <div class="admin-topbar">
      <div class="hero-actions compact-actions">
        <a href="/admin/report-edit.php" class="btn btn-primary">تقرير جديد</a>
        <a href="/admin/report-import.php" class="btn btn-outline">رفع تقرير JSON</a>
        <a href="/admin/report-template.php" class="btn btn-outline">قالب JSON</a>
      </div>
      <a href="/logout.php" class="btn btn-outline">خروج</a>
    </div>
    <div class="feature-grid admin-feature-grid">
      <article class="card admin-stat"><small>إجمالي الملفات</small><strong><?= (int)$stats['total'] ?></strong></article>
      <article class="card admin-stat"><small>منشور</small><strong><?= (int)$stats['published'] ?></strong></article>
      <article class="card admin-stat"><small>مسودات</small><strong><?= (int)$stats['draft'] ?></strong></article>
      <article class="card admin-stat"><small>نسخ احتياطية</small><strong><?= (int)$stats['backups'] ?></strong></article>
    </div>

    <?php if (!empty($invalid)): ?>
      <article class="card admin-panel warning-panel">
        <div class="section-title-row">
          <h2>ملفات تحتاج إلى إصلاح</h2>
          <span class="status-tag premium">تحقق مطلوب</span>
        </div>
        <div class="stack-list">
          <?php foreach ($invalid as $item): ?>
            <div class="issue-item">
              <strong><?= e($item['file'] ?? 'unknown') ?></strong>
              <ul class="clean-list issue-list">
                <?php foreach (($item['errors'] ?? []) as $error): ?>
                  <li><?= e($error) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endforeach; ?>
        </div>
      </article>
    <?php endif; ?>

    <article class="card admin-panel">
      <div class="section-title-row">
        <h2>التقارير</h2>
        <small class="muted-inline">الأحدث أولًا</small>
      </div>
      <div class="table-wrap">
        <table class="data-table">
          <thead><tr><th>العنوان</th><th>التاريخ</th><th>الوصول</th><th>الحالة</th><th>إجراءات</th></tr></thead>
          <tbody>
          <?php foreach ($reports as $report): ?>
            <tr>
              <td><?= e($report['title'] ?? '') ?></td>
              <td><?= e(format_ar_date($report['report_date'] ?? '')) ?></td>
              <td><?= e($report['access'] ?? 'free') ?></td>
              <td><span class="status-tag <?= ($report['status'] ?? 'draft') === 'published' ? 'free' : 'premium' ?>"><?= e($report['status'] ?? 'draft') ?></span></td>
              <td>
                <a href="/admin/report-edit.php?slug=<?= urlencode($report['slug']) ?>">تعديل</a>
                |
                <a href="/admin/report-history.php?slug=<?= urlencode($report['slug']) ?>">نسخ</a>
                |
                <a href="/report.php?slug=<?= urlencode($report['slug']) ?>" target="_blank">عرض</a>
                |
                <a href="/admin/report-delete.php?slug=<?= urlencode($report['slug']) ?>" onclick="return confirm('حذف التقرير؟');">حذف</a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="tiny-note">حالة الأسعار الحالية: <?= e($market['status']) ?> — افحص أيضًا <a class="text-link" href="/api/health.php" target="_blank">/api/health.php</a>.</div>
    </article>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
