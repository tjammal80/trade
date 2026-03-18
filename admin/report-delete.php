<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
use HubTrend\Services\ReportRepository;
$slug = $_GET['slug'] ?? '';
if ($slug) {
    (new ReportRepository())->delete($slug);
}
header('Location: /admin/index.php');
exit;
