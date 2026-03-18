<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: /admin/index.php');
        exit;
    }
    $error = 'بيانات المدير غير صحيحة. غيّرها من ملف الإعدادات.';
}
$pageTitle = SITE_NAME . ' | Admin Login';
include __DIR__ . '/../includes/header.php';
?>
<section class="auth-shell">
  <div class="container auth-grid">
    <div>
      <span class="eyebrow">Admin</span>
      <h1>دخول لوحة الإدارة</h1>
      <p class="lead">هذه اللوحة تدير التقارير المخزنة كملفات JSON. قبل الإطلاق، غيّر بيانات الدخول من ملف الإعدادات.</p>
    </div>
    <div class="card auth-card">
      <?php if ($error): ?><p style="color:#ffb2b2"><?= e($error) ?></p><?php endif; ?>
      <form method="post" class="stack-form">
        <input class="input" type="text" name="username" placeholder="اسم المستخدم" required />
        <input class="input" type="password" name="password" placeholder="كلمة المرور" required />
        <button class="btn btn-primary btn-block" type="submit">دخول</button>
      </form>
    </div>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
