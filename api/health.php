<?php
require_once __DIR__ . '/../includes/bootstrap.php';
use HubTrend\Services\ReportRepository;
header('Content-Type: application/json; charset=utf-8');
$repo = new ReportRepository();
$health = [
    'generated_at' => date('c'),
    'php_version' => PHP_VERSION,
    'storage_writable' => is_writable(STORAGE_DIR),
    'reports_writable' => is_writable(REPORTS_DIR),
    'cache_writable' => is_writable(CACHE_DIR),
    'backups_writable' => is_writable(BACKUPS_DIR),
    'report_stats' => $repo->stats(),
    'latest_report' => $repo->latestPublished()['slug'] ?? null,
];
$health['status'] = ($health['storage_writable'] && $health['reports_writable'] && $health['cache_writable'] && $health['backups_writable'] && $health['report_stats']['invalid'] === 0) ? 'ok' : 'attention';
echo json_encode($health, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
