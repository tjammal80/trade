<?php
require_once __DIR__ . '/includes/bootstrap.php';
use HubTrend\Services\ReportRepository;
use HubTrend\Services\MarketDataService;

$repo = new ReportRepository();
$latest = $repo->latestPublished();
$stats = $repo->stats();
$market = (new MarketDataService())->getSnapshot();
$pageTitle = SITE_NAME . ' | الرئيسية';
$pageDescription = 'تقارير ذهب عربية احترافية، محدثة، وموثقة.';
include __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div class="container hero-grid">
    <div class="hero-copy">
      <span class="eyebrow">Arabic Gold Intelligence</span>
      <h1>نسخة ثالثة أخف في التشغيل وأوضح في إدارة التقارير</h1>
      <p class="lead">النسخة الثالثة تضيف التحقق من ملفات JSON، أرشيفًا أذكى، أدوات أسرع للمحرر، وتحسينًا واضحًا في عرض التقرير وقفل المحتوى.</p>
      <div class="hero-actions">
        <a href="<?= url('report.php') ?>" class="btn btn-primary">اقرأ أحدث تقرير</a>
        <a href="<?= url('reports.php') ?>" class="btn btn-outline">استعرض الأرشيف</a>
      </div>
      <div class="hero-points">
        <span>رفع التقرير كـ JSON ثم ظهوره مباشرة</span>
        <span>تحقق آلي من البنية قبل النشر</span>
        <span>أدوات إدارة أسرع دون CMS خارجي</span>
      </div>
    </div>
    <div class="hero-card card glass">
      <div class="mini-label">حالة المشروع الآن</div>
      <h3><?= e($latest['title'] ?? 'جاهز لنشر تقريرك الأول') ?></h3>
      <p><?= e(report_excerpt($latest['executive_summary'] ?? 'أنشئ تقريرًا من لوحة الإدارة أو ارفع JSON صالحًا وسيظهر هنا تلقائيًا.', 220)) ?></p>
      <div class="mini-stats">
        <div><small>تقارير صالحة</small><strong><?= (int)$stats['valid'] ?></strong></div>
        <div><small>للأعضاء</small><strong><?= (int)$stats['premium'] ?></strong></div>
        <div><small>حالة الأسعار</small><strong><?= e($market['status']) ?></strong></div>
      </div>
    </div>
  </div>
</section>

<section class="trust-strip">
  <div class="container trust-grid">
    <div class="trust-item"><strong>01</strong><span>Validator داخلي لملفات التقارير قبل الاعتماد</span></div>
    <div class="trust-item"><strong>02</strong><span>أدوات استيراد وتوليد JSON للمحرر</span></div>
    <div class="trust-item"><strong>03</strong><span>أرشيف قابل للفرز والبحث حسب النوع والتاريخ</span></div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">ما الجديد في v3؟</span>
      <h2>تحسينات تشغيلية تمس النشر اليومي مباشرة</h2>
    </div>
    <div class="feature-grid">
      <article class="card feature-card">
        <h3>تحقق من JSON</h3>
        <p>أي ملف تقرير ناقص أو تالف يظهر كمشكلة واضحة في لوحة الإدارة بدل أن يكسر الواجهة.</p>
      </article>
      <article class="card feature-card">
        <h3>استيراد مباشر</h3>
        <p>يمكنك الآن لصق JSON أو رفع ملفه من لوحة الإدارة بدل الاعتماد الكامل على File Manager.</p>
      </article>
      <article class="card feature-card">
        <h3>قالب جاهز</h3>
        <p>مولّد قالب يخرج لك JSON صحيح البنية لتعبئته ثم رفعه أو استيراده.</p>
      </article>
      <article class="card feature-card">
        <h3>عرض تقرير أوضح</h3>
        <p>ملاحة بين التقارير، حالة الوصول، وتقديم أفضل للمحتوى المدفوع.</p>
      </article>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">آخر التقارير</span>
      <h2>منشور حديثًا</h2>
    </div>
    <div class="archive-grid">
      <?php foreach (array_slice($repo->all(true), 0, 4) as $report): ?>
        <article class="card archive-card <?= e(report_visibility_class($report['access'] ?? 'free')) ?>">
          <div class="archive-meta">
            <span><?= e(format_ar_date($report['report_date'] ?? '')) ?></span>
            <span class="status-tag <?= e(report_status_badge($report['access'] ?? 'free')) ?>"><?= e(report_access_label($report['access'] ?? 'free')) ?></span>
          </div>
          <h3><?= e($report['title'] ?? '') ?></h3>
          <p><?= e(report_excerpt($report['executive_summary'] ?? '', 180)) ?></p>
          <a class="btn btn-outline btn-sm" href="<?= url('report.php?slug=' . urlencode($report['slug'])) ?>">فتح التقرير</a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="cta-band">
  <div class="container cta-wrap">
    <div>
      <span class="eyebrow">خطوة التشغيل التالية</span>
      <h2>ادخل لوحة الإدارة وجرّب الاستيراد أو توليد قالب تقرير جديد</h2>
    </div>
    <div class="hero-actions">
      <a href="<?= url('admin/index.php') ?>" class="btn btn-primary">لوحة الإدارة</a>
      <a href="<?= url('api/health.php') ?>" class="btn btn-outline">فحص الصحة</a>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
