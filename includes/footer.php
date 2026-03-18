</main>
<footer class="site-footer">
  <div class="container footer-grid">
    <div>
      <h4><?= e(SITE_NAME) ?></h4>
      <p><?= e(SITE_DESCRIPTION) ?></p>
    </div>
    <div>
      <h4>روابط سريعة</h4>
      <ul class="footer-links">
        <li><a href="<?= url('index.php') ?>">الرئيسية</a></li>
        <li><a href="<?= url('report.php') ?>">تقرير اليوم</a></li>
        <li><a href="<?= url('reports.php') ?>">الأرشيف</a></li>
        <li><a href="<?= url('pricing.php') ?>">التسعير</a></li>
      </ul>
    </div>
    <div>
      <h4>قانوني</h4>
      <ul class="footer-links">
        <li><a href="<?= url('legal/disclaimer.php') ?>">إخلاء المسؤولية</a></li>
        <li><a href="<?= url('legal/privacy.php') ?>">الخصوصية</a></li>
        <li><a href="<?= url('legal/terms.php') ?>">الشروط</a></li>
      </ul>
    </div>
  </div>
  <div class="container footer-bottom">
    <span>© <?= date('Y') ?> <?= e(SITE_NAME) ?></span>
    <span>نسخة MVP v3 مبنية على PHP ديناميكي خفيف</span>
  </div>
</footer>
<script src="<?= asset_url('assets/js/main.js') ?>"></script>
</body>
</html>
