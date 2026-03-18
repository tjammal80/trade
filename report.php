<?php
require_once __DIR__ . '/includes/bootstrap.php';
use HubTrend\Services\ReportRepository;
$repo = new ReportRepository();
$slug = trim((string)($_GET['slug'] ?? ''));
$previewToken = trim((string)($_GET['preview_token'] ?? ''));
$isPreview = false;
if ($previewToken !== '' && is_admin()) {
    $preview = preview_get($previewToken);
    if (is_array($preview)) {
        $report = $preview;
        $isPreview = true;
    }
}
if (empty($report)) {
    if ($slug !== '') {
        $report = is_admin() ? $repo->findBySlug($slug) : $repo->findPublishedBySlug($slug);
    } else {
        $report = $repo->latestPublished();
    }
}
if (!$report) {
    http_response_code(404);
    $pageTitle = 'التقرير غير موجود';
    include __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container"><div class="card report-section"><h1>التقرير غير موجود</h1><p>أنشئ تقريرًا من لوحة الإدارة أولًا أو تأكد من صحة الـ slug.</p></div></div></section>';
    include __DIR__ . '/includes/footer.php';
    exit;
}
if (!$isPreview && !is_admin() && ($report['status'] ?? 'draft') !== 'published') {
    http_response_code(404);
    $pageTitle = 'التقرير غير متاح';
    include __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container"><div class="card report-section"><h1>التقرير غير متاح</h1><p>هذا التقرير ما يزال في وضع المسودة.</p></div></div></section>';
    include __DIR__ . '/includes/footer.php';
    exit;
}
$pageTitle = ($report['title'] ?? 'تقرير') . ' | ' . SITE_NAME;
$diagnosticRows = $report['diagnostic_table'] ?? [];
$triggers = $report['trigger_map'] ?? [];
$sources = $report['sources'] ?? [];
$technical = $report['technical_levels'] ?? [];
$canViewFull = $isPreview || can_view_report($report);
$partialRows = array_slice($diagnosticRows, 0, 4);
$adjacent = !$isPreview ? $repo->adjacent((string)($report['slug'] ?? '')) : ['prev' => null, 'next' => null];
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact">
  <div class="container narrow">
    <div class="report-head-top">
      <span class="eyebrow"><?= $isPreview ? 'Preview' : 'تقرير اليوم' ?></span>
      <span class="status-tag <?= e(report_status_badge($report['access'] ?? 'free')) ?>"><?= e(report_access_label($report['access'] ?? 'free')) ?></span>
      <?php if (($report['status'] ?? 'draft') === 'draft'): ?><span class="status-tag premium">draft</span><?php endif; ?>
    </div>
    <h1><?= e($report['title']) ?></h1>
    <p class="lead">هذا التقرير متوافق حتى تاريخ <?= e(format_ar_date($report['report_date'] ?? '')) ?></p>
    <div class="report-nav-inline">
      <?php if ($isPreview): ?><span>معاينة داخلية غير منشورة</span><?php endif; ?>
      <?php if (!empty($adjacent['next'])): ?><a href="<?= url('report.php?slug=' . urlencode($adjacent['next']['slug'])) ?>">← أحدث</a><?php endif; ?>
      <?php if (!empty($adjacent['prev'])): ?><a href="<?= url('report.php?slug=' . urlencode($adjacent['prev']['slug'])) ?>">أقدم →</a><?php endif; ?>
    </div>
  </div>
</section>
<section class="section">
  <div class="container report-layout">
    <div class="report-content">
      <article class="card report-section">
        <h2>الملخص التنفيذي</h2>
        <p><?= nl2br(e($report['executive_summary'] ?? '')) ?></p>
      </article>

      <article class="card report-section">
        <div class="section-title-row">
          <h2>جدول تشخيص السوق</h2>
          <?php if (!$canViewFull): ?><span class="mini-lock">معاينة مجانية</span><?php endif; ?>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
            <tr>
              <th>المؤشر</th>
              <th>القراءة الحالية</th>
              <th>الإشارة</th>
              <th>الأثر السعري على الذهب</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach (($canViewFull ? $diagnosticRows : $partialRows) as $row): ?>
              <tr>
                <td><?= e($row['indicator'] ?? '') ?></td>
                <td><?= e($row['current_reading'] ?? '') ?></td>
                <td><span class="pill <?= e(signal_class($row['signal'] ?? 'محايد')) ?>"><?= e($row['signal'] ?? 'محايد') ?></span></td>
                <td><?= e($row['price_impact'] ?? '') ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php if (!$canViewFull): ?>
          <div class="paywall teaser-paywall">
            <h3>المعاينة انتهت هنا</h3>
            <p>الجزء المجاني يعرض الملخص وأول إشارات السوق. النسخة الكاملة تتضمن السيناريوهات، المحفزات، والمستويات الفنية الحاسمة.</p>
            <div class="hero-actions compact-actions">
              <a href="<?= url('subscribe.php') ?>" class="btn btn-primary">فتح التقرير الكامل</a>
              <a href="<?= url('login.php') ?>" class="btn btn-outline">دخول الأعضاء</a>
            </div>
          </div>
        <?php endif; ?>
      </article>

      <article class="card report-section <?= $canViewFull ? '' : 'partial-access' ?>">
        <div class="<?= $canViewFull ? '' : 'locked-blur' ?>">
          <h2>تفسير الحركة الحالية</h2>
          <p><?= nl2br(e($report['current_interpretation'] ?? '')) ?></p>

          <h2>سيناريو الشهر الواحد</h2>
          <p><?= nl2br(e($report['scenario_1m'] ?? '')) ?></p>

          <h2>سيناريو الثلاثة أشهر</h2>
          <p><?= nl2br(e($report['scenario_3m'] ?? '')) ?></p>

          <h2>سيناريو الستة أشهر</h2>
          <p><?= nl2br(e($report['scenario_6m'] ?? '')) ?></p>

          <h2>المحفزات التي يجب مراقبتها</h2>
          <ul class="clean-list">
            <?php foreach ($triggers as $trigger): ?>
              <li><?= e($trigger) ?></li>
            <?php endforeach; ?>
          </ul>

          <h2>المستويات الفنية الحاسمة</h2>
          <div class="levels-grid">
            <div><small>أهم دعم</small><strong><?= e($technical['support'] ?? '-') ?></strong></div>
            <div><small>أهم مقاومة</small><strong><?= e($technical['resistance'] ?? '-') ?></strong></div>
            <div><small>تأكيد الصعود</small><strong><?= e($technical['bull_confirmation'] ?? '-') ?></strong></div>
            <div><small>تأكيد الهبوط</small><strong><?= e($technical['bear_confirmation'] ?? '-') ?></strong></div>
            <div><small>مستوى الإبطال</small><strong><?= e($technical['invalidation'] ?? '-') ?></strong></div>
          </div>

          <h2>الخلاصة</h2>
          <p><?= nl2br(e($report['conclusion'] ?? '')) ?></p>

          <?php if (!empty($sources)): ?>
            <h2>المصادر</h2>
            <ul class="clean-list">
              <?php foreach ($sources as $source): ?>
                <li><?= e($source) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </article>
    </div>

    <aside class="card sticky-card">
      <h3>بيانات التقرير</h3>
      <div class="meta-list">
        <div><span>تاريخ التقرير</span><strong><?= e(format_ar_date($report['report_date'] ?? '')) ?></strong></div>
        <div><span>نوع الوصول</span><strong><?= e(report_access_label($report['access'] ?? 'free')) ?></strong></div>
        <div><span>الحالة</span><strong><?= e($report['status'] ?? '') ?></strong></div>
        <div><span>Slug</span><strong><?= e($report['slug'] ?? '') ?></strong></div>
      </div>
      <?php if (!$canViewFull): ?>
        <div class="paywall side-paywall">
          <h3>افتح التقرير الكامل</h3>
          <p>هذه نسخة تشغيلية لقفل المحتوى. الخطوة التالية لاحقًا هي ربطها باشتراك فعلي.</p>
          <a href="<?= url('subscribe.php') ?>" class="btn btn-primary btn-block">فعّل الوصول الكامل</a>
        </div>
      <?php endif; ?>
      <div class="side-links">
        <?php if ($isPreview): ?><a href="<?= url('admin/report-edit.php?slug=' . urlencode((string)($report['slug'] ?? ''))) ?>">العودة إلى التحرير</a><?php endif; ?>
        <a href="<?= url('reports.php') ?>">العودة إلى الأرشيف</a>
        <?php if (!empty($adjacent['next'])): ?><a href="<?= url('report.php?slug=' . urlencode($adjacent['next']['slug'])) ?>">انتقال إلى تقرير أحدث</a><?php endif; ?>
        <?php if (!empty($adjacent['prev'])): ?><a href="<?= url('report.php?slug=' . urlencode($adjacent['prev']['slug'])) ?>">انتقال إلى تقرير أقدم</a><?php endif; ?>
      </div>
    </aside>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
