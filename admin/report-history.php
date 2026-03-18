<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
use HubTrend\Services\ReportRepository;
$repo = new ReportRepository();
$slug = trim((string)($_GET['slug'] ?? ''));
$report = $slug ? $repo->findBySlug($slug) : null;
if (!$slug || !$report) {
    admin_flash('التقرير المطلوب غير موجود.', 'error');
    header('Location: /admin/index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $backup = basename((string)($_POST['backup'] ?? ''));
    try {
        if ($backup === '') throw new RuntimeException('لم يتم تحديد نسخة احتياطية.');
        $repo->restoreBackup($slug, $backup);
        admin_flash('تمت استعادة النسخة الاحتياطية بنجاح.');
        header('Location: /admin/report-edit.php?slug=' . urlencode($slug));
        exit;
    } catch (Throwable $e) {
        admin_flash($e->getMessage(), 'error');
        header('Location: /admin/report-history.php?slug=' . urlencode($slug));
        exit;
    }
}
$history = $repo->backupHistory($slug);
$pageTitle = SITE_NAME . ' | النسخ الاحتياطية';
$flash = admin_flash();
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero compact">
  <div class="container narrow">
    <span class="eyebrow">Admin</span>
    <h1>النسخ الاحتياطية للتقرير</h1>
    <p class="lead">Slug: <strong><?= e($slug) ?></strong> — أي حفظ جديد فوق ملف موجود سينشئ backup تلقائيًا قبل الاستبدال.</p>
  </div>
</section>
<section class="section">
  <div class="container narrow">
    <?php if ($flash): ?><div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
    <div class="admin-topbar">
      <a class="btn btn-outline" href="/admin/report-edit.php?slug=<?= urlencode($slug) ?>">عودة إلى التحرير</a>
      <a class="btn btn-outline" href="/admin/index.php">لوحة الإدارة</a>
    </div>
    <article class="card admin-panel">
      <?php if (empty($history)): ?>
        <p>لا توجد نسخ احتياطية حتى الآن لهذا التقرير.</p>
      <?php else: ?>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>الملف</th><th>آخر تعديل</th><th>الحجم</th><th>إجراء</th></tr></thead>
            <tbody>
            <?php foreach ($history as $item): ?>
              <tr>
                <td><?= e($item['name']) ?></td>
                <td><?= e(format_ar_date(substr($item['modified_at'], 0, 10))) ?> <?= e(substr($item['modified_at'], 11, 8)) ?></td>
                <td><?= e(number_format(($item['size'] ?? 0) / 1024, 1)) ?> KB</td>
                <td>
                  <form method="post" onsubmit="return confirm('استعادة هذه النسخة ستستبدل الملف الحالي بعد أخذ backup جديد. هل أنت متأكد؟');">
                    <input type="hidden" name="backup" value="<?= e($item['name']) ?>" />
                    <button class="btn btn-outline btn-sm" type="submit">استعادة</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </article>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
