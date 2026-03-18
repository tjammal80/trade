<?php
require_once __DIR__ . '/includes/bootstrap.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if ($email === MEMBER_EMAIL && $password === MEMBER_PASSWORD) {
        $_SESSION['member_logged_in'] = true;
        $_SESSION['member_email'] = $email;
        $_SESSION['member_plan'] = MEMBER_PLAN;
        header('Location: /account.php');
        exit;
    }
    $error = 'بيانات الدخول غير صحيحة. عدّلها من ملف الإعدادات قبل الإطلاق.';
}
$pageTitle = SITE_NAME . ' | دخول';
include __DIR__ . '/includes/header.php';
?>
<section class="auth-shell">
  <div class="container auth-grid">
    <div>
      <span class="eyebrow">دخول الأعضاء</span>
      <h1>فعّل الوصول الكامل للتقارير</h1>
      <p class="lead">في النسخة الحالية يوجد عضو تجريبي واحد. لاحقًا يُستبدل هذا المسار بمصادقة حقيقية وربط دفع.</p>
    </div>
    <div class="card auth-card">
      <?php if ($error): ?><p style="color:#ffb2b2"><?= e($error) ?></p><?php endif; ?>
      <form method="post" class="stack-form">
        <input class="input" type="email" name="email" placeholder="البريد الإلكتروني" required />
        <input class="input" type="password" name="password" placeholder="كلمة المرور" required />
        <button class="btn btn-primary btn-block" type="submit">دخول</button>
      </form>
      <p class="tiny-note">هذا المسار تجريبي فقط. غيّر بيانات العضو من <code>src/Support/config.php</code>.</p>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
