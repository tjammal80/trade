<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
use HubTrend\Services\ReportRepository;
$repo = new ReportRepository();
$slug = trim((string)($_GET['slug'] ?? ''));
$report = $slug ? $repo->findBySlug($slug) : null;
$report = $report ?: [
    'title' => '',
    'slug' => '',
    'report_date' => date('Y-m-d'),
    'status' => 'draft',
    'access' => 'free',
    'executive_summary' => '',
    'diagnostic_table' => [['indicator'=>'XAU/USD','current_reading'=>'','signal'=>'مختلط','price_impact'=>'']],
    'current_interpretation' => '',
    'scenario_1m' => '',
    'scenario_3m' => '',
    'scenario_6m' => '',
    'trigger_map' => [''],
    'technical_levels' => ['support'=>'','resistance'=>'','bull_confirmation'=>'','bear_confirmation'=>'','invalidation'=>''],
    'conclusion' => '',
    'sources' => ['Reuters'],
    'created_at' => null,
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string)($_POST['form_action'] ?? 'save'));
    $payload = [
        'title' => trim((string)($_POST['title'] ?? '')),
        'slug' => trim((string)($_POST['slug'] ?? '')),
        'report_date' => trim((string)($_POST['report_date'] ?? date('Y-m-d'))),
        'status' => trim((string)($_POST['status'] ?? 'draft')),
        'access' => trim((string)($_POST['access'] ?? 'free')),
        'executive_summary' => trim((string)($_POST['executive_summary'] ?? '')),
        'diagnostic_table' => parse_diagnostic_rows($_POST['diagnostic_table'] ?? ''),
        'current_interpretation' => trim((string)($_POST['current_interpretation'] ?? '')),
        'scenario_1m' => trim((string)($_POST['scenario_1m'] ?? '')),
        'scenario_3m' => trim((string)($_POST['scenario_3m'] ?? '')),
        'scenario_6m' => trim((string)($_POST['scenario_6m'] ?? '')),
        'trigger_map' => parse_lines($_POST['trigger_map'] ?? ''),
        'technical_levels' => [
            'support' => trim((string)($_POST['support'] ?? '')),
            'resistance' => trim((string)($_POST['resistance'] ?? '')),
            'bull_confirmation' => trim((string)($_POST['bull_confirmation'] ?? '')),
            'bear_confirmation' => trim((string)($_POST['bear_confirmation'] ?? '')),
            'invalidation' => trim((string)($_POST['invalidation'] ?? '')),
        ],
        'conclusion' => trim((string)($_POST['conclusion'] ?? '')),
        'sources' => parse_lines($_POST['sources'] ?? ''),
        'created_at' => $report['created_at'] ?? null,
    ];

    if ($action === 'save_draft') {
        $payload['status'] = 'draft';
    } elseif ($action === 'publish') {
        $payload['status'] = 'published';
    }

    $report = array_merge($report, $payload);
    $validationMode = in_array($action, ['publish'], true) ? 'publish' : 'draft';
    $errors = $repo->validate($report, $validationMode);

    if (empty($errors) && $action === 'preview') {
        $token = preview_store($report);
        header('Location: /report.php?preview_token=' . urlencode($token));
        exit;
    }

    if (empty($errors) && in_array($action, ['save', 'save_draft', 'publish'], true)) {
        try {
            $savedSlug = $repo->save($report);
            $message = $action === 'publish' ? 'تم نشر التقرير بنجاح.' : 'تم حفظ التقرير بنجاح.';
            admin_flash($message);
            header('Location: /admin/report-edit.php?slug=' . urlencode($savedSlug));
            exit;
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }
    }
}
$pageTitle = SITE_NAME . ' | ' . ($slug ? 'تعديل تقرير' : 'تقرير جديد');
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero compact">
  <div class="container narrow">
    <span class="eyebrow">Admin</span>
    <h1><?= $slug ? 'تعديل التقرير' : 'تقرير جديد' ?></h1>
    <p class="lead">دورة العمل في v3.3.2 أبسط: ابدأ من رفع JSON، ثم راجع التقرير هنا، ثم انشره عند الجاهزية. عند كل حفظ ينشأ backup تلقائي إن كان الملف موجودًا مسبقًا.</p>
  </div>
</section>
<section class="section">
  <div class="container">
    <?php if (!empty($errors)): ?>
      <div class="flash flash-error">
        <strong>تعذر المتابعة بسبب الأخطاء التالية:</strong>
        <ul class="clean-list issue-list">
          <?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
    <div class="admin-topbar">
      <div class="hero-actions compact-actions">
        <a class="btn btn-outline" href="/admin/report-template.php?date=<?= e($report['report_date']) ?>">توليد قالب JSON</a>
        <a class="btn btn-outline" href="/admin/report-import.php">رفع JSON</a>
        <?php if (!empty($report['slug'])): ?><a class="btn btn-outline" href="/admin/report-history.php?slug=<?= urlencode($report['slug']) ?>">النسخ الاحتياطية</a><?php endif; ?>
      </div>
      <a class="btn btn-outline" href="/admin/index.php">عودة</a>
    </div>
    <form method="post" class="stack-form">
      <div class="flash flash-info">يمكنك الآن حفظ المسودة بحد أدنى من الحقول. التحقق الصارم يعمل فقط عند الضغط على "نشر التقرير".</div>
      <div class="form-row">
        <input class="input" name="title" placeholder="العنوان" value="<?= e($report['title']) ?>" required />
        <input class="input" name="slug" placeholder="slug" value="<?= e($report['slug']) ?>" />
      </div>
      <div class="form-row">
        <input class="input" type="date" name="report_date" value="<?= e($report['report_date']) ?>" required />
        <select name="status"><option value="draft" <?= ($report['status'] ?? '') === 'draft' ? 'selected' : '' ?>>draft</option><option value="published" <?= ($report['status'] ?? '') === 'published' ? 'selected' : '' ?>>published</option></select>
      </div>
      <div class="form-row">
        <select name="access"><option value="free" <?= ($report['access'] ?? '') === 'free' ? 'selected' : '' ?>>free</option><option value="premium" <?= ($report['access'] ?? '') === 'premium' ? 'selected' : '' ?>>premium</option></select>
        <div class="status-note">الحالة الحالية: <strong><?= e($report['status'] ?? 'draft') ?></strong> — الوصول: <strong><?= e($report['access'] ?? 'free') ?></strong></div>
      </div>
      <textarea class="textarea" name="executive_summary" placeholder="الملخص التنفيذي (مطلوب عند النشر فقط)"><?= e($report['executive_summary']) ?></textarea>
      <textarea class="textarea" name="diagnostic_table" placeholder="المؤشر | القراءة الحالية | الإشارة | الأثر السعري"><?= e(stringify_diagnostic_rows($report['diagnostic_table'])) ?></textarea>
      <textarea class="textarea" name="current_interpretation" placeholder="تفسير الحركة الحالية"><?= e($report['current_interpretation']) ?></textarea>
      <textarea class="textarea" name="scenario_1m" placeholder="سيناريو الشهر الواحد"><?= e($report['scenario_1m']) ?></textarea>
      <textarea class="textarea" name="scenario_3m" placeholder="سيناريو الثلاثة أشهر"><?= e($report['scenario_3m']) ?></textarea>
      <textarea class="textarea" name="scenario_6m" placeholder="سيناريو الستة أشهر"><?= e($report['scenario_6m']) ?></textarea>
      <textarea class="textarea" name="trigger_map" placeholder="المحفزات - كل عنصر في سطر مستقل (مطلوب عنصر واحد على الأقل عند النشر)"><?= e(implode("
", $report['trigger_map'])) ?></textarea>
      <div class="form-row">
        <input class="input" name="support" placeholder="أهم دعم" value="<?= e($report['technical_levels']['support'] ?? '') ?>" />
        <input class="input" name="resistance" placeholder="أهم مقاومة" value="<?= e($report['technical_levels']['resistance'] ?? '') ?>" />
      </div>
      <div class="form-row">
        <input class="input" name="bull_confirmation" placeholder="تأكيد الصعود" value="<?= e($report['technical_levels']['bull_confirmation'] ?? '') ?>" />
        <input class="input" name="bear_confirmation" placeholder="تأكيد الهبوط" value="<?= e($report['technical_levels']['bear_confirmation'] ?? '') ?>" />
      </div>
      <input class="input" name="invalidation" placeholder="مستوى الإبطال" value="<?= e($report['technical_levels']['invalidation'] ?? '') ?>" />
      <textarea class="textarea" name="conclusion" placeholder="الخلاصة"><?= e($report['conclusion']) ?></textarea>
      <textarea class="textarea" name="sources" placeholder="المصادر - كل مصدر في سطر مستقل (مطلوب مصدر واحد على الأقل عند النشر)"><?= e(implode("
", $report['sources'])) ?></textarea>
      <div class="hero-actions wrap-actions">
        <button class="btn btn-outline" type="submit" name="form_action" value="save_draft">حفظ كمسودة</button>
        <button class="btn btn-outline" type="submit" name="form_action" value="preview">معاينة</button>
        <button class="btn btn-primary" type="submit" name="form_action" value="publish">نشر التقرير</button>
        <?php if (!empty($report['slug'])): ?><a class="btn btn-outline" target="_blank" href="/report.php?slug=<?= urlencode($report['slug']) ?>">عرض النسخة الحالية</a><?php endif; ?>
      </div>
    </form>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
