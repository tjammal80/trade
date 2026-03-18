<?php
require_once __DIR__ . '/includes/bootstrap.php';
use HubTrend\Services\ReportRepository;
$repo = new ReportRepository();
$reports = $repo->all(true);
$type = $_GET['type'] ?? 'all';
$q = trim((string)($_GET['q'] ?? ''));

$reports = array_values(array_filter($reports, function (array $report) use ($type, $q) {
    if ($type === 'free' && ($report['access'] ?? 'free') !== 'free') return false;
    if ($type === 'premium' && ($report['access'] ?? 'free') !== 'premium') return false;
    if ($q !== '') {
        $haystackSource = implode(' ', [
            $report['title'] ?? '',
            $report['executive_summary'] ?? '',
            $report['report_date'] ?? '',
            $report['slug'] ?? '',
        ]);
        $haystack = function_exists('mb_strtolower') ? mb_strtolower($haystackSource) : strtolower($haystackSource);
        $needle = function_exists('mb_strtolower') ? mb_strtolower($q) : strtolower($q);
        if (!str_contains($haystack, $needle)) return false;
    }
    return true;
}));

$pageTitle = SITE_NAME . ' | الأرشيف';
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact">
  <div class="container narrow">
    <span class="eyebrow">الأرشيف</span>
    <h1>أرشيف التقارير</h1>
    <p class="lead">فرز سريع بين المحتوى المجاني والمدفوع، مع بحث بسيط بالعنوان أو التاريخ أو الـ slug.</p>
  </div>
</section>
<section class="section">
  <div class="container">
    <form class="archive-toolbar card" method="get">
      <div class="toolbar-group chips-links">
        <a class="chip <?= $type === 'all' ? 'active' : '' ?>" href="<?= e(current_url_with(['type' => 'all', 'q' => $q ?: null])) ?>">الكل</a>
        <a class="chip <?= $type === 'free' ? 'active' : '' ?>" href="<?= e(current_url_with(['type' => 'free', 'q' => $q ?: null])) ?>">مجاني</a>
        <a class="chip <?= $type === 'premium' ? 'active' : '' ?>" href="<?= e(current_url_with(['type' => 'premium', 'q' => $q ?: null])) ?>">للأعضاء</a>
      </div>
      <div class="toolbar-group toolbar-search">
        <input class="input compact-input" type="search" name="q" value="<?= e($q) ?>" placeholder="ابحث بالعنوان أو التاريخ أو slug" />
        <input type="hidden" name="type" value="<?= e($type) ?>" />
        <button class="btn btn-outline btn-sm" type="submit">بحث</button>
      </div>
      <div class="toolbar-group"><span><?= count($reports) ?> تقريرًا</span></div>
    </form>

    <?php if (empty($reports)): ?>
      <div class="card report-section">
        <h3>لا توجد نتائج</h3>
        <p>جرّب تغيير الفلتر أو عبارة البحث.</p>
      </div>
    <?php else: ?>
      <div class="archive-grid">
        <?php foreach ($reports as $report): ?>
          <article class="card archive-card <?= e(report_visibility_class($report['access'] ?? 'free')) ?>">
            <div class="archive-meta">
              <span><?= e(format_ar_date($report['report_date'] ?? '')) ?></span>
              <span class="status-tag <?= e(report_status_badge($report['access'] ?? 'free')) ?>"><?= e(report_access_label($report['access'] ?? 'free')) ?></span>
            </div>
            <h3><?= e($report['title'] ?? '') ?></h3>
            <p><?= e(report_excerpt($report['executive_summary'] ?? '', 220)) ?></p>
            <div class="archive-actions">
              <a href="<?= url('report.php?slug=' . urlencode($report['slug'])) ?>" class="btn btn-outline btn-sm">فتح التقرير</a>
              <small class="muted-inline"><?= e($report['slug'] ?? '') ?></small>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
