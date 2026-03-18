<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
use HubTrend\Services\ReportRepository;
$repo = new ReportRepository();
$errors = [];
$flash = admin_flash();
$payloadText = '';
$nextStep = trim((string)($_POST['next_step'] ?? 'edit'));

function friendly_import_error(string $message): string
{
    $map = [
        'title' => 'عنوان التقرير',
        'report_date' => 'تاريخ التقرير',
        'executive_summary' => 'الملخص التنفيذي',
        'current_interpretation' => 'تفسير الحركة الحالية',
        'scenario_1m' => 'سيناريو الشهر الواحد',
        'scenario_3m' => 'سيناريو الثلاثة أشهر',
        'scenario_6m' => 'سيناريو الستة أشهر',
        'conclusion' => 'الخلاصة',
        'diagnostic_table' => 'جدول التشخيص',
        'trigger_map' => 'المحفزات',
        'technical_levels' => 'المستويات الفنية',
        'support' => 'الدعم',
        'resistance' => 'المقاومة',
        'bull_confirmation' => 'تأكيد الصعود',
        'bear_confirmation' => 'تأكيد الهبوط',
        'invalidation' => 'الإبطال',
        'sources' => 'المصادر',
        'status' => 'الحالة',
        'access' => 'نوع الوصول',
    ];
    $message = str_replace(array_keys($map), array_values($map), $message);
    $message = str_replace('published', 'منشور', $message);
    $message = str_replace('draft', 'مسودة', $message);
    $message = str_replace('free', 'مجاني', $message);
    $message = str_replace('premium', 'مدفوع', $message);
    return $message;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nextStep = trim((string)($_POST['next_step'] ?? 'edit'));
    if (!empty($_FILES['json_file']['tmp_name'])) {
        $payloadText = (string) file_get_contents($_FILES['json_file']['tmp_name']);
    } else {
        $payloadText = trim((string)($_POST['json_payload'] ?? ''));
    }

    try {
        if ($payloadText === '') {
            throw new RuntimeException('يرجى رفع ملف JSON أو لصق محتوى التقرير أولًا.');
        }
        $slug = $repo->saveFromJson($payloadText);
        $report = $repo->findBySlug($slug);
        if (!$report) {
            throw new RuntimeException('تم الاستيراد لكن تعذر قراءة التقرير بعد الحفظ.');
        }
        $redirectTarget = $nextStep === 'preview'
            ? '/report.php?slug=' . urlencode($slug)
            : '/admin/report-edit.php?slug=' . urlencode($slug);
        header('Location: /admin/report-import-success.php?slug=' . urlencode($slug) . '&next=' . urlencode($nextStep));
        exit;
    } catch (Throwable $e) {
        $errors = array_filter(array_map('trim', preg_split('/\R+/', friendly_import_error($e->getMessage())) ?: []));
        if (empty($errors)) {
            $errors[] = 'تعذر استيراد التقرير. تحقق من بنية JSON ثم أعد المحاولة.';
        }
    }
}
$pageTitle = SITE_NAME . ' | استيراد تقرير';
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero compact">
  <div class="container narrow">
    <span class="eyebrow">Admin</span>
    <h1>رفع التقرير اليومي</h1>
    <p class="lead">ارفع ملف JSON أو الصق محتوى التقرير، ثم اختر الخطوة التالية. إذا نجح الاستيراد ستظهر لك رسالة إتمام واضحة ويتم الانتقال تلقائيًا.</p>
  </div>
</section>
<section class="section">
  <div class="container narrow">
    <?php if ($flash): ?><div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?>
      <div class="flash flash-error">
        <strong>تعذر استيراد التقرير:</strong>
        <ul class="clean-list issue-list"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <article class="card admin-panel import-card">
      <div class="import-head">
        <div>
          <h2>1) اختر ملف التقرير</h2>
          <p class="muted-inline">يُفضّل رفع ملف JSON مباشرة. لصق المحتوى متاح عند الحاجة فقط.</p>
        </div>
        <a class="btn btn-outline btn-sm" href="/admin/report-template.php">تحميل قالب جاهز</a>
      </div>

      <form class="stack-form" method="post" enctype="multipart/form-data">
        <label class="upload-box" for="json_file">
          <input id="json_file" class="upload-input" type="file" name="json_file" accept="application/json,.json" />
          <strong>اسحب الملف هنا أو اضغط للاختيار</strong>
          <span id="selected-file-name">لم يتم اختيار ملف بعد</span>
        </label>

        <details class="card details-card">
          <summary>أو الصق JSON يدويًا</summary>
          <textarea class="textarea large-textarea" name="json_payload" placeholder='الصق هنا JSON كامل للتقرير'><?= e($payloadText) ?></textarea>
        </details>

        <div class="next-step-box">
          <h3>2) بعد الاستيراد</h3>
          <div class="next-step-options">
            <label class="option-card <?= $nextStep === 'edit' ? 'active' : '' ?>">
              <input type="radio" name="next_step" value="edit" <?= $nextStep === 'edit' ? 'checked' : '' ?> />
              <strong>الانتقال إلى التعديل</strong>
              <small>الأنسب إذا أردت مراجعة التقرير أو استكماله.</small>
            </label>
            <label class="option-card <?= $nextStep === 'preview' ? 'active' : '' ?>">
              <input type="radio" name="next_step" value="preview" <?= $nextStep === 'preview' ? 'checked' : '' ?> />
              <strong>الانتقال إلى المعاينة</strong>
              <small>الأنسب إذا كان التقرير جاهزًا وتريد رؤيته مباشرة.</small>
            </label>
          </div>
        </div>

        <div class="hero-actions compact-actions wrap-actions import-actions">
          <button class="btn btn-primary" type="submit">استيراد التقرير الآن</button>
          <a class="btn btn-outline" href="/admin/index.php">العودة إلى لوحة الإدارة</a>
        </div>
      </form>
    </article>
  </div>
</section>
<script>
(function(){
  const input = document.getElementById('json_file');
  const nameNode = document.getElementById('selected-file-name');
  const radios = Array.from(document.querySelectorAll('input[name="next_step"]'));
  const syncActive = () => {
    radios.forEach((radio) => {
      const card = radio.closest('.option-card');
      if (!card) return;
      card.classList.toggle('active', radio.checked);
    });
  };
  if (input && nameNode) {
    input.addEventListener('change', function(){
      nameNode.textContent = this.files && this.files[0] ? this.files[0].name : 'لم يتم اختيار ملف بعد';
    });
  }
  radios.forEach((radio) => radio.addEventListener('change', syncActive));
  syncActive();
})();
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
