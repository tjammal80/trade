<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
use HubTrend\Services\ReportRepository;
$repo = new ReportRepository();
$date = trim((string)($_GET['date'] ?? date('Y-m-d')));
$template = $repo->template($date);
$json = json_encode($template, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

if (isset($_GET['download'])) {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . ($template['slug'] ?? 'report-template') . '.json"');
    echo $json;
    exit;
}

$pageTitle = SITE_NAME . ' | قالب JSON';
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero compact">
  <div class="container narrow">
    <span class="eyebrow">Admin</span>
    <h1>قالب JSON جاهز</h1>
    <p class="lead">قالب صالح البنية يمكنك تنزيله كما هو، ثم تعبئته ورفعه إلى storage/reports أو استيراده من لوحة الإدارة.</p>
  </div>
</section>
<section class="section">
  <div class="container narrow">
    <form class="archive-toolbar card" method="get">
      <div class="toolbar-group toolbar-search">
        <input class="input compact-input" type="date" name="date" value="<?= e($date) ?>" />
        <button class="btn btn-outline btn-sm" type="submit">تحديث القالب</button>
      </div>
      <div class="toolbar-group">
        <a class="btn btn-primary btn-sm" href="<?= e(current_url_with(['download' => 1])) ?>">تنزيل JSON</a>
        <a class="btn btn-outline btn-sm" href="/admin/report-import.php">استيراد JSON</a>
      </div>
    </form>
    <textarea class="textarea large-textarea" readonly><?= e($json) ?></textarea>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
